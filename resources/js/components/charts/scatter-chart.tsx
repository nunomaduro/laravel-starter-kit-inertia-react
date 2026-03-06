import * as React from 'react';
import {
    CartesianGrid,
    ResponsiveContainer,
    Scatter,
    ScatterChart as RechartsScatterChart,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import { cn } from '@/lib/utils';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { Skeleton } from '@/components/ui/skeleton';
import { CHART_COLORS } from './chart-colors';

export interface ScatterChartProps {
    data: Record<string, unknown>[];
    xKey: string;
    yKey: string;
    xLabel?: string;
    yLabel?: string;
    showGrid?: boolean;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

export function ScatterChart({
    data,
    xKey,
    yKey,
    xLabel,
    yLabel,
    showGrid = true,
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: ScatterChartProps) {
    const reducedMotion = useReducedMotion();

    if (skeleton) {
        return <Skeleton className={cn('rounded-md', className)} style={{ height }} />;
    }

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <RechartsScatterChart margin={{ top: 4, right: 4, bottom: 0, left: 0 }}>
                    {showGrid && (
                        <CartesianGrid strokeDasharray="3 3" stroke="var(--border)" />
                    )}
                    <XAxis
                        dataKey={xKey}
                        name={xLabel ?? xKey}
                        type="number"
                        tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }}
                        axisLine={{ stroke: 'var(--border)' }}
                        tickLine={false}
                    />
                    <YAxis
                        dataKey={yKey}
                        name={yLabel ?? yKey}
                        type="number"
                        tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }}
                        axisLine={false}
                        tickLine={false}
                    />
                    {showTooltip && (
                        <Tooltip
                            contentStyle={{
                                background: 'var(--popover)',
                                border: '1px solid var(--border)',
                                borderRadius: '8px',
                                color: 'var(--popover-foreground)',
                                fontSize: 12,
                            }}
                            cursor={{ strokeDasharray: '3 3' }}
                        />
                    )}
                    <Scatter
                        data={data}
                        fill={CHART_COLORS[0]}
                        isAnimationActive={!reducedMotion}
                    />
                </RechartsScatterChart>
            </ResponsiveContainer>
        </div>
    );
}
