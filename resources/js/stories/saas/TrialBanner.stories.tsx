import type { Meta, StoryObj } from '@storybook/react';

import { TrialBanner } from '@/components/saas/trial-banner';

const meta: Meta<typeof TrialBanner> = {
    title: 'SaaS/TrialBanner',
    component: TrialBanner,
    tags: ['autodocs'],
    parameters: { layout: 'fullscreen' },
    argTypes: {
        daysRemaining: { control: { type: 'range', min: 0, max: 30 } },
    },
};

export default meta;
type Story = StoryObj<typeof TrialBanner>;

export const Healthy: Story = {
    args: { daysRemaining: 12, storageKey: 'sb-trial-healthy' },
};

export const Expiring: Story = {
    args: {
        daysRemaining: 2,
        storageKey: 'sb-trial-expiring',
        onUpgrade: () => alert('Upgrade clicked'),
    },
};

export const LastDay: Story = {
    args: {
        daysRemaining: 1,
        storageKey: 'sb-trial-last-day',
        onUpgrade: () => alert('Upgrade clicked'),
    },
};
