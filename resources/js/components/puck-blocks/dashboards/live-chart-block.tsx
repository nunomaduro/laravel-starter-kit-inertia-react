import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Line,
    LineChart,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import type { PieLabelRenderProps } from 'recharts';

export type LiveChartType = 'bar' | 'line' | 'pie';

export interface LiveChartBlockProps {
    dataSource: string;
    title: string;
    chartType: LiveChartType;
    xKey: string;
    yKey: string;
    refreshInterval?: number | null;
    data?: Record<string, unknown>[];
}

const COLORS = [
    'var(--color-primary)',
    'var(--color-chart-2, #8884d8)',
    'var(--color-chart-3, #82ca9d)',
    'var(--color-chart-4, #ffc658)',
    'var(--color-chart-5, #ff7300)',
    '#a855f7',
    '#ec4899',
    '#14b8a6',
];

export function LiveChartBlock({
    title,
    chartType,
    xKey,
    yKey,
    refreshInterval,
    data,
}: LiveChartBlockProps) {
    const [rows, setRows] = useState<Record<string, unknown>[]>(data ?? []);
    const [lastRefreshed, setLastRefreshed] = useState<Date>(new Date());

    useEffect(() => {
        setRows(data ?? []);
    }, [data]);

    const refresh = useCallback(() => {
        router.reload({ only: ['dashboard'], onFinish: () => setLastRefreshed(new Date()) });
    }, []);

    useEffect(() => {
        if (!refreshInterval || refreshInterval < 5) return;
        const timer = setInterval(refresh, refreshInterval * 1000);
        return () => clearInterval(timer);
    }, [refreshInterval, refresh]);

    const chartData = useMemo(
        () =>
            rows.map((row) => ({
                ...row,
                [yKey]: Number(row[yKey]) || 0,
            })),
        [rows, yKey],
    );

    if (rows.length === 0) {
        return (
            <div className="rounded-lg border bg-card p-4">
                {title && (
                    <h3 className="mb-3 text-lg font-semibold">{title}</h3>
                )}
                <p className="text-sm text-muted-foreground">
                    No data available. Select a data source.
                </p>
            </div>
        );
    }

    return (
        <div className="rounded-lg border bg-card p-4">
            <div className="mb-3 flex items-center justify-between">
                {title && <h3 className="text-lg font-semibold">{title}</h3>}
                {refreshInterval && (
                    <span className="text-xs text-muted-foreground">
                        Auto-refresh: {refreshInterval}s &middot; Last:{' '}
                        {lastRefreshed.toLocaleTimeString()}
                    </span>
                )}
            </div>
            <div className="h-64 w-full">
                <ResponsiveContainer width="100%" height="100%">
                    {chartType === 'pie' ? (
                        <PieChart>
                            <Pie
                                data={chartData}
                                dataKey={yKey}
                                nameKey={xKey}
                                cx="50%"
                                cy="50%"
                                outerRadius={80}
                                label={(entry: PieLabelRenderProps) =>
                                    String(
                                        (
                                            entry as unknown as Record<
                                                string,
                                                unknown
                                            >
                                        )[xKey] ?? '',
                                    )
                                }
                            >
                                {chartData.map((_entry, index) => (
                                    <Cell
                                        key={`cell-${index}`}
                                        fill={COLORS[index % COLORS.length]}
                                    />
                                ))}
                            </Pie>
                            <Tooltip />
                            <Legend />
                        </PieChart>
                    ) : chartType === 'line' ? (
                        <LineChart data={chartData}>
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis dataKey={xKey} />
                            <YAxis />
                            <Tooltip />
                            <Legend />
                            <Line
                                type="monotone"
                                dataKey={yKey}
                                stroke="var(--color-primary)"
                                strokeWidth={2}
                            />
                        </LineChart>
                    ) : (
                        <BarChart data={chartData}>
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis dataKey={xKey} />
                            <YAxis />
                            <Tooltip />
                            <Legend />
                            <Bar
                                dataKey={yKey}
                                fill="var(--color-primary)"
                                radius={[4, 4, 0, 0]}
                            />
                        </BarChart>
                    )}
                </ResponsiveContainer>
            </div>
        </div>
    );
}
