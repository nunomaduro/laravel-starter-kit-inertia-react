/**
 * Shared chart color palette using CSS custom properties so they adapt
 * to the current theme (light/dark/primary color swatches).
 */
export const CHART_COLORS: string[] = [
    'var(--primary)',
    'var(--color-info, oklch(0.6 0.15 210))',
    'var(--color-success, oklch(0.6 0.15 145))',
    'var(--color-warning, oklch(0.7 0.15 70))',
    'var(--color-error, oklch(0.55 0.2 25))',
    'var(--color-secondary, oklch(0.6 0.1 280))',
];
