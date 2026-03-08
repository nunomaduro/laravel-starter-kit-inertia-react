import {
    Legend,
    PolarAngleAxis,
    PolarGrid,
    PolarRadiusAxis,
    Radar,
    RadarChart as RechartsRadarChart,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';

import { Skeleton } from '@/components/ui/skeleton';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn } from '@/lib/utils';
import { CHART_COLORS } from './chart-colors';

export interface RadarChartProps {
    data: Record<string, unknown>[];
    dataKeys: string[];
    angleKey: string;
    showLegend?: boolean;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

export function RadarChart({
    data,
    dataKeys,
    angleKey,
    showLegend = false,
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: RadarChartProps) {
    const reducedMotion = useReducedMotion();

    if (skeleton) {
        return (
            <Skeleton
                className={cn('rounded-md', className)}
                style={{ height }}
            />
        );
    }

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <RechartsRadarChart data={data}>
                    <PolarGrid stroke="var(--border)" />
                    <PolarAngleAxis
                        dataKey={angleKey}
                        tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }}
                    />
                    <PolarRadiusAxis
                        tick={{ fill: 'var(--muted-foreground)', fontSize: 10 }}
                        axisLine={false}
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
                        />
                    )}
                    {showLegend && <Legend />}
                    {dataKeys.map((key, index) => (
                        <Radar
                            key={key}
                            name={key}
                            dataKey={key}
                            stroke={CHART_COLORS[index % CHART_COLORS.length]}
                            fill={CHART_COLORS[index % CHART_COLORS.length]}
                            fillOpacity={0.25}
                            isAnimationActive={!reducedMotion}
                        />
                    ))}
                </RechartsRadarChart>
            </ResponsiveContainer>
        </div>
    );
}
