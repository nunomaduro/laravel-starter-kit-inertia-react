import * as React from 'react';

import { AreaChart } from '@/components/charts/area-chart';
import { BarChart } from '@/components/charts/bar-chart';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { StatCard } from '@/components/ui/stat-card';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { cn } from '@/lib/utils';

export interface MetricCardConfig {
    id: string;
    title: string;
    value: React.ReactNode;
    description?: React.ReactNode;
    icon?: React.ReactNode;
    trend?: {
        value: number;
        label?: string;
        direction?: 'up' | 'down' | 'neutral';
    };
    badge?: React.ReactNode;
    isLoading?: boolean;
}

export interface ChartDataPoint {
    [key: string]: string | number;
}

export interface MetricDashboardProps {
    metrics: MetricCardConfig[];
    chartTitle?: string;
    chartData?: ChartDataPoint[];
    chartXKey?: string;
    chartDataKeys?: string[];
    chartType?: 'area' | 'bar';
    periodOptions?: { label: string; value: string }[];
    onPeriodChange?: (value: string) => void;
    className?: string;
    isLoading?: boolean;
    columns?: 2 | 3 | 4;
}

function MetricDashboard({
    metrics,
    chartTitle = 'Overview',
    chartData = [],
    chartXKey = 'date',
    chartDataKeys = ['value'],
    chartType = 'area',
    periodOptions,
    onPeriodChange,
    className,
    isLoading = false,
    columns = 4,
}: MetricDashboardProps) {
    const [selectedPeriod, setSelectedPeriod] = React.useState(
        periodOptions?.[0]?.value ?? '',
    );

    const handlePeriodChange = (value: string) => {
        setSelectedPeriod(value);
        onPeriodChange?.(value);
    };

    const gridCols: Record<number, string> = {
        2: 'grid-cols-1 sm:grid-cols-2',
        3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    };

    return (
        <div
            data-slot="metric-dashboard"
            className={cn('space-y-4', className)}
        >
            <div className={cn('grid gap-4', gridCols[columns])}>
                {metrics.map((metric) => (
                    <StatCard
                        key={metric.id}
                        title={metric.title}
                        value={metric.value}
                        description={metric.description}
                        icon={metric.icon}
                        trend={metric.trend}
                        badge={metric.badge}
                        isLoading={metric.isLoading ?? isLoading}
                    />
                ))}
            </div>

            {chartData.length > 0 && (
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium">
                            {chartTitle}
                        </CardTitle>
                        {periodOptions && periodOptions.length > 0 && (
                            <Tabs
                                value={selectedPeriod}
                                onValueChange={handlePeriodChange}
                            >
                                <TabsList className="h-7">
                                    {periodOptions.map((opt) => (
                                        <TabsTrigger
                                            key={opt.value}
                                            value={opt.value}
                                            className="px-2.5 text-xs"
                                        >
                                            {opt.label}
                                        </TabsTrigger>
                                    ))}
                                </TabsList>
                            </Tabs>
                        )}
                    </CardHeader>
                    <CardContent className="pb-4">
                        {chartType === 'area' ? (
                            <AreaChart
                                data={chartData}
                                xKey={chartXKey}
                                dataKeys={chartDataKeys}
                                height={220}
                                skeleton={isLoading}
                            />
                        ) : (
                            <BarChart
                                data={chartData}
                                xKey={chartXKey}
                                dataKeys={chartDataKeys}
                                height={220}
                                skeleton={isLoading}
                            />
                        )}
                    </CardContent>
                </Card>
            )}
        </div>
    );
}

export { MetricDashboard };
