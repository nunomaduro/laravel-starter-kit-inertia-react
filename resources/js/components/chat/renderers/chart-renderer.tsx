import { useMemo } from 'react';
import type { RendererProps } from './renderer-registry';
import { registerRenderer } from './renderer-registry';

interface ChartDataPoint {
    label: string;
    value: number;
}

function ChartRenderer({ data }: RendererProps) {
    const chartType = (data.type as 'bar' | 'line') ?? 'bar';
    const title = data.title as string | undefined;
    const points = (data.points as ChartDataPoint[]) ?? [];

    const maxValue = useMemo(
        () => Math.max(...points.map((p) => p.value), 1),
        [points],
    );

    if (points.length === 0) return null;

    if (chartType === 'line') {
        return <LineChart title={title} points={points} maxValue={maxValue} />;
    }

    return <BarChart title={title} points={points} maxValue={maxValue} />;
}

function BarChart({
    title,
    points,
    maxValue,
}: {
    title?: string;
    points: ChartDataPoint[];
    maxValue: number;
}) {
    return (
        <div className="rounded-lg border p-4">
            {title && (
                <h4 className="mb-3 font-mono text-xs font-semibold tracking-tight">
                    {title}
                </h4>
            )}
            <div className="space-y-2">
                {points.map((p, i) => {
                    const pct = (p.value / maxValue) * 100;
                    return (
                        <div key={i} className="flex items-center gap-3">
                            <span className="w-20 shrink-0 truncate text-right text-xs text-muted-foreground">
                                {p.label}
                            </span>
                            <div className="relative h-5 flex-1 overflow-hidden rounded bg-muted/50">
                                <div
                                    className="h-full rounded bg-[oklch(0.65_0.14_165)] transition-all duration-200"
                                    style={{ width: `${pct}%` }}
                                />
                            </div>
                            <span className="w-12 shrink-0 font-mono text-xs">
                                {p.value}
                            </span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

function LineChart({
    title,
    points,
    maxValue,
}: {
    title?: string;
    points: ChartDataPoint[];
    maxValue: number;
}) {
    const width = 320;
    const height = 120;
    const padding = { top: 8, right: 8, bottom: 24, left: 8 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;

    const pathD = useMemo(() => {
        if (points.length < 2) return '';
        return points
            .map((p, i) => {
                const x = padding.left + (i / (points.length - 1)) * chartW;
                const y = padding.top + chartH - (p.value / maxValue) * chartH;
                return `${i === 0 ? 'M' : 'L'}${x},${y}`;
            })
            .join(' ');
    }, [points, maxValue, chartW, chartH, padding.left, padding.top]);

    return (
        <div className="rounded-lg border p-4">
            {title && (
                <h4 className="mb-3 font-mono text-xs font-semibold tracking-tight">
                    {title}
                </h4>
            )}
            <svg
                viewBox={`0 0 ${width} ${height}`}
                className="w-full"
                aria-label={title ?? 'Line chart'}
            >
                <path
                    d={pathD}
                    fill="none"
                    stroke="oklch(0.65 0.14 165)"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
                {points.map((p, i) => {
                    const x = padding.left + (i / Math.max(points.length - 1, 1)) * chartW;
                    const y = padding.top + chartH - (p.value / maxValue) * chartH;
                    return (
                        <g key={i}>
                            <circle
                                cx={x}
                                cy={y}
                                r="3"
                                fill="oklch(0.65 0.14 165)"
                            />
                            <text
                                x={x}
                                y={height - 4}
                                textAnchor="middle"
                                className="fill-muted-foreground text-[9px]"
                            >
                                {p.label}
                            </text>
                        </g>
                    );
                })}
            </svg>
        </div>
    );
}

registerRenderer('chart', ChartRenderer);

export { ChartRenderer };
