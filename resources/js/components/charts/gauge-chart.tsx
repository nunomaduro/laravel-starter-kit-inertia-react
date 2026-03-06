import * as React from 'react';

import { cn } from '@/lib/utils';
import { Skeleton } from '@/components/ui/skeleton';

export interface GaugeChartProps {
    value: number;
    max?: number;
    label?: string;
    sublabel?: string;
    showValue?: boolean;
    color?: string;
    trackColor?: string;
    skeleton?: boolean;
    size?: number;
    className?: string;
}

export function GaugeChart({
    value,
    max = 100,
    label,
    sublabel,
    showValue = true,
    color,
    trackColor,
    skeleton = false,
    size = 200,
    className,
}: GaugeChartProps) {
    if (skeleton) {
        return <Skeleton className={cn('rounded-full', className)} style={{ width: size, height: size }} />;
    }

    const pct = Math.min(Math.max(value / max, 0), 1);
    // Arc spans 240° (from -120° to 120°), starting at bottom-left
    const arcDegrees = 240;
    const startAngle = -120; // degrees from top
    const cx = size / 2;
    const cy = size / 2;
    const radius = (size / 2) * 0.75;
    const strokeWidth = size * 0.08;

    function polarToCartesian(angle: number) {
        const rad = ((angle - 90) * Math.PI) / 180;
        return {
            x: cx + radius * Math.cos(rad),
            y: cy + radius * Math.sin(rad),
        };
    }

    function describeArc(startDeg: number, endDeg: number) {
        const s = polarToCartesian(startDeg);
        const e = polarToCartesian(endDeg);
        const largeArc = endDeg - startDeg > 180 ? 1 : 0;
        return `M ${s.x} ${s.y} A ${radius} ${radius} 0 ${largeArc} 1 ${e.x} ${e.y}`;
    }

    const endAngle = startAngle + arcDegrees * pct;
    const trackPath = describeArc(startAngle, startAngle + arcDegrees);
    const valuePath = pct > 0 ? describeArc(startAngle, endAngle) : '';

    const strokeColor = color ?? 'var(--primary)';
    const track = trackColor ?? 'var(--muted)';

    return (
        <div className={cn('flex flex-col items-center', className)}>
            <svg
                width={size}
                height={size * 0.75}
                viewBox={`0 0 ${size} ${size * 0.75}`}
                aria-valuenow={value}
                aria-valuemin={0}
                aria-valuemax={max}
                role="meter"
            >
                {/* Track */}
                <path
                    d={trackPath}
                    fill="none"
                    stroke={track}
                    strokeWidth={strokeWidth}
                    strokeLinecap="round"
                />
                {/* Value arc */}
                {valuePath && (
                    <path
                        d={valuePath}
                        fill="none"
                        stroke={strokeColor}
                        strokeWidth={strokeWidth}
                        strokeLinecap="round"
                    />
                )}
                {/* Center text */}
                {showValue && (
                    <text
                        x={cx}
                        y={cy * 0.9}
                        textAnchor="middle"
                        dominantBaseline="middle"
                        fill="var(--foreground)"
                        fontSize={size * 0.16}
                        fontWeight={600}
                    >
                        {value}
                    </text>
                )}
            </svg>
            {(label ?? sublabel) && (
                <div className="mt-1 text-center">
                    {label && <p className="text-sm font-medium text-foreground">{label}</p>}
                    {sublabel && <p className="text-xs text-muted-foreground">{sublabel}</p>}
                </div>
            )}
        </div>
    );
}
