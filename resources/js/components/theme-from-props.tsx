import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

import type { SharedData } from '@/types';

type Mode = 'dark' | 'light' | 'system';

const prefersDark = (): boolean =>
    typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;

const applyMode = (mode: Mode): void => {
    const isDark = mode === 'dark' || (mode === 'system' && prefersDark());
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
};

/**
 * Applies theme from page.props.theme (and later branding overrides) to documentElement
 * so CSS vars in themes.css take effect. Runs on every Inertia load.
 */
export function ThemeFromProps() {
    const { props } = usePage<SharedData>();
    const theme = props.theme ?? {};
    const branding = props.branding;

    // Apply legacy data-theme / data-radius / data-font attributes (backward-compatible)
    useEffect(() => {
        const root = document.documentElement;
        const userPreset = localStorage.getItem('theme-preset');
        const preset = branding?.themePreset ?? userPreset ?? theme.preset ?? 'default';
        const radius = branding?.themeRadius ?? theme.radius ?? 'default';
        const font = branding?.themeFont ?? theme.font ?? 'instrument-sans';
        const baseColor = theme.base_color ?? 'neutral';

        root.setAttribute('data-theme', preset);
        root.setAttribute('data-radius', radius);
        root.setAttribute('data-font', font);
        root.setAttribute('data-base-color', baseColor);
    }, [
        theme.preset,
        theme.radius,
        theme.font,
        theme.base_color,
        branding?.themePreset,
        branding?.themeRadius,
        branding?.themeFont,
    ]);

    // Apply Tailux theme attributes from DB settings
    useEffect(() => {
        const root = document.documentElement;

        if (theme.dark) {
            root.setAttribute('data-theme-dark', theme.dark);
        }

        if (theme.primary) {
            root.setAttribute('data-theme-primary', theme.primary);
        }

        if (theme.light) {
            root.setAttribute('data-theme-light', theme.light);
        }

        if (theme.skin) {
            root.setAttribute('data-card-skin', theme.skin);
        }

        if (theme.layout) {
            root.setAttribute('data-sidebar-layout', theme.layout);
        }

        if (theme.menuColor) {
            root.setAttribute('data-menu-color', theme.menuColor);
        }

        if (theme.menuAccent) {
            root.setAttribute('data-menu-accent', theme.menuAccent);
        }
    }, [theme.dark, theme.primary, theme.light, theme.skin, theme.layout, theme.menuColor, theme.menuAccent]);

    // Apply user mode (dark/light/system) on mount and when it changes
    useEffect(() => {
        const mode: Mode = (theme.userMode as Mode | undefined) ?? 'system';
        applyMode(mode);

        if (mode !== 'system') {
            return;
        }

        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = () => applyMode('system');
        mq.addEventListener('change', handler);

        return () => mq.removeEventListener('change', handler);
    }, [theme.userMode]);

    return null;
}
