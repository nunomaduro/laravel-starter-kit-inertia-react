import type { Meta, StoryObj } from '@storybook/react';
import { AlertCircleIcon, CheckCircleIcon, InfoIcon, TriangleAlertIcon } from 'lucide-react';
import React from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const meta: Meta<typeof Alert> = {
    title: 'Feedback/Alert',
    component: Alert,
    tags: ['autodocs'],
    parameters: { layout: 'centered' },
    argTypes: {
        variant: {
            control: 'select',
            options: ['soft', 'filled', 'outlined'],
        },
        color: {
            control: 'select',
            options: ['primary', 'info', 'success', 'warning', 'error', 'neutral'],
        },
    },
};

export default meta;
type Story = StoryObj<typeof Alert>;

export const Info: Story = {
    render: () => (
        <Alert variant="soft" color="info" className="w-96">
            <InfoIcon />
            <AlertTitle>Heads up</AlertTitle>
            <AlertDescription>Your trial expires in 5 days. Upgrade to keep access.</AlertDescription>
        </Alert>
    ),
};

export const Success: Story = {
    render: () => (
        <Alert variant="soft" color="success" className="w-96">
            <CheckCircleIcon />
            <AlertTitle>Success</AlertTitle>
            <AlertDescription>Your profile has been updated successfully.</AlertDescription>
        </Alert>
    ),
};

export const Warning: Story = {
    render: () => (
        <Alert variant="soft" color="warning" className="w-96">
            <TriangleAlertIcon />
            <AlertTitle>Warning</AlertTitle>
            <AlertDescription>You are approaching your usage limit (90%).</AlertDescription>
        </Alert>
    ),
};

export const Error: Story = {
    render: () => (
        <Alert variant="soft" color="error" className="w-96">
            <AlertCircleIcon />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>Failed to save changes. Please try again.</AlertDescription>
        </Alert>
    ),
};

export const AllVariants: Story = {
    render: () => (
        <div className="space-y-3 w-96">
            <Alert variant="soft" color="info"><InfoIcon /><AlertTitle>Soft / Info</AlertTitle></Alert>
            <Alert variant="filled" color="success"><CheckCircleIcon /><AlertTitle>Filled / Success</AlertTitle></Alert>
            <Alert variant="outlined" color="warning"><TriangleAlertIcon /><AlertTitle>Outlined / Warning</AlertTitle></Alert>
            <Alert variant="filled" color="error"><AlertCircleIcon /><AlertTitle>Filled / Error</AlertTitle></Alert>
        </div>
    ),
};
