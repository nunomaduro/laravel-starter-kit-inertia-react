import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

import {
    CARD_SKINS,
    DARK_THEMES,
    LIGHT_THEMES,
    PRIMARY_COLORS,
    RADIUS_OPTIONS,
    THEME_PRESETS,
} from '@/lib/tailux-themes';

function TokenGroup({ title, tokens }: { title: string; tokens: readonly string[] }) {
    return (
        <div className="space-y-2">
            <h3 className="text-xs font-mono font-semibold uppercase tracking-wide text-muted-foreground">{title}</h3>
            <div className="flex flex-wrap gap-2">
                {tokens.map((t) => (
                    <span key={t} className="inline-flex items-center rounded-md border border-border bg-muted px-2.5 py-1 text-xs font-mono">
                        {t}
                    </span>
                ))}
            </div>
        </div>
    );
}

function ThemeTokensDemo() {
    return (
        <div className="p-6 space-y-8 bg-background text-foreground">
            <div>
                <h2 className="text-lg font-semibold mb-1">Tailux Theme Tokens</h2>
                <p className="text-sm text-muted-foreground mb-6">
                    All theme dimensions available via the theme customizer and Storybook toolbar.
                </p>
                <div className="space-y-6">
                    <TokenGroup title="Dark Themes" tokens={DARK_THEMES} />
                    <TokenGroup title="Primary Colors" tokens={PRIMARY_COLORS} />
                    <TokenGroup title="Light Schemes" tokens={LIGHT_THEMES} />
                    <TokenGroup title="Card Skins" tokens={CARD_SKINS} />
                    <TokenGroup title="Border Radius" tokens={RADIUS_OPTIONS} />
                </div>
            </div>

            <div>
                <h3 className="text-sm font-semibold mb-3">Theme Presets</h3>
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                    {THEME_PRESETS.map((preset) => (
                        <div key={preset.name} className="rounded-lg border border-border bg-card p-4 space-y-2">
                            <p className="font-semibold text-sm text-card-foreground">{preset.name}</p>
                            <div className="grid grid-cols-2 gap-x-4 gap-y-1">
                                {[
                                    ['dark', preset.dark],
                                    ['primary', preset.primary],
                                    ['light', preset.light],
                                    ['skin', preset.skin],
                                    ['radius', preset.radius],
                                ].map(([k, v]) => (
                                    <div key={k} className="flex gap-1.5 items-baseline">
                                        <span className="text-[10px] text-muted-foreground">{k}</span>
                                        <span className="font-mono text-[10px] text-foreground">{v}</span>
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
