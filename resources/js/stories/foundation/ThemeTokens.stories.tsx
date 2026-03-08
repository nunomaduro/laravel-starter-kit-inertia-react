import type { Meta, StoryObj } from '@storybook/react';

import {
    CARD_SKINS,
    DARK_THEMES,
    LIGHT_THEMES,
    PRIMARY_COLORS,
    RADIUS_OPTIONS,
    THEME_PRESETS,
} from '@/lib/tailux-themes';

function TokenGroup({
    title,
    tokens,
}: {
    title: string;
    tokens: readonly string[];
}) {
    return (
        <div className="space-y-2">
            <h3 className="font-mono text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                {title}
            </h3>
            <div className="flex flex-wrap gap-2">
                {tokens.map((t) => (
                    <span
                        key={t}
                        className="inline-flex items-center rounded-md border border-border bg-muted px-2.5 py-1 font-mono text-xs"
                    >
                        {t}
                    </span>
                ))}
            </div>
        </div>
    );
}

function ThemeTokensDemo() {
    return (
        <div className="space-y-8 bg-background p-6 text-foreground">
            <div>
                <h2 className="mb-1 text-lg font-semibold">
                    Tailux Theme Tokens
                </h2>
                <p className="mb-6 text-sm text-muted-foreground">
                    All theme dimensions available via the theme customizer and
                    Storybook toolbar.
                </p>
                <div className="space-y-6">
                    <TokenGroup title="Dark Themes" tokens={DARK_THEMES} />
                    <TokenGroup
                        title="Primary Colors"
                        tokens={PRIMARY_COLORS}
                    />
                    <TokenGroup title="Light Schemes" tokens={LIGHT_THEMES} />
                    <TokenGroup title="Card Skins" tokens={CARD_SKINS} />
                    <TokenGroup title="Border Radius" tokens={RADIUS_OPTIONS} />
                </div>
            </div>

            <div>
                <h3 className="mb-3 text-sm font-semibold">Theme Presets</h3>
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                    {THEME_PRESETS.map((preset) => (
                        <div
                            key={preset.name}
                            className="space-y-2 rounded-lg border border-border bg-card p-4"
                        >
                            <p className="text-sm font-semibold text-card-foreground">
                                {preset.name}
                            </p>
                            <div className="grid grid-cols-2 gap-x-4 gap-y-1">
                                {[
                                    ['dark', preset.dark],
                                    ['primary', preset.primary],
                                    ['light', preset.light],
                                    ['skin', preset.skin],
                                    ['radius', preset.radius],
                                ].map(([k, v]) => (
                                    <div
                                        key={k}
                                        className="flex items-baseline gap-1.5"
                                    >
                                        <span className="text-[10px] text-muted-foreground">
                                            {k}
                                        </span>
                                        <span className="font-mono text-[10px] text-foreground">
                                            {v}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

const meta: Meta = {
    title: 'Foundation/Theme Tokens',
    component: ThemeTokensDemo,
    tags: ['autodocs'],
    parameters: { layout: 'fullscreen' },
};

export default meta;

export const Tokens: StoryObj = {
    render: () => <ThemeTokensDemo />,
};
