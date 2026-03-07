import type { Meta, StoryObj } from '@storybook/react';
import { ZapIcon } from 'lucide-react';
import React from 'react';

import { PricingCard } from '@/components/composed/pricing-card';

const STARTER_FEATURES = [
    { label: '5 team members', included: true },
    { label: '10 GB storage', included: true },
    { label: 'Basic analytics', included: true },
    { label: 'Email support', included: true },
    { label: 'Advanced reporting', included: false },
    { label: 'Custom domain', included: false },
];

const PRO_FEATURES = [
    { label: 'Unlimited team members', included: true },
    { label: '100 GB storage', included: true },
    { label: 'Advanced analytics', included: true },
    { label: 'Priority support', included: true },
    { label: 'Advanced reporting', included: true },
    { label: 'Custom domain', included: true },
];

const meta: Meta<typeof PricingCard> = {
    title: 'Composed/PricingCard',
    component: PricingCard,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        billingPeriod: { control: 'select', options: ['month', 'year', 'one-time'] },
        isPopular: { control: 'boolean' },
        isCurrent: { control: 'boolean' },
        disabled: { control: 'boolean' },
    },
};

export default meta;
type Story = StoryObj<typeof PricingCard>;

export const Starter: Story = {
    args: {
        name: 'Starter',
        description: 'Perfect for small teams just getting started.',
        price: 29,
        billingPeriod: 'month',
        features: STARTER_FEATURES,
        ctaLabel: 'Get started',
        onSelect: () => {},
    },
    render: (args) => <div className="w-72"><PricingCard {...args} /></div>,
};

export const Popular: Story = {
    args: {
        name: 'Pro',
        description: 'For growing teams that need more power.',
        price: 79,
        billingPeriod: 'month',
        yearlyPrice: 69,
        features: PRO_FEATURES,
        ctaLabel: 'Upgrade to Pro',
        isPopular: true,
        badge: 'Most popular',
        icon: <ZapIcon className="size-4" />,
        onSelect: () => {},
    },
    render: (args) => <div className="w-72"><PricingCard {...args} /></div>,
};

export const Current: Story = {
    args: {
        name: 'Starter',
        description: 'Your current plan.',
        price: 29,
        billingPeriod: 'month',
        features: STARTER_FEATURES,
        isCurrent: true,
        ctaLabel: 'Current plan',
        ctaVariant: 'outline',
    },
    render: (args) => <div className="w-72"><PricingCard {...args} /></div>,
};

export const Enterprise: Story = {
    args: {
        name: 'Enterprise',
        description: 'Custom solutions for large organizations.',
        price: 'custom',
        features: PRO_FEATURES,
        ctaLabel: 'Contact sales',
        ctaVariant: 'outline',
    },
    render: (args) => <div className="w-72"><PricingCard {...args} /></div>,
};

export const PricingGrid: Story = {
    render: () => (
        <div className="flex gap-4 flex-wrap justify-center">
            <div className="w-64">
                <PricingCard
                    name="Starter"
                    price={29}
                    billingPeriod="month"
                    features={STARTER_FEATURES}
                    ctaLabel="Get started"
                    isCurrent
                    ctaVariant="outline"
                />
            </div>
            <div className="w-64">
                <PricingCard
                    name="Pro"
                    price={79}
                    billingPeriod="month"
                    features={PRO_FEATURES}
                    ctaLabel="Upgrade"
                    isPopular
                    badge="Most popular"
                    onSelect={() => {}}
                />
            </div>
            <div className="w-64">
                <PricingCard
                    name="Enterprise"
                    price="custom"
                    features={PRO_FEATURES}
                    ctaLabel="Contact sales"
                    ctaVariant="outline"
                />
            </div>
        </div>
    ),
};
