import { router, usePage } from '@inertiajs/react';
import { Palette, X } from 'lucide-react';
import { type ReactNode, useCallback, useState } from 'react';
import { toast } from 'sonner';

import { cn } from '@/lib/utils';
import {
    CARD_SKINS,
    DARK_THEMES,
    LIGHT_THEMES,
    PRIMARY_COLORS,
    RADIUS_OPTIONS,
    THEME_PRESETS,
    type CardSkin,
    type DarkTheme,
    type LightTheme,
    type PrimaryColor,
    type RadiusOption,
} from '@/lib/tailux-themes';
import type { SharedData } from '@/types';

interface ThemeState {
    dark: DarkTheme;
    primary: PrimaryColor;
    light: LightTheme;
    skin: CardSkin;
    radius: RadiusOption;
}

function applyThemeState(state: ThemeState): void {
    const root = document.documentElement;
    root.setAttribute('data-theme-dark', state.dark);
    root.setAttribute('data-theme-primary', state.primary);
    root.setAttribute('data-theme-light', state.light);
    root.setAttribute('data-card-skin', state.skin);
    root.setAttribute('data-radius', state.radius);
}

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

const LIGHT_THEME_LABELS: Record<LightTheme, string> = {
    slate: 'Slate',
    gray: 'Gray',
    neutral: 'Neutral',
};

const CARD_SKIN_LABELS: Record<CardSkin, string> = {
    shadow: 'Shadow',
    bordered: 'Bordered',
    flat: 'Flat',
    elevated: 'Elevated',
};

const RADIUS_LABELS: Record<RadiusOption, string> = {
    none: 'None',
    sm: 'SM',
    default: 'Def',
    md: 'MD',
    lg: 'LG',
    full: 'Full',
};

const RADIUS_PREVIEW_CLASS: Record<RadiusOption, string> = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    default: 'rounded',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full',
};

function getInitialState(theme: SharedData['theme']): ThemeState {
    return {
        dark: (theme?.dark as DarkTheme | undefined) ?? 'navy',
        primary: (theme?.primary as PrimaryColor | undefined) ?? 'indigo',
        light: (theme?.light as LightTheme | undefined) ?? 'slate',
        skin: (theme?.skin as CardSkin | undefined) ?? 'shadow',
        radius: 'default',
    };
}

export function ThemeCustomizer() {
    const { props } = usePage<SharedData>();

    if (!props.theme?.canCustomize) {
        return null;
    }

    return <ThemeCustomizerPanel />;
}

function useThemeCustomizerState() {
    const { props } = usePage<SharedData>();
    const [saving, setSaving] = useState(false);
    const [resetting, setResetting] = useState(false);
    const [state, setState] = useState<ThemeState>(() => getInitialState(props.theme));

    const update = useCallback(<K extends keyof ThemeState>(key: K, value: ThemeState[K]) => {
        const next = { ...state, [key]: value };
        setState(next);
        applyThemeState(next);
    }, [state]);

    const applyPreset = useCallback((preset: (typeof THEME_PRESETS)[number]) => {
        const next: ThemeState = {
            dark: preset.dark,
            primary: preset.primary,
            light: preset.light,
            skin: preset.skin,
            radius: preset.radius,
        };
        setState(next);
        applyThemeState(next);
    }, []);

    const handleSave = useCallback(() => {
        setSaving(true);
        router.post(
            '/org/theme',
            { dark: state.dark, primary: state.primary, light: state.light, skin: state.skin, radius: state.radius },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => toast.success('Theme saved for your organization.'),
                onError: () => toast.error('Failed to save theme.'),
                onFinish: () => setSaving(false),
            },
        );
    }, [state]);

    const handleReset = useCallback(() => {
        setResetting(true);
        router.delete('/org/theme', {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                const theme = (page.props as unknown as SharedData).theme;
                const defaults = getInitialState(theme);
                setState(defaults);
                applyThemeState(defaults);
                toast.success('Theme reset to organization defaults.');
            },
            onError: () => toast.error('Failed to reset theme.'),
            onFinish: () => setResetting(false),
        });
    }, []);

    return { state, saving, resetting, update, applyPreset, handleSave, handleReset };
}

interface ThemeCustomizerBodyProps {
    state: ThemeState;
    saving: boolean;
    resetting: boolean;
    update: <K extends keyof ThemeState>(key: K, value: ThemeState[K]) => void;
    applyPreset: (preset: (typeof THEME_PRESETS)[number]) => void;
    handleSave: () => void;
    handleReset: () => void;
}

function ThemeCustomizerBody({ state, saving, resetting, update, applyPreset, handleSave, handleReset }: ThemeCustomizerBodyProps) {
    return (
        <div className="space-y-5">
            {/* Presets */}
            <Section title="Presets">
                <div className="grid grid-cols-3 gap-2">
                    {THEME_PRESETS.map((preset) => (
                        <button
                            key={preset.name}
                            type="button"
                            onClick={() => applyPreset(preset)}
                            className={cn(
                                'flex flex-col items-center gap-1 rounded-lg border px-2 py-2',
                                'text-xs font-medium transition-colors hover:border-primary hover:bg-muted',
                                state.dark === preset.dark &&
                                    state.primary === preset.primary &&
                                    state.light === preset.light
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border text-muted-foreground',
                            )}
                        >
                            <span
                                className="h-6 w-6 rounded-full border-2 border-white shadow"
                                style={{ background: PRIMARY_COLORS_HEX[preset.primary] }}
                            />
                            {preset.name}
                        </button>
                    ))}
                </div>
            </Section>

            {/* Dark Theme */}
            <Section title="Dark Theme">
                <div className="flex gap-2">
                    {DARK_THEMES.map((theme) => (
                        <button
                            key={theme}
                            type="button"
                            title={theme}
                            onClick={() => update('dark', theme)}
                            className={cn(
                                'h-7 w-7 rounded-full border-2 transition-transform hover:scale-110',
                                state.dark === theme
                                    ? 'border-primary scale-110 ring-2 ring-primary/30'
                                    : 'border-transparent',
                            )}
                            style={{ background: DARK_THEME_COLORS[theme] }}
                        />
                    ))}
                </div>
            </Section>

            {/* Primary Color */}
            <Section title="Primary Color">
                <div className="flex flex-wrap gap-2">
                    {PRIMARY_COLORS.map((color) => (
                        <button
                            key={color}
                            type="button"
                            title={color}
                            onClick={() => update('primary', color)}
                            className={cn(
                                'h-7 w-7 rotate-45 rounded-sm border-2 transition-transform hover:scale-110',
                                state.primary === color
                                    ? 'border-foreground scale-110'
                                    : 'border-transparent',
                            )}
                            style={{ background: PRIMARY_COLORS_HEX[color] }}
                        />
                    ))}
                </div>
            </Section>

            {/* Light Scheme */}
            <Section title="Light Scheme">
                <div className="flex gap-2">
                    {LIGHT_THEMES.map((theme) => (
                        <button
                            key={theme}
                            type="button"
                            onClick={() => update('light', theme)}
                            className={cn(
                                'flex-1 rounded-md border px-2 py-1.5 text-xs font-medium transition-colors',
                                state.light === theme
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
                            )}
                        >
                            {LIGHT_THEME_LABELS[theme]}
                        </button>
                    ))}
                </div>
            </Section>

            {/* Card Skin */}
            <Section title="Card Skin">
                <div className="grid grid-cols-2 gap-2">
                    {CARD_SKINS.map((skin) => (
                        <button
                            key={skin}
                            type="button"
                            onClick={() => update('skin', skin)}
                            className={cn(
                                'rounded-md border px-2 py-1.5 text-xs font-medium transition-colors',
                                state.skin === skin
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
                            )}
                        >
                            {CARD_SKIN_LABELS[skin]}
                        </button>
                    ))}
                </div>
            </Section>

            {/* Border Radius */}
            <Section title="Border Radius">
                <div className="flex flex-wrap gap-2">
                    {RADIUS_OPTIONS.map((r) => (
                        <button
                            key={r}
                            type="button"
                            title={r}
                            onClick={() => update('radius', r)}
                            className={cn(
                                'flex h-9 w-9 items-center justify-center border text-xs font-medium transition-colors',
                                RADIUS_PREVIEW_CLASS[r],
                                state.radius === r
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border text-muted-foreground hover:border-primary hover:bg-muted',
                            )}
                        >
                            {RADIUS_LABELS[r]}
                        </button>
                    ))}
                </div>
            </Section>

            {/* Actions */}
            <div className="space-y-2 pt-2">
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

function ThemeCustomizerPanel() {
    const [open, setOpen] = useState(false);
    const { state, saving, resetting, update, applyPreset, handleSave, handleReset } = useThemeCustomizerState();

    return (
        <>
            {/* Floating toggle button */}
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-label="Open theme customizer"
                className={cn(
                    'fixed right-0 top-1/2 z-50 -translate-y-1/2 translate-x-0',
                    'flex h-10 w-10 items-center justify-center',
                    'rounded-l-lg bg-primary text-primary-foreground shadow-lg',
                    'transition-all hover:w-12',
                )}
            >
                <Palette className="h-5 w-5" />
            </button>

            {/* Backdrop */}
            {open && (
                <div
                    className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm"
                    onClick={() => setOpen(false)}
                />
            )}

            {/* Panel */}
            <aside
                className={cn(
                    'fixed right-0 top-0 z-50 h-full w-80 overflow-y-auto',
                    'border-l bg-background shadow-xl',
                    'transition-transform duration-300',
                    open ? 'translate-x-0' : 'translate-x-full',
                )}
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b px-4 py-3">
                    <div className="flex items-center gap-2">
                        <Palette className="h-4 w-4" />
                        <span className="font-semibold text-sm">Theme Customizer</span>
                    </div>
                    <button
                        type="button"
                        onClick={() => setOpen(false)}
                        aria-label="Close"
                        className="rounded p-1 hover:bg-muted"
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>

                <div className="p-4">
                    <ThemeCustomizerBody
                        state={state}
                        saving={saving}
                        resetting={resetting}
                        update={update}
                        applyPreset={applyPreset}
                        handleSave={handleSave}
                        handleReset={handleReset}
                    />
                </div>
            </aside>
        </>
    );
}

export function ThemeCustomizerInline() {
    const { state, saving, resetting, update, applyPreset, handleSave, handleReset } = useThemeCustomizerState();

    return (
        <div className="rounded-lg border bg-card p-4">
            <div className="mb-4 flex items-center gap-2">
                <Palette className="h-4 w-4" />
                <span className="font-semibold text-sm">Theme Customizer</span>
            </div>
            <ThemeCustomizerBody
                state={state}
                saving={saving}
                resetting={resetting}
                update={update}
                applyPreset={applyPreset}
                handleSave={handleSave}
                handleReset={handleReset}
            />
        </div>
    );
}

function Section({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="space-y-2">
            <h3 className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{title}</h3>
            {children}
        </div>
    );
}
