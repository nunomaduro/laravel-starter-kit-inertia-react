/**
 * CVA color variant definitions for semantic colors.
 * Provides `filled`, `soft`, and `outlined` styles across all 7 semantic colors.
 * Import and compose with component-specific CVA definitions.
 */

import { cva, type VariantProps } from 'class-variance-authority';

export const colorVariants = cva('', {
    variants: {
        variant: {
            filled: '',
            soft: '',
            outlined: '',
        },
        color: {
            primary: '',
            secondary: '',
            info: '',
            success: '',
            warning: '',
            error: '',
            neutral: '',
        },
    },
    compoundVariants: [
        // Primary
        {
            variant: 'filled',
            color: 'primary',
            class: 'bg-primary text-primary-foreground',
        },
        {
            variant: 'soft',
            color: 'primary',
            class: 'bg-primary/10 text-primary dark:bg-primary/20',
        },
        {
            variant: 'outlined',
            color: 'primary',
            class: 'border border-primary text-primary bg-transparent',
        },

        // Secondary
        {
            variant: 'filled',
            color: 'secondary',
            class: 'bg-secondary text-white dark:text-black',
        },
        {
            variant: 'soft',
            color: 'secondary',
            class: 'bg-secondary/10 text-secondary dark:bg-secondary/20',
        },
        {
            variant: 'outlined',
            color: 'secondary',
            class: 'border border-secondary text-secondary bg-transparent',
        },

        // Info
        { variant: 'filled', color: 'info', class: 'bg-info text-white' },
        {
            variant: 'soft',
            color: 'info',
            class: 'bg-info/10 text-info dark:bg-info/20',
        },
        {
            variant: 'outlined',
            color: 'info',
            class: 'border border-info text-info bg-transparent',
        },

        // Success
        { variant: 'filled', color: 'success', class: 'bg-success text-white' },
        {
            variant: 'soft',
            color: 'success',
            class: 'bg-success/10 text-success dark:bg-success/20',
        },
        {
            variant: 'outlined',
            color: 'success',
            class: 'border border-success text-success bg-transparent',
        },

        // Warning
        { variant: 'filled', color: 'warning', class: 'bg-warning text-black' },
        {
            variant: 'soft',
            color: 'warning',
            class: 'bg-warning/10 text-warning dark:bg-warning/20',
        },
        {
            variant: 'outlined',
            color: 'warning',
            class: 'border border-warning text-warning bg-transparent',
        },

        // Error
        { variant: 'filled', color: 'error', class: 'bg-error text-white' },
        {
            variant: 'soft',
            color: 'error',
            class: 'bg-error/10 text-error dark:bg-error/20',
        },
        {
            variant: 'outlined',
            color: 'error',
            class: 'border border-error text-error bg-transparent',
        },

        // Neutral
        {
            variant: 'filled',
            color: 'neutral',
            class: 'bg-neutral-700 text-white dark:bg-neutral-300 dark:text-neutral-900',
        },
        {
            variant: 'soft',
            color: 'neutral',
            class: 'bg-muted text-muted-foreground',
        },
        {
            variant: 'outlined',
            color: 'neutral',
            class: 'border border-border text-muted-foreground bg-transparent',
        },
    ],
    defaultVariants: {
        variant: 'filled',
        color: 'primary',
    },
});

export type ColorVariantsProps = VariantProps<typeof colorVariants>;
export type ColorVariant = NonNullable<ColorVariantsProps['variant']>;
export type SemanticColor = NonNullable<ColorVariantsProps['color']>;
