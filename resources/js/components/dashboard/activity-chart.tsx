import { Skeleton } from '@/components/ui/skeleton';
import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    XAxis,
    YAxis,
} from 'recharts';

export interface WeeklyStat {
    name: string;
    value: number;
}

interface ActivityChartProps {
    data: WeeklyStat[];
}

export function ActivityChart({ data }: ActivityChartProps) {
    return (
        <div
            className="rounded-xl border bg-card p-6"
            data-pan="dashboard-chart"
        >
            <h3 className="mb-4 font-medium">Activity this week</h3>
            {data.length === 0 ? (
                <div className="h-[200px] w-full">
                    <div className="flex h-full items-center justify-center">
                        <Skeleton className="h-[180px] w-full rounded" />
                    </div>
                </div>
            ) : (
                <div className="h-[200px] w-full text-primary">
                    <ResponsiveContainer
                        width="100%"
                        height={200}
                        minHeight={200}
                    >
                        <AreaChart data={data}>
                            <CartesianGrid
                                strokeDasharray="3 3"
                                className="stroke-muted"
                            />
                            <XAxis dataKey="name" className="text-xs" />
                            <YAxis className="text-xs" allowDecimals={false} />
                            <Area
                                type="monotone"
                                dataKey="value"
                                stroke="currentColor"
                                fill="currentColor"
                                fillOpacity={0.2}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </div>
            )}
        </div>
    );
}
