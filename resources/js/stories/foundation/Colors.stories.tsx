import type { Meta, StoryObj } from '@storybook/react';

const PALETTE = [
    { name: 'background', var: '--color-background' },
    { name: 'foreground', var: '--color-foreground' },
    { name: 'primary', var: '--color-primary' },
    { name: 'primary-foreground', var: '--color-primary-foreground' },
    { name: 'secondary', var: '--color-secondary' },
    { name: 'secondary-foreground', var: '--color-secondary-foreground' },
    { name: 'muted', var: '--color-muted' },
    { name: 'muted-foreground', var: '--color-muted-foreground' },
    { name: 'accent', var: '--color-accent' },
    { name: 'accent-foreground', var: '--color-accent-foreground' },
    { name: 'destructive', var: '--color-destructive' },
    { name: 'border', var: '--color-border' },
    { name: 'input', var: '--color-input' },
    { name: 'ring', var: '--color-ring' },
    { name: 'card', var: '--color-card' },
    { name: 'popover', var: '--color-popover' },
    { name: 'success', var: '--color-success' },
    { name: 'warning', var: '--color-warning' },
    { name: 'info', var: '--color-info' },
    { name: 'error', var: '--color-error' },
];

function ColorSwatch({ name, cssVar }: { name: string; cssVar: string }) {
    return (
        <div className="flex flex-col gap-1.5">
            <div
                className="h-12 w-full rounded-md border border-border shadow-sm"
                style={{ background: `var(${cssVar})` }}
            />
            <div className="space-y-0.5">
                <p className="text-xs font-medium text-foreground">{name}</p>
                <p className="font-mono text-[10px] text-muted-foreground">
                    {cssVar}
                </p>
            </div>
        </div>
    );
}

function ColorsDemo() {
    return (
        <div className="min-h-screen space-y-6 bg-background p-6 text-foreground">
            <div>
                <h2 className="mb-1 text-lg font-semibold">
                    Semantic Color Tokens
                </h2>
                <p className="mb-4 text-sm text-muted-foreground">
                    All colors adapt to the active theme via CSS custom
                    properties.
                </p>
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    {PALETTE.map(({ name, var: cssVar }) => (
                        <ColorSwatch key={name} name={name} cssVar={cssVar} />
                    ))}
                </div>
            </div>
        </div>
    );
}

const meta: Meta = {
    title: 'Foundation/Colors',
    component: ColorsDemo,
    tags: ['autodocs'],
    parameters: { layout: 'fullscreen' },
};

export default meta;

export const AllColors: StoryObj = {
    render: () => <ColorsDemo />,
};
