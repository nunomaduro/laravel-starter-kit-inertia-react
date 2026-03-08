import * as React from 'react';

import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

export interface HeatmapDatum {
    x: string;
    y: string;
    value: number;
}

export interface HeatmapChartProps {
    data: HeatmapDatum[];
    xLabels?: string[];
    yLabels?: string[];
    showTooltip?: boolean;
    skeleton?: boolean;
    cellSize?: number;
    className?: string;
}

export function HeatmapChart({
    data,
    xLabels: xLabelsProp,
    yLabels: yLabelsProp,
    showTooltip = true,
    skeleton = false,
    cellSize = 32,
    className,
}: HeatmapChartProps) {
    const [tooltip, setTooltip] = React.useState<{
        datum: HeatmapDatum;
        x: number;
        y: number;
    } | null>(null);

    if (skeleton) {
        return (
            <Skeleton
                className={cn('rounded-md', className)}
                style={{ height: cellSize * 7 + 40 }}
            />
        );
    }

    const xLabels = xLabelsProp ?? [...new Set(data.map((d) => d.x))];
    const yLabels = yLabelsProp ?? [...new Set(data.map((d) => d.y))];

    const values = data.map((d) => d.value);
    const minVal = Math.min(...values, 0);
    const maxVal = Math.max(...values, 1);

    function getOpacity(value: number): number {
        if (maxVal === minVal) {
            return 0.5;
        }
        return (value - minVal) / (maxVal - minVal);
    }

    function getCellValue(x: string, y: string): number | undefined {
        return data.find((d) => d.x === x && d.y === y)?.value;
    }

    return (
        <div className={cn('relative overflow-auto', className)}>
            <div
                className="grid"
                style={{
                    gridTemplateColumns: `auto repeat(${xLabels.length}, ${cellSize}px)`,
                    gap: 2,
                }}
            >
                {/* Top-left corner */}
                <div style={{ width: 60 }} />
                {/* X labels */}
                {xLabels.map((x) => (
                    <div
                        key={x}
                        className="flex items-center justify-center truncate text-[10px] text-muted-foreground"
                        style={{ height: 20 }}
                    >
                        {x}
                    </div>
                ))}
                {/* Rows */}
                {yLabels.map((y) => (
                    <React.Fragment key={y}>
                        {/* Y label */}
                        <div
                            className="flex items-center justify-end truncate pr-2 text-[10px] text-muted-foreground"
                            style={{ width: 60, height: cellSize }}
                        >
                            {y}
                        </div>
                        {/* Cells */}
                        {xLabels.map((x) => {
                            const val = getCellValue(x, y);
                            const opacity =
                                val !== undefined ? getOpacity(val) : 0;
                            return (
                                <div
                                    key={x}
                                    className="cursor-default rounded-sm transition-opacity"
                                    style={{
                                        width: cellSize,
                                        height: cellSize,
                                        background: `color-mix(in oklch, var(--primary) ${Math.round(opacity * 100)}%, transparent)`,
                                        opacity: val !== undefined ? 1 : 0.15,
                                    }}
                                    onMouseEnter={(e) => {
                                        if (!showTooltip || val === undefined) {
                                            return;
                                        }
                                        const rect =
                                            e.currentTarget.getBoundingClientRect();
                                        setTooltip({
                                            datum: { x, y, value: val },
                                            x: rect.left + rect.width / 2,
                                            y: rect.top,
                                        });
                                    }}
                                    onMouseLeave={() => setTooltip(null)}
                                />
                            );
                        })}
                    </React.Fragment>
                ))}
            </div>
            {/* Tooltip */}
            {showTooltip && tooltip && (
                <div
                    className="pointer-events-none fixed z-50 -translate-x-1/2 -translate-y-full rounded-lg border border-border bg-popover px-2 py-1 text-xs text-popover-foreground shadow-md"
                    style={{ left: tooltip.x, top: tooltip.y - 4 }}
                >
                    <span className="font-medium">
                        {tooltip.datum.x} / {tooltip.datum.y}:
                    </span>{' '}
                    {tooltip.datum.value}
                </div>
            )}
        </div>
    );
}
