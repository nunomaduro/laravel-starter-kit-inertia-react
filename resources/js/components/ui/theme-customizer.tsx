import { router, usePage } from '@inertiajs/react';
import { ChevronDown, Copy, ImageIcon, Loader2, Lock, LockKeyhole, Palette, RefreshCw, Unlock, Upload, X } from 'lucide-react';
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
    type ThemePreset,
} from '@/lib/tailux-themes';
import type { SharedData } from '@/types';

type ThemeMode = 'dark' | 'light' | 'system';
type LockableKey = 'dark' | 'primary' | 'light' | 'skin' | 'radius' | 'layout' | 'font' | 'menuColor' | 'menuAccent';

// Maps DB setting names (from ThemeSettings) to frontend ThemeState keys
const DB_SETTING_TO_FRONTEND_KEY: Record<string, LockableKey> = {
    dark_color_scheme: 'dark',
    primary_color: 'primary',
    light_color_scheme: 'light',
    card_skin: 'skin',
    border_radius: 'radius',
    sidebar_layout: 'layout',
    font: 'font',
    menu_color: 'menuColor',
    menu_accent: 'menuAccent',
};

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

const LIGHT_THEME_COLORS: Record<LightTheme, string> = {
    slate: '#cbd5e1',
    gray: '#d1d5db',
    neutral: '#d4d4d4',
};

const FONT_LABELS: Record<FontOption, string> = {
    inter: 'Inter',
    'geist-sans': 'Geist',
    'instrument-sans': 'Instrument',
    poppins: 'Poppins',
    outfit: 'Outfit',
    'plus-jakarta-sans': 'Jakarta',
};

const FONT_FAMILIES: Record<FontOption, string> = {
    inter: 'Inter, sans-serif',
    'geist-sans': '"Geist", "Geist Sans", sans-serif',
    'instrument-sans': '"Instrument Sans", sans-serif',
    poppins: 'Poppins, sans-serif',
    outfit: 'Outfit, sans-serif',
    'plus-jakarta-sans': '"Plus Jakarta Sans", sans-serif',
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

function randomThemeState(mode: ThemeMode, locked: Set<string>, current: ThemeState): ThemeState {
    return {
        mode,
        dark: locked.has('dark') ? current.dark : pickRandom(DARK_THEMES),
        primary: locked.has('primary') ? current.primary : pickRandom(PRIMARY_COLORS),
        light: locked.has('light') ? current.light : pickRandom(LIGHT_THEMES),
        skin: locked.has('skin') ? current.skin : pickRandom(CARD_SKINS),
        radius: locked.has('radius') ? current.radius : pickRandom(RADIUS_OPTIONS),
        layout: locked.has('layout') ? current.layout : pickRandom(SIDEBAR_LAYOUTS),
        font: locked.has('font') ? current.font : pickRandom(FONT_OPTIONS),
        menuColor: locked.has('menuColor') ? current.menuColor : pickRandom(MENU_COLORS),
        menuAccent: locked.has('menuAccent') ? current.menuAccent : pickRandom(MENU_ACCENTS),
    };
}

function getPresetSlug(state: ThemeState): string {
    const preset = THEME_PRESETS.find(
        (p) => p.dark === state.dark && p.primary === state.primary && p.light === state.light && p.skin === state.skin && p.radius === state.radius,
    );
    return preset ? `--preset ${preset.name.toLowerCase()}` : '--preset custom';
}

function getInitialState(theme: SharedData['theme']): ThemeState {
    return {
        mode: (theme?.userMode as ThemeMode | undefined) ?? 'system',
        dark: (theme?.dark as DarkTheme | undefined) ?? 'navy',
        primary: (theme?.primary as PrimaryColor | undefined) ?? 'indigo',
        light: (theme?.light as LightTheme | undefined) ?? 'slate',
        skin: (theme?.skin as CardSkin | undefined) ?? 'shadow',
        radius: (theme?.radius as RadiusOption | undefined) ?? 'default',
        layout: (theme?.layout as SidebarLayout | undefined) ?? 'main',
        font: (theme?.font as FontOption | undefined) ?? 'inter',
        menuColor: (theme?.menuColor as MenuColor | undefined) ?? 'default',
        menuAccent: (theme?.menuAccent as MenuAccent | undefined) ?? 'subtle',
    };
}

function cap(s: string): string {
    return s.charAt(0).toUpperCase() + s.slice(1);
}

/**
 * Extracts the dominant hue from an image file using canvas sampling,
 * skipping near-white, near-black, and transparent pixels.
 * Returns the nearest PrimaryColor name.
 */
function extractDominantPrimary(file: File): Promise<PrimaryColor> {
    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 64;
                canvas.height = 64;
                const ctx = canvas.getContext('2d');
                if (!ctx) { resolve('indigo'); return; }
                ctx.drawImage(img, 0, 0, 64, 64);
                const { data } = ctx.getImageData(0, 0, 64, 64);
                let rSum = 0, gSum = 0, bSum = 0, count = 0;
                for (let i = 0; i < data.length; i += 4) {
                    const a = data[i + 3];
                    if (a < 128) continue;
                    const r = data[i], g = data[i + 1], b = data[i + 2];
                    // Skip near-white and near-black pixels
                    if (r > 215 && g > 215 && b > 215) continue;
                    if (r < 40 && g < 40 && b < 40) continue;
                    rSum += r; gSum += g; bSum += b; count++;
                }
                if (count === 0) { resolve('indigo'); return; }
                const rn = (rSum / count) / 255;
                const gn = (gSum / count) / 255;
                const bn = (bSum / count) / 255;
                const max = Math.max(rn, gn, bn);
                const min = Math.min(rn, gn, bn);
                const d = max - min;
                let h = 0;
                if (d > 0.05) {
                    if (max === rn) h = ((gn - bn) / d) % 6;
                    else if (max === gn) h = (bn - rn) / d + 2;
                    else h = (rn - gn) / d + 4;
                    h = Math.round(h * 60);
                    if (h < 0) h += 360;
                }
                // Map hue to nearest primary color
                let primary: PrimaryColor;
                if (h >= 330 || h < 20) primary = 'rose';
                else if (h < 70) primary = 'amber';
                else if (h < 160) primary = 'green';
                else if (h < 240) primary = 'blue';
                else if (h < 275) primary = 'indigo';
                else primary = 'purple';
                resolve(primary);
            } catch {
                resolve('indigo');
            } finally {
                URL.revokeObjectURL(url);
            }
        };
        img.onerror = () => { URL.revokeObjectURL(url); resolve('indigo'); };
        img.src = url;
    });
}

function useThemeCustomizer(isOpen: boolean) {
    const { props } = usePage<SharedData>();
    const [state, setState] = useState<ThemeState>(() => getInitialState(props.theme));
    const [saving, setSaving] = useState(false);
    const [resetting, setResetting] = useState(false);
    const [expanded, setExpanded] = useState<ExpandedKey>(null);
    const [importText, setImportText] = useState('');
    const [importError, setImportError] = useState('');
    const [lockedKeys, setLockedKeys] = useState<Set<LockableKey>>(() => {
        try {
            const saved = localStorage.getItem('theme-customizer-locks');
            if (saved) {
                return new Set(JSON.parse(saved) as LockableKey[]);
            }
        } catch {
            /* ignore */
        }
        return new Set();
    });

    const [logoUrl, setLogoUrl] = useState<string | null>(() => props.branding?.logoUrl ?? null);
    const [analyzing, setAnalyzing] = useState(false);

    const stateRef = useRef(state);
    stateRef.current = state;
    const lockedRef = useRef(lockedKeys);
    lockedRef.current = lockedKeys;

    useEffect(() => {
        try {
            localStorage.setItem('theme-customizer-locks', JSON.stringify([...lockedKeys]));
        } catch {
            /* ignore */
        }
    }, [lockedKeys]);

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

    const applyPreset = useCallback((preset: ThemePreset) => {
        setState((prev) => {
            const next: ThemeState = {
                ...prev,
                dark: preset.dark,
                primary: preset.primary,
                light: preset.light,
                skin: preset.skin,
                radius: preset.radius,
                ...(preset.font ? { font: preset.font } : {}),
                ...(preset.layout ? { layout: preset.layout } : {}),
                ...(preset.menuColor ? { menuColor: preset.menuColor } : {}),
                ...(preset.menuAccent ? { menuAccent: preset.menuAccent } : {}),
            };
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

    const toggleLock = useCallback((key: LockableKey) => {
        setLockedKeys((prev) => {
            const next = new Set(prev);
            if (next.has(key)) {
                next.delete(key);
            } else {
                next.add(key);
            }
            return next;
        });
    }, []);

    const tryRandom = useCallback(() => {
        commit(randomThemeState(stateRef.current.mode, lockedRef.current, stateRef.current));
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

    const handleLogoUpload = useCallback(async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;
        e.target.value = '';

        const xsrfCookie = document.cookie.split(';').find((c) => c.trim().startsWith('XSRF-TOKEN='));
        const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie.split('=')[1]) : '';
        const formData = new FormData();
        formData.append('logo', file);

        setAnalyzing(true);

        // Extract dominant color client-side immediately for instant visual feedback
        const clientPrimary = await extractDominantPrimary(file);

        try {
            const res = await fetch('/org/theme/analyze-logo', {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': xsrfToken,
                    'X-Color-Hint': clientPrimary,
                },
                credentials: 'include',
                body: formData,
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = (await res.json()) as {
                suggestion: Partial<ThemeState> & { reason?: string; ai_derived?: boolean };
                logoUrl: string;
            };
            const { reason, ai_derived, ...themeValues } = data.suggestion;
            setLogoUrl(data.logoUrl);
            // If AI was not available, use the client-extracted primary color
            const primary = ai_derived === false ? clientPrimary : (themeValues.primary ?? clientPrimary);
            commit({ ...stateRef.current, ...(themeValues as Partial<ThemeState>), primary });
            toast.success(reason ?? 'Theme applied from your logo! Adjust as needed.');
        } catch {
            // AI unavailable — apply client-extracted primary at minimum
            commit({ ...stateRef.current, primary: clientPrimary });
            toast.success('Logo color applied. Connect an AI provider for full theme suggestions.');
        } finally {
            setAnalyzing(false);
        }
    }, [commit]);

    return {
        state,
        saving,
        resetting,
        expanded,
        importText,
        importError,
        lockedKeys,
        logoUrl,
        analyzing,
        setImportText,
        update,
        applyPreset,
        preview,
        revertPreview,
        tryRandom,
        exportTheme,
        importTheme,
        handleSave,
        handleReset,
        toggle,
        toggleLock,
        handleLogoUpload,
    };
}

// ─── Mini Live Preview ─────────────────────────────────────────────────────────

function MiniPreview({ state, logoUrl }: { state: ThemeState; logoUrl?: string | null }) {
    return (
        <div className="mb-3 rounded-lg border border-border bg-muted/20 p-2.5" style={{ fontFamily: FONT_FAMILIES[state.font] }}>
            <div
                className={cn(
                    'rounded-lg bg-card p-3',
                    state.skin === 'shadow' && 'shadow-md border border-border/50',
                    state.skin === 'elevated' && 'shadow-lg border border-border/30',
                    state.skin === 'bordered' && 'border-2 border-border',
                    state.skin === 'flat' && 'border border-border',
                )}
            >
                <p className="mb-2 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Live Preview</p>
                {logoUrl && (
                    <div className="mb-2 flex h-6 items-center">
                        <img src={logoUrl} alt="" className="max-h-full max-w-[60px] object-contain opacity-80" />
                    </div>
                )}
                <div className="flex flex-wrap items-center gap-1.5">
                    <button className={cn('bg-primary px-2.5 py-1 text-[11px] font-medium text-primary-foreground', RADIUS_CLASS[state.radius])}>
                        Button
                    </button>
                    <button
                        className={cn(
                            'border border-border bg-background px-2.5 py-1 text-[11px] font-medium text-foreground',
                            RADIUS_CLASS[state.radius],
                        )}
                    >
                        Ghost
                    </button>
                    <span className={cn('bg-primary/15 px-2 py-0.5 text-[10px] font-medium text-primary', RADIUS_CLASS[state.radius])}>Badge</span>
                    <span className="text-[10px] text-muted-foreground">Sample text</span>
                </div>
            </div>
        </div>
    );
}

// ─── Logo Section ──────────────────────────────────────────────────────────────

function LogoSection({
    logoUrl,
    analyzing,
    onUpload,
}: {
    logoUrl: string | null;
    analyzing: boolean;
    onUpload: (e: React.ChangeEvent<HTMLInputElement>) => void;
}) {
    return (
        <div className="mb-3 rounded-lg border border-border bg-muted/20 p-2.5">
            <p className="mb-2 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Logo & Auto-Theme</p>
            {logoUrl && (
                <div className="mb-2 flex h-10 items-center">
                    <img src={logoUrl} alt="Logo" className="max-h-full max-w-full object-contain" />
                </div>
            )}
            <label
                className={cn(
                    'flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-md border px-2 py-1.5 text-xs transition-colors',
                    analyzing
                        ? 'cursor-not-allowed border-primary bg-primary/10 text-primary'
                        : 'border-border text-muted-foreground hover:border-primary hover:text-foreground',
                )}
            >
                {analyzing ? <Loader2 className="h-3 w-3 animate-spin" /> : <ImageIcon className="h-3 w-3" />}
                {analyzing ? 'Analyzing…' : logoUrl ? 'Change Logo' : 'Upload Logo'}
                <input type="file" accept="image/png,image/jpeg,image/gif,image/webp" className="sr-only" onChange={onUpload} disabled={analyzing} />
            </label>
            {analyzing && <p className="mt-1.5 text-center text-[10px] text-muted-foreground">AI is reading your logo colors…</p>}
        </div>
    );
}

// ─── Dimension Row (accordion) ─────────────────────────────────────────────────

interface DimensionRowProps {
    label: string;
    value: string;
    rowKey: ExpandedKey;
    expanded: ExpandedKey;
    onToggle: (key: ExpandedKey) => void;
    onMouseLeave?: () => void;
    locked?: boolean;
    onToggleLock?: () => void;
    /** When true, system admin has locked this setting; inputs are disabled with a lock badge. */
    systemLocked?: boolean;
    children: React.ReactNode;
}

function DimensionRow({ label, value, rowKey, expanded, onToggle, onMouseLeave, locked, onToggleLock, systemLocked, children }: DimensionRowProps) {
    const isExpanded = expanded === rowKey;

    return (
        <div className={cn('border-b border-border/50 last:border-0', systemLocked && 'opacity-60')}>
            <button
                type="button"
                onClick={() => !systemLocked && onToggle(rowKey)}
                disabled={systemLocked}
                className="flex w-full items-center justify-between py-2.5 text-left disabled:cursor-not-allowed"
            >
                <span className="text-xs text-muted-foreground">{label}</span>
                <div className="flex items-center gap-1.5">
                    <span className="text-xs font-medium">{value}</span>
                    {systemLocked && (
                        <span title="Set by system administrator" className="text-muted-foreground/60">
                            <LockKeyhole className="h-3 w-3" />
                        </span>
                    )}
                    {!systemLocked && onToggleLock !== undefined && (
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                onToggleLock();
                            }}
                            className={cn(
                                'rounded p-0.5 transition-colors',
                                locked ? 'text-primary' : 'text-muted-foreground/30 hover:text-muted-foreground',
                            )}
                            title={locked ? "Locked (won't shuffle)" : 'Lock from shuffle'}
                        >
                            {locked ? <Lock className="h-3 w-3" /> : <Unlock className="h-3 w-3" />}
                        </button>
                    )}
                    {!systemLocked && <ChevronDown className={cn('h-3.5 w-3.5 text-muted-foreground transition-transform duration-200', isExpanded && 'rotate-180')} />}
                </div>
            </button>
            {!systemLocked && isExpanded && (
                <div className="pb-3" onMouseLeave={onMouseLeave}>
                    {children}
                </div>
            )}
        </div>
    );
}

// ─── Option pill ───────────────────────────────────────────────────────────────

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

// ─── Body ──────────────────────────────────────────────────────────────────────

interface BodyProps {
    state: ThemeState;
    expanded: ExpandedKey;
    saving: boolean;
    resetting: boolean;
    importText: string;
    importError: string;
    lockedKeys: Set<LockableKey>;
    /** Keys locked by the system admin (cannot be changed by orgs). */
    systemLockedKeys: Set<LockableKey>;
    logoUrl: string | null;
    analyzing: boolean;
    setImportText: (v: string) => void;
    update: <K extends keyof ThemeState>(key: K, value: ThemeState[K]) => void;
    applyPreset: (preset: ThemePreset) => void;
    preview: (partial: Partial<ThemeState>) => void;
    revertPreview: () => void;
    tryRandom: () => void;
    exportTheme: () => void;
    importTheme: () => void;
    handleSave: () => void;
    handleReset: () => void;
    toggle: (key: ExpandedKey) => void;
    toggleLock: (key: LockableKey) => void;
    handleLogoUpload: (e: React.ChangeEvent<HTMLInputElement>) => void;
    canManageBranding: boolean;
}

function ThemeCustomizerBody({
    state,
    expanded,
    saving,
    resetting,
    importText,
    importError,
    lockedKeys,
    systemLockedKeys,
    logoUrl,
    analyzing,
    setImportText,
    update,
    applyPreset,
    preview,
    revertPreview,
    tryRandom,
    exportTheme,
    importTheme,
    handleSave,
    handleReset,
    toggle,
    toggleLock,
    handleLogoUpload,
    canManageBranding,
}: BodyProps) {
    const presetSlug = getPresetSlug(state);
    const activePreset = THEME_PRESETS.find(
        (p) => p.dark === state.dark && p.primary === state.primary && p.light === state.light && p.skin === state.skin && p.radius === state.radius,
    );
    const lockedCount = lockedKeys.size;

    return (
        <div className="flex flex-col gap-0">
            {/* Logo & Auto-Theme */}
            {canManageBranding && <LogoSection logoUrl={logoUrl} analyzing={analyzing} onUpload={handleLogoUpload} />}

            {/* Live Preview */}
            <MiniPreview state={state} logoUrl={logoUrl} />

            {/* Toolbar */}
            <div className="mb-3 flex items-center gap-1.5">
                <button
                    type="button"
                    onClick={tryRandom}
                    title={`Shuffle (R)${lockedCount > 0 ? ` · ${lockedCount} locked` : ''}`}
                    className="flex flex-1 items-center justify-center gap-1.5 rounded-md border border-border px-2 py-1.5 text-xs text-muted-foreground transition-colors hover:border-primary hover:text-foreground"
                >
                    <RefreshCw className="h-3 w-3" />
                    Shuffle{lockedCount > 0 ? ` (${lockedCount})` : ''}
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
                <DimensionRow
                    label="Preset"
                    value={activePreset?.name ?? 'Custom'}
                    rowKey="preset"
                    expanded={expanded}
                    onToggle={toggle}
                    onMouseLeave={revertPreview}
                >
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
                                        preview({
                                            dark: preset.dark,
                                            primary: preset.primary,
                                            light: preset.light,
                                            skin: preset.skin,
                                            radius: preset.radius,
                                            ...(preset.font ? { font: preset.font } : {}),
                                            ...(preset.layout ? { layout: preset.layout } : {}),
                                        })
                                    }
                                    onClick={() => applyPreset(preset)}
                                    className={cn(
                                        'flex flex-col items-center gap-1.5 rounded-lg border p-2 text-xs font-medium transition-colors',
                                        isActive
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
                                    )}
                                >
                                    <div className="flex items-center gap-0.5">
                                        <span
                                            className="h-3 w-3 rounded-full border border-white/20 shadow-sm"
                                            style={{ background: DARK_THEME_COLORS[preset.dark] }}
                                        />
                                        <span className="h-3 w-3 rounded-full shadow-sm" style={{ background: PRIMARY_COLORS_HEX[preset.primary] }} />
                                        <span
                                            className="h-3 w-3 rounded-full border border-black/10 shadow-sm"
                                            style={{ background: LIGHT_THEME_COLORS[preset.light] }}
                                        />
                                    </div>
                                    {preset.name}
                                </button>
                            );
                        })}
                    </div>
                </DimensionRow>

                {/* Layout */}
                <DimensionRow
                    label="Layout"
                    value={cap(state.layout)}
                    rowKey="layout"
                    expanded={expanded}
                    onToggle={toggle}
                    locked={lockedKeys.has('layout')}
                    onToggleLock={() => toggleLock('layout')}
                    systemLocked={systemLockedKeys.has('layout')}
                >
                    <div className="flex gap-1.5">
                        {SIDEBAR_LAYOUTS.map((l) => (
                            <Pill key={l} active={state.layout === l} onClick={() => update('layout', l)}>
                                {l === 'sideblock' ? 'Sideblock' : 'Main'}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Dark Palette */}
                <DimensionRow
                    label="Dark Palette"
                    value={cap(state.dark)}
                    rowKey="dark"
                    expanded={expanded}
                    onToggle={toggle}
                    onMouseLeave={revertPreview}
                    locked={lockedKeys.has('dark')}
                    onToggleLock={() => toggleLock('dark')}
                    systemLocked={systemLockedKeys.has('dark')}
                >
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
                <DimensionRow
                    label="Primary Color"
                    value={cap(state.primary)}
                    rowKey="primary"
                    expanded={expanded}
                    onToggle={toggle}
                    onMouseLeave={revertPreview}
                    locked={lockedKeys.has('primary')}
                    onToggleLock={() => toggleLock('primary')}
                    systemLocked={systemLockedKeys.has('primary')}
                >
                    <div className="flex flex-wrap gap-3 pb-2">
                        {PRIMARY_COLORS.map((color) => (
                            <div key={color} className="group relative flex flex-col items-center">
                                <span className="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap rounded border border-border bg-popover px-1.5 py-0.5 text-[10px] font-medium text-popover-foreground shadow-sm opacity-0 transition-opacity group-hover:opacity-100">
                                    {cap(color)}
                                </span>
                                <button
                                    type="button"
                                    onMouseEnter={() => preview({ primary: color })}
                                    onClick={() => update('primary', color)}
                                    className={cn(
                                        'h-7 w-7 rounded-full border-2 transition-transform hover:scale-110',
                                        state.primary === color ? 'scale-110 border-foreground ring-2 ring-foreground/20' : 'border-transparent',
                                    )}
                                    style={{ background: PRIMARY_COLORS_HEX[color] }}
                                />
                            </div>
                        ))}
                    </div>
                </DimensionRow>

                {/* Light Scheme */}
                <DimensionRow
                    label="Light Scheme"
                    value={cap(state.light)}
                    rowKey="light"
                    expanded={expanded}
                    onToggle={toggle}
                    onMouseLeave={revertPreview}
                    locked={lockedKeys.has('light')}
                    onToggleLock={() => toggleLock('light')}
                    systemLocked={systemLockedKeys.has('light')}
                >
                    <div className="flex gap-1.5">
                        {LIGHT_THEMES.map((theme) => (
                            <Pill key={theme} active={state.light === theme} onClick={() => update('light', theme)} onMouseEnter={() => preview({ light: theme })}>
                                {cap(theme)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Font */}
                <DimensionRow
                    label="Font"
                    value={FONT_LABELS[state.font] ?? state.font}
                    rowKey="font"
                    expanded={expanded}
                    onToggle={toggle}
                    locked={lockedKeys.has('font')}
                    onToggleLock={() => toggleLock('font')}
                    systemLocked={systemLockedKeys.has('font')}
                >
                    <div className="grid grid-cols-3 gap-1.5">
                        {FONT_OPTIONS.map((f) => (
                            <button
                                key={f}
                                type="button"
                                onClick={() => update('font', f)}
                                className={cn(
                                    'flex flex-col items-center gap-0.5 rounded-md border px-1.5 py-1.5 transition-colors',
                                    state.font === f
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
                                )}
                            >
                                <span style={{ fontFamily: FONT_FAMILIES[f] }} className="text-base font-semibold leading-none">
                                    Aa
                                </span>
                                <span className="truncate text-[10px] font-medium">{FONT_LABELS[f]}</span>
                            </button>
                        ))}
                    </div>
                </DimensionRow>

                {/* Menu Color */}
                <DimensionRow
                    label="Menu Color"
                    value={cap(state.menuColor)}
                    rowKey="menuColor"
                    expanded={expanded}
                    onToggle={toggle}
                    locked={lockedKeys.has('menuColor')}
                    onToggleLock={() => toggleLock('menuColor')}
                    systemLocked={systemLockedKeys.has('menuColor')}
                >
                    <div className="flex gap-1.5">
                        {MENU_COLORS.map((c) => (
                            <Pill key={c} active={state.menuColor === c} onClick={() => update('menuColor', c)}>
                                {cap(c)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Menu Accent */}
                <DimensionRow
                    label="Menu Accent"
                    value={cap(state.menuAccent)}
                    rowKey="menuAccent"
                    expanded={expanded}
                    onToggle={toggle}
                    locked={lockedKeys.has('menuAccent')}
                    onToggleLock={() => toggleLock('menuAccent')}
                    systemLocked={systemLockedKeys.has('menuAccent')}
                >
                    <div className="flex gap-1.5">
                        {MENU_ACCENTS.map((a) => (
                            <Pill key={a} active={state.menuAccent === a} onClick={() => update('menuAccent', a)}>
                                {cap(a)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Card Skin */}
                <DimensionRow
                    label="Card Skin"
                    value={cap(state.skin)}
                    rowKey="skin"
                    expanded={expanded}
                    onToggle={toggle}
                    locked={lockedKeys.has('skin')}
                    onToggleLock={() => toggleLock('skin')}
                    systemLocked={systemLockedKeys.has('skin')}
                >
                    <div className="grid grid-cols-2 gap-1.5">
                        {CARD_SKINS.map((s) => (
                            <Pill key={s} active={state.skin === s} onClick={() => update('skin', s)}>
                                {cap(s)}
                            </Pill>
                        ))}
                    </div>
                </DimensionRow>

                {/* Radius */}
                <DimensionRow
                    label="Radius"
                    value={cap(state.radius)}
                    rowKey="radius"
                    expanded={expanded}
                    onToggle={toggle}
                    locked={lockedKeys.has('radius')}
                    onToggleLock={() => toggleLock('radius')}
                    systemLocked={systemLockedKeys.has('radius')}
                >
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

            {/* Preset slug */}
            <div className="mt-3 flex items-center justify-between border-t border-border/50 pt-3">
                <span className="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px] text-muted-foreground">{presetSlug}</span>
                <span className="text-[10px] text-muted-foreground/60">R = Shuffle</span>
            </div>

            {/* Actions */}
            <div className="mt-3 space-y-2">
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

// ─── Public exports ────────────────────────────────────────────────────────────

export function ThemeCustomizer() {
    const { props } = usePage<SharedData>();

    if (!props.theme?.canCustomize) {
        return null;
    }

    return <ThemeCustomizerPanel />;
}

function buildSystemLockedKeys(lockedSettings: string[]): Set<LockableKey> {
    const keys = new Set<LockableKey>();
    for (const setting of lockedSettings) {
        const key = DB_SETTING_TO_FRONTEND_KEY[setting];
        if (key) {
            keys.add(key);
        }
    }
    return keys;
}

export function ThemeCustomizerPanel() {
    const { props } = usePage<SharedData>();
    const [open, setOpen] = useState(false);
    const customizer = useThemeCustomizer(open);
    const canManageBranding = props.theme?.canManageBranding ?? false;
    const systemLockedKeys = buildSystemLockedKeys(props.theme?.lockedSettings ?? []);

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
                    <ThemeCustomizerBody {...customizer} canManageBranding={canManageBranding} systemLockedKeys={systemLockedKeys} />
                </div>
            </aside>
        </>
    );
}

export function ThemeCustomizerInline() {
    const { props } = usePage<SharedData>();
    const customizer = useThemeCustomizer(true);
    const canManageBranding = props.theme?.canManageBranding ?? false;
    const systemLockedKeys = buildSystemLockedKeys(props.theme?.lockedSettings ?? []);

    return (
        <div className="rounded-lg border bg-card p-4">
            <div className="mb-4 flex items-center gap-2">
                <Palette className="h-4 w-4" />
                <span className="text-sm font-semibold">Theme Customizer</span>
            </div>
            <ThemeCustomizerBody {...customizer} canManageBranding={canManageBranding} systemLockedKeys={systemLockedKeys} />
        </div>
    );
}
