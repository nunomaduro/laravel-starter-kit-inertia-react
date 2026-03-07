/**
 * Tailux theme constants — typed references for all theme dimensions.
 * Used by ThemeCustomizer, ThemeFromProps, settings, and Storybook toolbar.
 */

export const DARK_THEMES = ['navy', 'mirage', 'mint', 'black', 'cinder'] as const;
export type DarkTheme = (typeof DARK_THEMES)[number];

export const PRIMARY_COLORS = ['indigo', 'blue', 'green', 'amber', 'purple', 'rose'] as const;
export type PrimaryColor = (typeof PRIMARY_COLORS)[number];

export const LIGHT_THEMES = ['slate', 'gray', 'neutral'] as const;
export type LightTheme = (typeof LIGHT_THEMES)[number];

export const CARD_SKINS = ['shadow', 'bordered', 'flat', 'elevated'] as const;
export type CardSkin = (typeof CARD_SKINS)[number];

export const RADIUS_OPTIONS = ['none', 'sm', 'default', 'md', 'lg', 'full'] as const;
export type RadiusOption = (typeof RADIUS_OPTIONS)[number];

export const SIDEBAR_LAYOUTS = ['main', 'sideblock'] as const;
export type SidebarLayout = (typeof SIDEBAR_LAYOUTS)[number];

export const FONT_OPTIONS = ['inter', 'geist-sans', 'poppins', 'outfit', 'plus-jakarta-sans'] as const;
export type FontOption = (typeof FONT_OPTIONS)[number];

export const MENU_COLORS = ['default', 'primary', 'muted'] as const;
export type MenuColor = (typeof MENU_COLORS)[number];

export const MENU_ACCENTS = ['subtle', 'strong', 'bordered'] as const;
export type MenuAccent = (typeof MENU_ACCENTS)[number];

export interface ThemePreset {
    name: string;
    dark: DarkTheme;
    primary: PrimaryColor;
    light: LightTheme;
    skin: CardSkin;
    radius: RadiusOption;
}

export const THEME_PRESETS: ThemePreset[] = [
    {
        name: 'Corporate',
        dark: 'navy',
        primary: 'indigo',
        light: 'slate',
        skin: 'shadow',
        radius: 'default',
    },
    {
        name: 'Midnight',
        dark: 'black',
        primary: 'purple',
        light: 'slate',
        skin: 'flat',
        radius: 'none',
    },
    {
        name: 'Sunset',
        dark: 'cinder',
        primary: 'amber',
        light: 'neutral',
        skin: 'shadow',
        radius: 'lg',
    },
    {
        name: 'Forest',
        dark: 'mint',
        primary: 'green',
        light: 'gray',
        skin: 'bordered',
        radius: 'default',
    },
    {
        name: 'Ocean',
        dark: 'mirage',
        primary: 'blue',
        light: 'slate',
        skin: 'elevated',
        radius: 'md',
    },
    {
        name: 'Candy',
        dark: 'navy',
        primary: 'rose',
        light: 'neutral',
        skin: 'shadow',
        radius: 'full',
    },
];
