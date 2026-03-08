import {
    Area,
    AreaChart,
    Line,
    LineChart,
    ResponsiveContainer,
} from 'recharts';

import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn } from '@/lib/utils';
import { CHART_COLORS } from './chart-colors';

export interface SparklineProps {
    data: Record<string, unknown>[];
    dataKey: string;
    variant?: 'line' | 'area';
    color?: string;
    height?: number;
    className?: string;
}

export function Sparkline({
    data,
    dataKey,
    variant = 'line',
    color,
    height = 40,
    className,
}: SparklineProps) {
    const reducedMotion = useReducedMotion();
    const strokeColor = color ?? CHART_COLORS[0];

    if (variant === 'area') {
        return (
            <div className={cn('w-full', className)} style={{ height }}>
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart
                        data={data}
                        margin={{ top: 0, right: 0, bottom: 0, left: 0 }}
                    >
                        <Area
                            type="monotone"
                            dataKey={dataKey}
                            stroke={strokeColor}
                            fill={strokeColor}
                            fillOpacity={0.2}
                            strokeWidth={1.5}
                            dot={false}
                            isAnimationActive={!reducedMotion}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
        );
    }

    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                <LineChart
                    data={data}
                    margin={{ top: 0, right: 0, bottom: 0, left: 0 }}
                >
                    <Line
                        type="monotone"
                        dataKey={dataKey}
                        stroke={strokeColor}
                        strokeWidth={1.5}
                        dot={false}
                        isAnimationActive={!reducedMotion}
                    />
                </LineChart>
            </ResponsiveContainer>
        </div>
    );
}
