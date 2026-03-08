import {
    Bar,
    CartesianGrid,
    Legend,
    BarChart as RechartsBarChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import { Skeleton } from '@/components/ui/skeleton';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn } from '@/lib/utils';
import { CHART_COLORS } from './chart-colors';

export interface BarChartProps {
    data: Record<string, unknown>[];
    dataKeys: string[];
    xKey: string;
    horizontal?: boolean;
    stacked?: boolean;
    showGrid?: boolean;
    showLegend?: boolean;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

export function BarChart({
    data,
    dataKeys,
    xKey,
    horizontal = false,
    stacked = false,
    showGrid = true,
    showLegend = false,
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: BarChartProps) {
    const reducedMotion = useReducedMotion();

    if (skeleton) {
        return (
            <Skeleton
                className={cn('rounded-md', className)}
                style={{ height }}
            />
        );
    }

    const layout = horizontal ? 'vertical' : 'horizontal';

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <RechartsBarChart
                    data={data}
                    layout={layout}
                    margin={{ top: 4, right: 4, bottom: 0, left: 0 }}
                >
                    {showGrid && (
                        <CartesianGrid
                            strokeDasharray="3 3"
                            stroke="var(--border)"
                            horizontal={!horizontal}
                            vertical={horizontal}
                        />
                    )}
                    {horizontal ? (
                        <>
                            <XAxis
                                type="number"
                                tick={{
                                    fill: 'var(--muted-foreground)',
                                    fontSize: 12,
                                }}
                                axisLine={false}
                                tickLine={false}
                            />
                            <YAxis
                                dataKey={xKey}
                                type="category"
                                tick={{
                                    fill: 'var(--muted-foreground)',
                                    fontSize: 12,
                                }}
                                axisLine={{ stroke: 'var(--border)' }}
                                tickLine={false}
                                width={80}
                            />
                        </>
                    ) : (
                        <>
                            <XAxis
                                dataKey={xKey}
                                tick={{
                                    fill: 'var(--muted-foreground)',
                                    fontSize: 12,
                                }}
                                axisLine={{ stroke: 'var(--border)' }}
                                tickLine={false}
                            />
                            <YAxis
                                tick={{
                                    fill: 'var(--muted-foreground)',
                                    fontSize: 12,
                                }}
                                axisLine={false}
                                tickLine={false}
                            />
                        </>
                    )}
                    {showTooltip && (
                        <Tooltip
                            contentStyle={{
                                background: 'var(--popover)',
                                border: '1px solid var(--border)',
                                borderRadius: '8px',
                                color: 'var(--popover-foreground)',
                                fontSize: 12,
                            }}
                            cursor={{ fill: 'var(--muted)', opacity: 0.3 }}
                        />
                    )}
                    {showLegend && <Legend />}
                    {dataKeys.map((key, index) => (
                        <Bar
                            key={key}
                            dataKey={key}
                            stackId={stacked ? 'stack' : undefined}
                            fill={CHART_COLORS[index % CHART_COLORS.length]}
                            radius={[4, 4, 0, 0]}
                            isAnimationActive={!reducedMotion}
                        />
                    ))}
                </RechartsBarChart>
            </ResponsiveContainer>
        </div>
    );
}
