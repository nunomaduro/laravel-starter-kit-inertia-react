import * as React from 'react';

import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

export type ProgressRingColor =
    | 'primary'
    | 'success'
    | 'warning'
    | 'error'
    | 'info';

const colorMap: Record<ProgressRingColor, string> = {
    primary: 'var(--primary)',
    success: 'var(--color-success, oklch(0.6 0.15 145))',
    warning: 'var(--color-warning, oklch(0.7 0.15 70))',
    error: 'var(--color-error, oklch(0.55 0.2 25))',
    info: 'var(--color-info, oklch(0.6 0.15 210))',
};

export interface ProgressRingProps {
    value: number;
    max?: number;
    label?: React.ReactNode;
    sublabel?: React.ReactNode;
    showValue?: boolean;
    color?: ProgressRingColor;
    strokeWidth?: number;
    skeleton?: boolean;
    size?: number;
    className?: string;
}

export function ProgressRing({
    value,
    max = 100,
    label,
    sublabel,
    showValue = true,
    color = 'primary',
    strokeWidth,
    skeleton = false,
    size = 120,
    className,
}: ProgressRingProps) {
    if (skeleton) {
        return (
            <Skeleton
                className={cn('rounded-full', className)}
                style={{ width: size, height: size }}
            />
        );
    }

    const pct = Math.min(Math.max(value / max, 0), 1);
    const sw = strokeWidth ?? Math.max(6, size * 0.08);
    const radius = (size - sw) / 2;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference * (1 - pct);
    const cx = size / 2;
    const stroke = colorMap[color];

    return (
        <div
            className={cn('inline-flex flex-col items-center', className)}
            role="progressbar"
            aria-valuenow={value}
            aria-valuemin={0}
            aria-valuemax={max}
        >
            <svg
                width={size}
                height={size}
                viewBox={`0 0 ${size} ${size}`}
                style={{ transform: 'rotate(-90deg)' }}
            >
                {/* Track */}
                <circle
                    cx={cx}
                    cy={cx}
                    r={radius}
                    fill="none"
                    stroke="var(--muted)"
                    strokeWidth={sw}
                />
                {/* Progress */}
                <circle
                    cx={cx}
                    cy={cx}
                    r={radius}
                    fill="none"
                    stroke={stroke}
                    strokeWidth={sw}
                    strokeDasharray={circumference}
                    strokeDashoffset={offset}
                    strokeLinecap="round"
                    style={{ transition: 'stroke-dashoffset 0.4s ease' }}
                />
            </svg>
            {/* Center label rendered outside SVG to avoid rotation */}
            <div
                className="pointer-events-none absolute flex flex-col items-center justify-center"
                style={{ width: size, height: size }}
            >
                {showValue && (
                    <span className="text-sm leading-none font-semibold text-foreground">
                        {Math.round(pct * 100)}%
                    </span>
                )}
                {label && (
                    <span className="mt-0.5 text-xs leading-none text-muted-foreground">
                        {label}
                    </span>
                )}
            </div>
            {sublabel && (
                <span className="mt-1 text-xs text-muted-foreground">
                    {sublabel}
                </span>
            )}
        </div>
    );
}
