import {
    Funnel,
    LabelList,
    FunnelChart as RechartsFunnelChart,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';

import { Skeleton } from '@/components/ui/skeleton';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn } from '@/lib/utils';
import { CHART_COLORS } from './chart-colors';

export interface FunnelDatum {
    name: string;
    value: number;
    fill?: string;
}

export interface FunnelChartProps {
    data: FunnelDatum[];
    showLabels?: boolean;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

export function FunnelChart({
    data,
    showLabels = true,
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: FunnelChartProps) {
    const reducedMotion = useReducedMotion();

    if (skeleton) {
        return (
            <Skeleton
                className={cn('rounded-md', className)}
                style={{ height }}
            />
        );
    }

    const enriched = data.map((d, index) => ({
        ...d,
        fill: d.fill ?? CHART_COLORS[index % CHART_COLORS.length],
    }));

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <RechartsFunnelChart>
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
                    <Funnel
                        dataKey="value"
                        data={enriched}
                        isAnimationActive={!reducedMotion}
                    >
                        {showLabels && (
                            <LabelList
                                position="right"
                                fill="var(--foreground)"
                                stroke="none"
                                dataKey="name"
                                style={{ fontSize: 12 }}
                            />
                        )}
                    </Funnel>
                </RechartsFunnelChart>
            </ResponsiveContainer>
        </div>
    );
}
