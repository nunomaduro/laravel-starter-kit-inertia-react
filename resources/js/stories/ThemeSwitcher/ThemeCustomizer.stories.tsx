import type { Meta, StoryObj } from '@storybook/react';

import { ThemeCustomizerPanel } from '@/components/ui/theme-customizer';

// ThemeCustomizerPanel renders a floating panel trigger button.
// Inertia usePage() is mocked in preview.tsx with a theme object that
// matches the SharedData shape, so handleSave/handleReset are no-ops in Storybook.

const meta: Meta<typeof ThemeCustomizerPanel> = {
    title: 'ThemeSwitcher/ThemeCustomizerPanel',
    component: ThemeCustomizerPanel,
    tags: ['autodocs'],
    parameters: {
        layout: 'fullscreen',
        docs: {
            description: {
                component:
                    'The floating theme customizer panel. In the real app, it is gated by `canCustomize: true` on the theme prop. In Storybook it renders unconditionally so you can test all theme dimensions live.',
            },
        },
    },
};

export default meta;
type Story = StoryObj<typeof ThemeCustomizerPanel>;

export const Default: Story = {
    render: () => (
        <div className="min-h-screen bg-background p-8">
            <div className="max-w-lg space-y-4">
                <h1 className="text-2xl font-bold text-foreground">
                    Theme Customizer
                </h1>
                <p className="text-sm text-muted-foreground">
                    Click the palette button on the right edge of the screen to
                    open the theme customizer panel. All changes are reflected
                    live on this preview using CSS custom properties.
                </p>
                <div className="grid grid-cols-2 gap-4">
                    {[
                        'primary',
                        'secondary',
                        'muted',
                        'accent',
                        'destructive',
                    ].map((color) => (
                        <div
                            key={color}
                            className="h-12 rounded-lg border border-border"
                            style={{ background: `var(--color-${color})` }}
                            title={color}
                        />
                    ))}
                </div>
            </div>
            <ThemeCustomizerPanel />
        </div>
    ),
};
