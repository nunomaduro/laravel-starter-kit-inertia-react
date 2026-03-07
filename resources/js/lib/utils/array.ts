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

/**
 * Group an array of objects by a key.
 * @example groupBy([{type:'a'},{type:'b'},{type:'a'}], 'type') // {a:[...],b:[...]}
 */
export function groupBy<T>(array: T[], key: keyof T): Record<string, T[]> {
    return array.reduce<Record<string, T[]>>((acc, item) => {
        const groupKey = String(item[key]);
        (acc[groupKey] ??= []).push(item);

        return acc;
    }, {});
}

/**
 * Clamp a number between min and max.
 * @example clamp(150, 0, 100) // 100
 */
export function clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
}

/**
 * Pick a random element from an array.
 * @example randomItem(['a', 'b', 'c']) // 'b'
 */
export function randomItem<T>(array: T[]): T {
    return array[Math.floor(Math.random() * array.length)];
}

export interface ThemeConfig {
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

/**
 * Pick a random valid theme configuration from all Tailux theme dimensions.
 */
export function randomTheme(): ThemeConfig {
    return {
        dark: randomItem([...DARK_THEMES]),
        primary: randomItem([...PRIMARY_COLORS]),
        light: randomItem([...LIGHT_THEMES]),
        skin: randomItem([...CARD_SKINS]),
        radius: randomItem([...RADIUS_OPTIONS]),
        layout: randomItem([...SIDEBAR_LAYOUTS]),
        font: randomItem([...FONT_OPTIONS]),
        menuColor: randomItem([...MENU_COLORS]),
        menuAccent: randomItem([...MENU_ACCENTS]),
    };
}
