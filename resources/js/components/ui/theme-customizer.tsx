import { router, usePage } from '@inertiajs/react';
import { ChevronDown, Copy, Palette, RefreshCw, Upload, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

import { cn } from '@/lib/utils';
import {
    CARD_SKINS,
    DARK_THEMES,
    FONT_OPTIONS,
    LIGHT_THEMES,
    MENU_ACCENTS,
    MENU_COLORS,
    PRIMARY_COLORS,
    RADIUS_OPTIONS,
    SIDEBAR_LAYOUTS,
    THEME_PRESETS,
    type CardSkin,
    type DarkTheme,
    type FontOption,
    type LightTheme,
    type MenuAccent,
    type MenuColor,
    type PrimaryColor,
    type RadiusOption,
    type SidebarLayout,
} from '@/lib/tailux-themes';
import type { SharedData } from '@/types';

type ThemeMode = 'dark' | 'light' | 'system';

interface ThemeState {
    mode: ThemeMode;
    dark: DarkTheme;
    primary: PrimaryColor;
    light: LightTheme;
    skin: CardSkin;
    radius: RadiusOption;
    layout: SidebarLayout;
    font: FontOption;
    menuColor: MenuColor;
    menuAccent: MenuAccent;
}

type ExpandedKey =
    | 'mode'
    | 'preset'
    | 'layout'
    | 'dark'
    | 'primary'
    | 'light'
    | 'font'
    | 'menuColor'
    | 'menuAccent'
    | 'skin'
    | 'radius'
    | 'import'
    | null;

const DARK_THEME_COLORS: Record<DarkTheme, string> = {
    navy: '#1e2d4a',
    mirage: '#1a1d2e',
    mint: '#1a2e2a',
    black: '#0a0a0a',
    cinder: '#1c1a1a',
};

const PRIMARY_COLORS_HEX: Record<PrimaryColor, string> = {
    indigo: '#6366f1',
    blue: '#3b82f6',
    green: '#22c55e',
    amber: '#f59e0b',
    purple: '#a855f7',
    rose: '#f43f5e',
};

const FONT_LABELS: Record<FontOption, string> = {
    inter: 'Inter',
    'geist-sans': 'Geist',
    poppins: 'Poppins',
    outfit: 'Outfit',
    'plus-jakarta-sans': 'Plus Jakarta',
};

const RADIUS_CLASS: Record<RadiusOption, string> = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    default: 'rounded',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full',
};

function prefersDarkMQ(): boolean {
    return typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function applyThemeState(state: ThemeState): void {
    const root = document.documentElement;
    root.setAttribute('data-theme-dark', state.dark);
    root.setAttribute('data-theme-primary', state.primary);
    root.setAttribute('data-theme-light', state.light);
    root.setAttribute('data-card-skin', state.skin);
    root.setAttribute('data-radius', state.radius);
    root.setAttribute('data-sidebar-layout', state.layout);
    root.setAttribute('data-font', state.font);
    root.setAttribute('data-menu-color', state.menuColor);
    root.setAttribute('data-menu-accent', state.menuAccent);
    const isDark = state.mode === 'dark' || (state.mode === 'system' && prefersDarkMQ());
    root.classList.toggle('dark', isDark);
    root.style.colorScheme = isDark ? 'dark' : 'light';
}

function pickRandom<T>(arr: readonly T[]): T {
    return arr[Math.floor(Math.random() * arr.length)];
}

function randomThemeState(mode: ThemeMode): ThemeState {
    return {
        mode,
        dark: pickRandom(DARK_THEMES),
        primary: pickRandom(PRIMARY_COLORS),
        light: pickRandom(LIGHT_THEMES),
        skin: pickRandom(CARD_SKINS),
        radius: pickRandom(RADIUS_OPTIONS),
        layout: pickRandom(SIDEBAR_LAYOUTS),
        font: pickRandom(FONT_OPTIONS),
        menuColor: pickRandom(MENU_COLORS),
        menuAccent: pickRandom(MENU_ACCENTS),
    };
}

function getInitialState(theme: SharedData['theme']): ThemeState {
    return {
        mode: (theme?.userMode as ThemeMode | undefined) ?? 'system',
        dark: (theme?.dark as DarkTheme | undefined) ?? 'navy',
        primary: (theme?.primary as PrimaryColor | undefined) ?? 'indigo',
        light: (theme?.light as LightTheme | undefined) ?? 'slate',
        skin: (theme?.skin as CardSkin | undefined) ?? 'shadow',
        radius: 'default',
        layout: (theme?.layout as SidebarLayout | undefined) ?? 'main',
        font: (theme?.font as FontOption | undefined) ?? 'inter',
        menuColor: (theme?.menuColor as MenuColor | undefined) ?? 'default',
        menuAccent: (theme?.menuAccent as MenuAccent | undefined) ?? 'subtle',
    };
}

function cap(s: string): string {
    return s.charAt(0).toUpperCase() + s.slice(1);
}

function useThemeCustomizer(isOpen: boolean) {
    const { props } = usePage<SharedData>();
    const [state, setState] = useState<ThemeState>(() => getInitialState(props.theme));
    const [saving, setSaving] = useState(false);
    const [resetting, setResetting] = useState(false);
    const [expanded, setExpanded] = useState<ExpandedKey>(null);
    const [importText, setImportText] = useState('');
    const [importError, setImportError] = useState('');

    const stateRef = useRef(state);
    stateRef.current = state;

    const commit = useCallback((next: ThemeState) => {
        setState(next);
        applyThemeState(next);
    }, []);

    const update = useCallback(<K extends keyof ThemeState>(key: K, value: ThemeState[K]) => {
        setState((prev) => {
            const next = { ...prev, [key]: value };
            applyThemeState(next);

            return next;
        });
    }, []);

    const preview = useCallback((partial: Partial<ThemeState>) => {
        applyThemeState({ ...stateRef.current, ...partial });
    }, []);

    const revertPreview = useCallback(() => {
        applyThemeState(stateRef.current);
    }, []);

    const tryRandom = useCallback(() => {
        commit(randomThemeState(stateRef.current.mode));
    }, [commit]);

    useEffect(() => {
        if (!isOpen) {
            return;
        }
        const handler = (e: KeyboardEvent) => {
            if (e.key !== 'r' && e.key !== 'R') {
                return;
            }
            const tag = (e.target as HTMLElement)?.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA') {
                return;
            }
            tryRandom();
        };
        document.addEventListener('keydown', handler);

        return () => document.removeEventListener('keydown', handler);
    }, [isOpen, tryRandom]);

    const exportTheme = useCallback(() => {
        const s = stateRef.current;
        const data = {
            dark: s.dark,
            primary: s.primary,
            light: s.light,
            skin: s.skin,
            radius: s.radius,
            layout: s.layout,
            font: s.font,
            menuColor: s.menuColor,
            menuAccent: s.menuAccent,
        };
        navigator.clipboard.writeText(JSON.stringify(data, null, 2));
        toast.success('Theme copied to clipboard.');
    }, []);

    const importTheme = useCallback(() => {
        setImportError('');
        try {
            const parsed = JSON.parse(importText) as Record<string, string>;
            const s = stateRef.current;
            const next: ThemeState = {
                ...s,
                dark: (DARK_THEMES as readonly string[]).includes(parsed.dark) ? (parsed.dark as DarkTheme) : s.dark,
                primary: (PRIMARY_COLORS as readonly string[]).includes(parsed.primary) ? (parsed.primary as PrimaryColor) : s.primary,
                light: (LIGHT_THEMES as readonly string[]).includes(parsed.light) ? (parsed.light as LightTheme) : s.light,
                skin: (CARD_SKINS as readonly string[]).includes(parsed.skin) ? (parsed.skin as CardSkin) : s.skin,
                radius: (RADIUS_OPTIONS as readonly string[]).includes(parsed.radius) ? (parsed.radius as RadiusOption) : s.radius,
                layout: (SIDEBAR_LAYOUTS as readonly string[]).includes(parsed.layout) ? (parsed.layout as SidebarLayout) : s.layout,
                font: (FONT_OPTIONS as readonly string[]).includes(parsed.font) ? (parsed.font as FontOption) : s.font,
                menuColor: (MENU_COLORS as readonly string[]).includes(parsed.menuColor) ? (parsed.menuColor as MenuColor) : s.menuColor,
                menuAccent: (MENU_ACCENTS as readonly string[]).includes(parsed.menuAccent) ? (parsed.menuAccent as MenuAccent) : s.menuAccent,
            };
            commit(next);
            setImportText('');
            setExpanded(null);
            toast.success('Theme imported.');
        } catch {
            setImportError('Invalid JSON. Please check the format.');
        }
    }, [importText, commit]);

    const handleSave = useCallback(() => {
        setSaving(true);
        const s = stateRef.current;
        router.post(
            '/org/theme',
            {
                dark: s.dark,
                primary: s.primary,
                light: s.light,
                skin: s.skin,
                radius: s.radius,
                layout: s.layout,
                font: s.font,
                menuColor: s.menuColor,
                menuAccent: s.menuAccent,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => toast.success('Theme saved for your organization.'),
                onError: () => toast.error('Failed to save theme.'),
                onFinish: () => setSaving(false),
            },
        );
    }, []);

    const handleReset = useCallback(() => {
        setResetting(true);
        router.delete('/org/theme', {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                const theme = (page.props as unknown as SharedData).theme;
                commit(getInitialState(theme));
                toast.success('Theme reset to organization defaults.');
            },
            onError: () => toast.error('Failed to reset theme.'),
            onFinish: () => setResetting(false),
        });
    }, [commit]);

    const toggle = useCallback((key: ExpandedKey) => {
        setExpanded((prev) => (prev === key ? null : key));
    }, []);

    return {
        state,
        saving,
        resetting,
        expanded,
        importText,
        importError,
        setImportText,
        update,
        preview,
        revertPreview,
        tryRandom,
        exportTheme,
        importTheme,
        handleSave,
        handleReset,
        toggle,
    };
}

// ─── Dimension Row (accordion) ────────────────────────────────────────────────

interface DimensionRowProps {
    label: string;
    value: string;
    rowKey: ExpandedKey;
    expanded: ExpandedKey;
    onToggle: (key: ExpandedKey) => void;
    onMouseLeave?: () => void;
    children: React.ReactNode;
}

function DimensionRow({ label, value, rowKey, expanded, onToggle, onMouseLeave, children }: DimensionRowProps) {
    const isExpanded = expanded === rowKey;

    return (
        <div className="border-b border-border/50 last:border-0">
            <button type="button" onClick={() => onToggle(rowKey)} className="flex w-full items-center justify-between py-2.5 text-left">
                <span className="text-xs text-muted-foreground">{label}</span>
                <div className="flex items-center gap-1.5">
                    <span className="text-xs font-medium">{value}</span>
                    <ChevronDown className={cn('h-3.5 w-3.5 text-muted-foreground transition-transform duration-200', isExpanded && 'rotate-180')} />
                </div>
            </button>
            {isExpanded && (
                <div className="pb-3" onMouseLeave={onMouseLeave}>
                    {children}
                </div>
            )}
        </div>
    );
}

// ─── Option pill ──────────────────────────────────────────────────────────────

function Pill({
    active,
    onClick,
    onMouseEnter,
    children,
}: {
    active: boolean;
    onClick: () => void;
    onMouseEnter?: () => void;
    children: React.ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            onMouseEnter={onMouseEnter}
            className={cn(
                'rounded-md border px-2.5 py-1 text-xs font-medium transition-colors',
                active ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
            )}
        >
            {children}
        </button>
    );
}

// ─── Body ─────────────────────────────────────────────────────────────────────

interface BodyProps {
    state: ThemeState;
    expanded: ExpandedKey;
    saving: boolean;
    resetting: boolean;
    importText: string;
    importError: string;
    setImportText: (v: string) => void;
    update: <K extends keyof ThemeState>(key: K, value: ThemeState[K]) => void;
    preview: (partial: Partial<ThemeState>) => void;
    revertPreview: () => void;
    tryRandom: () => void;
    exportTheme: () => void;
    importTheme: () => void;
    handleSave: () => void;
    handleReset: () => void;
    toggle: (key: ExpandedKey) => void;
}

function ThemeCustomizerBody({
    state,
    expanded,
    saving,
    resetting,
    importText,
    importError,
    setImportText,
    update,
    preview,
    revertPreview,
    tryRandom,
    exportTheme,
    importTheme,
    handleSave,
    handleReset,
    toggle,
}: BodyProps) {
    const presetName =
        THEME_PRESETS.find(
            (p) => p.dark === state.dark && p.primary === state.primary && p.light === state.light && p.skin === state.skin && p.radius === state.radius,
        )?.name ?? 'Custom';

    return (
        <div className="flex flex-col gap-0">
            {/* Toolbar */}
            <div className="mb-3 flex items-center gap-1.5">
                <button
                    type="button"
                    onClick={tryRandom}
                    title="Try Random (R)"
                    className="flex flex-1 items-center justify-center gap-1.5 rounded-md border border-border px-2 py-1.5 text-xs text-muted-foreground transition-colors hover:border-primary hover:text-foreground"
                >
                    <RefreshCw className="h-3 w-3" />
                    Random
                </button>
                <button
                    type="button"
                    onClick={exportTheme}
                    title="Copy theme JSON"
                    className="flex flex-1 items-center justify-center gap-1.5 rounded-md border border-border px-2 py-1.5 text-xs text-muted-foreground transition-colors hover:border-primary hover:text-foreground"
                >
                    <Copy className="h-3 w-3" />
                    Export
                </button>
                <button
                    type="button"
                    onClick={() => toggle('import')}
                    title="Import theme JSON"
                    className={cn(
                        'flex flex-1 items-center justify-center gap-1.5 rounded-md border px-2 py-1.5 text-xs transition-colors',
                        expanded === 'import'
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-border text-muted-foreground hover:border-primary hover:text-foreground',
                    )}
                >
                    <Upload className="h-3 w-3" />
                    Import
                </button>
            </div>

            {/* Import textarea */}
            {expanded === 'import' && (
                <div className="mb-3 space-y-2 rounded-md border border-border bg-muted/30 p-2.5">
                    <textarea
                        value={importText}
                        onChange={(e) => setImportText(e.target.value)}
                        placeholder='{ "dark": "navy", "primary": "indigo", ... }'
                        rows={4}
                        className="w-full resize-none rounded border border-border bg-background px-2 py-1.5 font-mono text-xs placeholder:text-muted-foreground/50 focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                    {importError && <p className="text-xs text-destructive">{importError}</p>}
                    <div className="flex gap-2">
                        <button type="button" onClick={importTheme} className="rounded-md bg-primary px-3 py-1 text-xs font-medium text-primary-foreground hover:opacity-90">
                            Apply
                        </button>
                        <button
                            type="button"
                            onClick={() => toggle('import')}
                            className="rounded-md border border-border px-3 py-1 text-xs font-medium text-muted-foreground hover:bg-muted"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            )}

            {/* Accordion rows */}
            <div>
                {/* Theme Mode */}
                <DimensionRow label="Theme Mode" value={cap(state.mode)} rowKey="mode" expanded={expanded} onToggle={toggle}>
                    <div className="flex gap-1.5">
                        {(['light', 'dark', 'system'] as ThemeMode[]).map((m) => (
                            <Pill key={m} active={state.mode === m} onClick={() => update('mode', m)}>
                                {cap(m)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Preset */}
                <DimensionRow label="Preset" value={presetName} rowKey="preset" expanded={expanded} onToggle={toggle} onMouseLeave={revertPreview}>
                    <div className="grid grid-cols-3 gap-1.5">
                        {THEME_PRESETS.map((preset) => {
                            const isActive =
                                state.dark === preset.dark &&
                                state.primary === preset.primary &&
                                state.light === preset.light &&
                                state.skin === preset.skin &&
                                state.radius === preset.radius;

                            return (
                                <button
                                    key={preset.name}
                                    type="button"
                                    onMouseEnter={() =>
                                        preview({ dark: preset.dark, primary: preset.primary, light: preset.light, skin: preset.skin, radius: preset.radius })
                                    }
                                    onClick={() => {
                                        update('dark', preset.dark);
                                        update('primary', preset.primary);
                                        update('light', preset.light);
                                        update('skin', preset.skin);
                                        update('radius', preset.radius);
                                    }}
                                    className={cn(
                                        'flex flex-col items-center gap-1 rounded-lg border p-2 text-xs font-medium transition-colors',
                                        isActive ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
                                    )}
                                >
                                    <span className="h-5 w-5 rounded-full border border-white/30 shadow-sm" style={{ background: PRIMARY_COLORS_HEX[preset.primary] }} />
                                    {preset.name}
                                </button>
                            );
                        })}
                    </div>
                </DimensionRow>

                {/* Layout */}
                <DimensionRow label="Layout" value={cap(state.layout)} rowKey="layout" expanded={expanded} onToggle={toggle}>
                    <div className="flex gap-1.5">
                        {SIDEBAR_LAYOUTS.map((l) => (
                            <Pill key={l} active={state.layout === l} onClick={() => update('layout', l)}>
                                {l === 'sideblock' ? 'Sideblock' : 'Main'}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Dark Palette */}
                <DimensionRow label="Dark Palette" value={cap(state.dark)} rowKey="dark" expanded={expanded} onToggle={toggle} onMouseLeave={revertPreview}>
                    <div className="flex gap-2">
                        {DARK_THEMES.map((theme) => (
                            <button
                                key={theme}
                                type="button"
                                title={cap(theme)}
                                onMouseEnter={() => preview({ dark: theme })}
                                onClick={() => update('dark', theme)}
                                className={cn(
                                    'h-7 w-7 rounded-full border-2 transition-transform hover:scale-110',
                                    state.dark === theme ? 'scale-110 border-primary ring-2 ring-primary/30' : 'border-transparent',
                                )}
                                style={{ background: DARK_THEME_COLORS[theme] }}
                            />
                        ))}
                    </div>
                </DimensionRow>

                {/* Primary Color */}
                <DimensionRow label="Primary Color" value={cap(state.primary)} rowKey="primary" expanded={expanded} onToggle={toggle} onMouseLeave={revertPreview}>
                    <div className="flex flex-wrap gap-2">
                        {PRIMARY_COLORS.map((color) => (
                            <button
                                key={color}
                                type="button"
                                title={cap(color)}
                                onMouseEnter={() => preview({ primary: color })}
                                onClick={() => update('primary', color)}
                                className={cn(
                                    'h-7 w-7 rotate-45 rounded-sm border-2 transition-transform hover:scale-110',
                                    state.primary === color ? 'scale-110 border-foreground' : 'border-transparent',
                                )}
                                style={{ background: PRIMARY_COLORS_HEX[color] }}
                            />
                        ))}
                    </div>
                </DimensionRow>

                {/* Light Scheme */}
                <DimensionRow label="Light Scheme" value={cap(state.light)} rowKey="light" expanded={expanded} onToggle={toggle} onMouseLeave={revertPreview}>
                    <div className="flex gap-1.5">
                        {LIGHT_THEMES.map((theme) => (
                            <Pill key={theme} active={state.light === theme} onClick={() => update('light', theme)} onMouseEnter={() => preview({ light: theme })}>
                                {cap(theme)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Font */}
                <DimensionRow label="Font" value={FONT_LABELS[state.font] ?? state.font} rowKey="font" expanded={expanded} onToggle={toggle}>
                    <div className="flex flex-wrap gap-1.5">
                        {FONT_OPTIONS.map((f) => (
                            <Pill key={f} active={state.font === f} onClick={() => update('font', f)}>
                                {FONT_LABELS[f]}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Menu Color */}
                <DimensionRow label="Menu Color" value={cap(state.menuColor)} rowKey="menuColor" expanded={expanded} onToggle={toggle}>
                    <div className="flex gap-1.5">
                        {MENU_COLORS.map((c) => (
                            <Pill key={c} active={state.menuColor === c} onClick={() => update('menuColor', c)}>
                                {cap(c)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Menu Accent */}
                <DimensionRow label="Menu Accent" value={cap(state.menuAccent)} rowKey="menuAccent" expanded={expanded} onToggle={toggle}>
                    <div className="flex gap-1.5">
                        {MENU_ACCENTS.map((a) => (
                            <Pill key={a} active={state.menuAccent === a} onClick={() => update('menuAccent', a)}>
                                {cap(a)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Card Skin */}
                <DimensionRow label="Card Skin" value={cap(state.skin)} rowKey="skin" expanded={expanded} onToggle={toggle}>
                    <div className="grid grid-cols-2 gap-1.5">
                        {CARD_SKINS.map((s) => (
                            <Pill key={s} active={state.skin === s} onClick={() => update('skin', s)}>
                                {cap(s)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Radius */}
                <DimensionRow label="Radius" value={cap(state.radius)} rowKey="radius" expanded={expanded} onToggle={toggle}>
                    <div className="flex flex-wrap gap-1.5">
                        {RADIUS_OPTIONS.map((r) => (
                            <button
                                key={r}
                                type="button"
                                onClick={() => update('radius', r)}
                                className={cn(
                                    'flex h-8 w-8 items-center justify-center border text-xs font-medium transition-colors',
                                    RADIUS_CLASS[r],
                                    state.radius === r
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
                                )}
                            >
                                {r === 'default' ? 'Def' : r === 'none' ? '0' : r.toUpperCase()}
                            </button>
                        ))}
                    </div>
                </DimensionRow>
            </div>

            {/* Actions */}
            <div className="mt-4 space-y-2">
                <button
                    type="button"
                    disabled={saving}
                    onClick={handleSave}
                    className={cn(
                        'w-full rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground',
                        'transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50',
                    )}
                >
                    {saving ? 'Saving…' : 'Save for Organization'}
                </button>
                <button
                    type="button"
                    disabled={resetting}
                    onClick={handleReset}
                    className="w-full text-center text-xs text-muted-foreground underline-offset-2 hover:text-foreground hover:underline disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {resetting ? 'Resetting…' : 'Reset to defaults'}
                </button>
            </div>
        </div>
    );
}

// ─── Public exports ───────────────────────────────────────────────────────────

export function ThemeCustomizer() {
    const { props } = usePage<SharedData>();

    if (!props.theme?.canCustomize) {
        return null;
    }

    return <ThemeCustomizerPanel />;
}

export function ThemeCustomizerPanel() {
    const [open, setOpen] = useState(false);
    const customizer = useThemeCustomizer(open);

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-label="Open theme customizer"
                className={cn(
                    'fixed right-0 top-1/2 z-50 -translate-y-1/2',
                    'flex h-10 w-10 items-center justify-center',
                    'rounded-l-lg bg-primary text-primary-foreground shadow-lg',
                    'transition-all hover:w-12',
                )}
            >
                <Palette className="h-5 w-5" />
            </button>

            {open && <div className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm" onClick={() => setOpen(false)} />}

            <aside
                className={cn(
                    'fixed right-0 top-0 z-50 h-full w-72 overflow-y-auto',
                    'border-l bg-background shadow-xl',
                    'transition-transform duration-300',
                    open ? 'translate-x-0' : 'translate-x-full',
                )}
            >
                <div className="flex items-center justify-between border-b px-4 py-3">
                    <div className="flex items-center gap-2">
                        <Palette className="h-4 w-4" />
                        <span className="text-sm font-semibold">Theme Customizer</span>
                    </div>
                    <button type="button" onClick={() => setOpen(false)} aria-label="Close" className="rounded p-1 hover:bg-muted">
                        <X className="h-4 w-4" />
                    </button>
                </div>
                <div className="p-4">
                    <ThemeCustomizerBody {...customizer} />
                </div>
            </aside>
        </>
    );
}

export function ThemeCustomizerInline() {
    const customizer = useThemeCustomizer(true);

    return (
        <div className="rounded-lg border bg-card p-4">
            <div className="mb-4 flex items-center gap-2">
                <Palette className="h-4 w-4" />
                <span className="text-sm font-semibold">Theme Customizer</span>
            </div>
            <ThemeCustomizerBody {...customizer} />
        </div>
    );
}
