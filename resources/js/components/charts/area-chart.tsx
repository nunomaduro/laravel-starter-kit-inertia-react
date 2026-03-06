import * as React from 'react';
import {
    Area,
    AreaChart as RechartsAreaChart,
    CartesianGrid,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import { cn } from '@/lib/utils';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { Skeleton } from '@/components/ui/skeleton';
import { CHART_COLORS } from './chart-colors';

export interface AreaChartProps {
    data: Record<string, unknown>[];
    dataKeys: string[];
    xKey: string;
    stacked?: boolean;
    showGrid?: boolean;
    showLegend?: boolean;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

export function AreaChart({
    data,
    dataKeys,
    xKey,
    stacked = false,
    showGrid = true,
    showLegend = false,
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: AreaChartProps) {
    const reducedMotion = useReducedMotion();

    if (skeleton) {
        return <Skeleton className={cn('rounded-md', className)} style={{ height }} />;
    }

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <RechartsAreaChart data={data} margin={{ top: 4, right: 4, bottom: 0, left: 0 }}>
                    {showGrid && (
                        <CartesianGrid
                            strokeDasharray="3 3"
                            stroke="var(--border)"
                            vertical={false}
                        />
                    )}
                    <XAxis
                        dataKey={xKey}
                        tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }}
                        axisLine={{ stroke: 'var(--border)' }}
                        tickLine={false}
                    />
                    <YAxis
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
                            cursor={{ stroke: 'var(--border)' }}
                        />
                    )}
                    {showLegend && <Legend />}
                    {dataKeys.map((key, index) => (
                        <Area
                            key={key}
                            type="monotone"
                            dataKey={key}
                            stackId={stacked ? 'stack' : undefined}
                            stroke={CHART_COLORS[index % CHART_COLORS.length]}
                            fill={CHART_COLORS[index % CHART_COLORS.length]}
                            fillOpacity={0.2}
                            strokeWidth={2}
                            dot={false}
                            isAnimationActive={!reducedMotion}
                        />
                    ))}
                </RechartsAreaChart>
            </ResponsiveContainer>
        </div>
    );
}
