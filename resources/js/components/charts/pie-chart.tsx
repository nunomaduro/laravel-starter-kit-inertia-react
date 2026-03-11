import {
    Cell,
    Legend,
    Pie,
    PieChart as RechartsPieChart,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';

import { Skeleton } from '@/components/ui/skeleton';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn } from '@/lib/utils';
import { CHART_COLORS } from './chart-colors';

export interface PieChartDatum {
    name: string;
    value: number;
    color?: string;
}

export interface PieChartProps {
    data: PieChartDatum[];
    donut?: boolean;
    showLegend?: boolean;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

export function PieChart({
    data,
    donut = false,
    showLegend = true,
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: PieChartProps) {
    const reducedMotion = useReducedMotion();

    if (skeleton) {
        return (
            <Skeleton
                className={cn('rounded-md', className)}
                style={{ height }}
            />
        );
    }

    const innerRadius = donut ? '55%' : 0;

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <RechartsPieChart>
                    {showTooltip && (
                        <Tooltip
                            contentStyle={{
                                background: 'var(--popover)',
                                border: '1px solid var(--border)',
                                borderRadius: '8px',
                                color: 'var(--popover-foreground)',
                                fontSize: 12,
                            }}
                        />
                    )}
                    {showLegend && <Legend />}
                    <Pie
                        data={data}
                        cx="50%"
                        cy="50%"
                        innerRadius={innerRadius}
                        outerRadius="75%"
                        dataKey="value"
                        nameKey="name"
                        isAnimationActive={!reducedMotion}
                        strokeWidth={2}
                        stroke="var(--background)"
                    >
                        {data.map((entry, index) => (
                            <Cell
                                key={entry.name ?? String(entry.value)}
                                fill={
                                    entry.color ??
                                    CHART_COLORS[index % CHART_COLORS.length]
                                }
                            />
                        ))}
                    </Pie>
                </RechartsPieChart>
            </ResponsiveContainer>
        </div>
    );
}
