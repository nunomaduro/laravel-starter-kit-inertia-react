import * as React from 'react';
import {
    CartesianGrid,
    Legend,
    Line,
    LineChart as RechartsLineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import { cn } from '@/lib/utils';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { Skeleton } from '@/components/ui/skeleton';
import { CHART_COLORS } from './chart-colors';

export interface LineChartProps {
    data: Record<string, unknown>[];
    dataKeys: string[];
    xKey: string;
    curved?: boolean;
    showDots?: boolean;
    showGrid?: boolean;
    showLegend?: boolean;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

export function LineChart({
    data,
    dataKeys,
    xKey,
    curved = true,
    showDots = false,
    showGrid = true,
    showLegend = false,
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: LineChartProps) {
    const reducedMotion = useReducedMotion();

    if (skeleton) {
        return <Skeleton className={cn('rounded-md', className)} style={{ height }} />;
    }

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <RechartsLineChart data={data} margin={{ top: 4, right: 4, bottom: 0, left: 0 }}>
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
                        <Line
                            key={key}
                            type={curved ? 'monotone' : 'linear'}
                            dataKey={key}
                            stroke={CHART_COLORS[index % CHART_COLORS.length]}
                            strokeWidth={2}
                            dot={showDots ? { r: 3 } : false}
                            activeDot={{ r: 5 }}
                            isAnimationActive={!reducedMotion}
                        />
                    ))}
                </RechartsLineChart>
            </ResponsiveContainer>
        </div>
    );
}
