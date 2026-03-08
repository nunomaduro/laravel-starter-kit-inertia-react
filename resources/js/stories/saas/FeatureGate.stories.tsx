import type { Meta, StoryObj } from '@storybook/react';

import { FeatureGate } from '@/components/saas/feature-gate';

const meta: Meta<typeof FeatureGate> = {
    title: 'SaaS/FeatureGate',
    component: FeatureGate,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        hasAccess: { control: 'boolean' },
        title: { control: 'text' },
        description: { control: 'text' },
        ctaLabel: { control: 'text' },
    },
};

export default meta;
type Story = StoryObj<typeof FeatureGate>;

export const WithAccess: Story = {
    args: {
        hasAccess: true,
        children: (
            <div className="rounded-lg border border-border bg-card p-6 text-sm text-muted-foreground">
                Premium feature content is visible here.
            </div>
        ),
    },
};

export const NoAccess: Story = {
    args: {
        hasAccess: false,
        title: 'Advanced Analytics',
        description:
            'Upgrade to Business plan to access detailed analytics and custom reports.',
        ctaLabel: 'Upgrade to Business',
        onUpgrade: () => alert('Upgrade clicked'),
        children: <div>Hidden content</div>,
    },
};

export const NoAccessNoCTA: Story = {
    args: {
        hasAccess: false,
        title: 'Enterprise Feature',
        description:
            'Contact sales to enable this feature for your organization.',
        children: <div>Hidden content</div>,
    },
};
