import { ResponsiveContainer, Tooltip, Treemap } from 'recharts';

import { Skeleton } from '@/components/ui/skeleton';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn } from '@/lib/utils';
import { CHART_COLORS } from './chart-colors';

export interface TreemapDatum {
    name: string;
    size?: number;
    children?: TreemapDatum[];
    color?: string;
    [key: string]: unknown;
}

export interface TreemapChartProps {
    data: TreemapDatum[];
    dataKey?: string;
    showTooltip?: boolean;
    skeleton?: boolean;
    height?: number;
    className?: string;
}

function TreemapContent(props: {
    x?: number;
    y?: number;
    width?: number;
    height?: number;
    name?: string;
    index?: number;
    depth?: number;
}) {
    const {
        x = 0,
        y = 0,
        width = 0,
        height = 0,
        name = '',
        index = 0,
        depth = 0,
    } = props;
    const color = CHART_COLORS[index % CHART_COLORS.length];

    if (depth === 0 || width < 10 || height < 10) {
        return null;
    }

    return (
        <g>
            <rect
                x={x + 1}
                y={y + 1}
                width={width - 2}
                height={height - 2}
                style={{
                    fill: color,
                    fillOpacity: 0.75,
                    stroke: 'var(--background)',
                    strokeWidth: 2,
                }}
                rx={4}
            />
            {width > 40 && height > 20 && (
                <text
                    x={x + width / 2}
                    y={y + height / 2}
                    textAnchor="middle"
                    dominantBaseline="middle"
                    fill="var(--background)"
                    fontSize={Math.min(12, width / 6)}
                    fontWeight={500}
                >
                    {name}
                </text>
            )}
        </g>
    );
}

export function TreemapChart({
    data,
    dataKey = 'size',
    showTooltip = true,
    skeleton = false,
    height = 300,
    className,
}: TreemapChartProps) {
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
                <Treemap
                    data={data}
                    dataKey={dataKey}
                    isAnimationActive={!reducedMotion}
                    content={<TreemapContent />}
                >
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
                </Treemap>
            </ResponsiveContainer>
        </div>
    );
}
