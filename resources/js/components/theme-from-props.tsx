import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

interface ThemeProps {
    preset?: string;
    base_color?: string;
    radius?: string;
    font?: string;
    default_appearance?: string;
}

/**
 * Applies theme from page.props.theme (and later branding overrides) to documentElement
 * so CSS vars in themes.css take effect. Runs on every Inertia load.
 */
export function ThemeFromProps() {
    const { props } = usePage();
    const theme = (props.theme as ThemeProps | undefined) ?? {};
    const branding = props.branding as
        | { themePreset?: string; themeRadius?: string; themeFont?: string }
        | undefined;

    useEffect(() => {
        const root = document.documentElement;
        const preset = branding?.themePreset ?? theme.preset ?? 'default';
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

    return null;
}
