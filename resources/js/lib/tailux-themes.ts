/**
 * Tailux theme constants — typed references for all theme dimensions.
 * Used by ThemeCustomizer, ThemeFromProps, settings, and Storybook toolbar.
 */

export const DARK_THEMES = [
    'navy',
    'mirage',
    'mint',
    'black',
    'cinder',
] as const;
export type DarkTheme = (typeof DARK_THEMES)[number];

export const PRIMARY_COLORS = [
    'indigo',
    'blue',
    'green',
    'amber',
    'purple',
    'rose',
] as const;
export type PrimaryColor = (typeof PRIMARY_COLORS)[number];

export const LIGHT_THEMES = ['slate', 'gray', 'neutral'] as const;
export type LightTheme = (typeof LIGHT_THEMES)[number];

export const CARD_SKINS = ['shadow', 'bordered', 'flat', 'elevated'] as const;
export type CardSkin = (typeof CARD_SKINS)[number];

export const RADIUS_OPTIONS = [
    'none',
    'sm',
    'default',
    'md',
    'lg',
    'full',
] as const;
export type RadiusOption = (typeof RADIUS_OPTIONS)[number];

export const SIDEBAR_LAYOUTS = ['main', 'sideblock'] as const;
export type SidebarLayout = (typeof SIDEBAR_LAYOUTS)[number];

export const FONT_OPTIONS = [
    'inter',
    'geist-sans',
    'instrument-sans',
    'poppins',
    'outfit',
    'plus-jakarta-sans',
] as const;
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
    font?: FontOption;
    layout?: SidebarLayout;
    menuColor?: MenuColor;
    menuAccent?: MenuAccent;
}

export const THEME_PRESETS: ThemePreset[] = [
    {
        name: 'Corporate',
        dark: 'navy',
        primary: 'indigo',
        light: 'slate',
        skin: 'shadow',
        radius: 'default',
        font: 'inter',
        menuColor: 'default',
        menuAccent: 'subtle',
    },
    {
        name: 'Midnight',
        dark: 'black',
        primary: 'purple',
        light: 'slate',
        skin: 'flat',
        radius: 'none',
        font: 'geist-sans',
        menuColor: 'primary',
        menuAccent: 'strong',
    },
    {
        name: 'Sunset',
        dark: 'cinder',
        primary: 'amber',
        light: 'neutral',
        skin: 'shadow',
        radius: 'lg',
        font: 'poppins',
        menuColor: 'muted',
        menuAccent: 'subtle',
    },
    {
        name: 'Forest',
        dark: 'mint',
        primary: 'green',
        light: 'gray',
        skin: 'bordered',
        radius: 'default',
        font: 'outfit',
        menuColor: 'default',
        menuAccent: 'bordered',
    },
    {
        name: 'Ocean',
        dark: 'mirage',
        primary: 'blue',
        light: 'slate',
        skin: 'elevated',
        radius: 'md',
        font: 'plus-jakarta-sans',
        menuColor: 'primary',
        menuAccent: 'subtle',
    },
    {
        name: 'Candy',
        dark: 'navy',
        primary: 'rose',
        light: 'neutral',
        skin: 'shadow',
        radius: 'full',
        font: 'instrument-sans',
        menuColor: 'muted',
        menuAccent: 'strong',
    },
    {
        name: 'Nova',
        dark: 'mirage',
        primary: 'rose',
        light: 'slate',
        skin: 'elevated',
        radius: 'lg',
        font: 'instrument-sans',
        layout: 'sideblock',
        menuColor: 'primary',
        menuAccent: 'strong',
    },
    {
        name: 'Ember',
        dark: 'cinder',
        primary: 'amber',
        light: 'gray',
        skin: 'bordered',
        radius: 'sm',
        font: 'poppins',
        menuColor: 'muted',
        menuAccent: 'bordered',
    },
    {
        name: 'Arctic',
        dark: 'black',
        primary: 'blue',
        light: 'slate',
        skin: 'flat',
        radius: 'default',
        font: 'geist-sans',
        menuColor: 'default',
        menuAccent: 'subtle',
    },
];
