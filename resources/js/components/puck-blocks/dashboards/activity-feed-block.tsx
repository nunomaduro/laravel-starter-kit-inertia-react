import { router } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

export interface ActivityItem {
    id: string;
    message: string;
    timestamp: string;
    icon?: string;
}

export interface ActivityFeedBlockProps {
    title: string;
    dataSource: string;
    maxItems: number;
    refreshInterval?: number | null;
    data?: ActivityItem[];
}

const DEFAULT_ICON = '●';

export function ActivityFeedBlock({
    title,
    maxItems,
    refreshInterval,
    data,
}: ActivityFeedBlockProps) {
    const [items, setItems] = useState<ActivityItem[]>(data ?? []);
    const [lastRefreshed, setLastRefreshed] = useState<Date>(new Date());

    useEffect(() => {
        setItems(data ?? []);
    }, [data]);

    const refresh = useCallback(() => {
        router.reload({ only: ['dashboard'], onFinish: () => setLastRefreshed(new Date()) });
    }, []);

    useEffect(() => {
        if (!refreshInterval || refreshInterval < 5) return;
        const timer = setInterval(refresh, refreshInterval * 1000);
        return () => clearInterval(timer);
    }, [refreshInterval, refresh]);

    const displayItems = items.slice(0, maxItems);

    return (
        <div className="rounded-lg border bg-card p-4">
            <div className="mb-3 flex items-center justify-between">
                <h3 className="font-semibold">{title}</h3>
                {refreshInterval && (
                    <span className="text-xs text-muted-foreground">
                        Last: {lastRefreshed.toLocaleTimeString()}
                    </span>
                )}
            </div>
            {displayItems.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    No recent activity
                </p>
            ) : (
                <ul className="space-y-3">
                    {displayItems.map((item, i) => (
                        <li
                            key={item.id ?? i}
                            className="flex items-start gap-3 border-b pb-3 last:border-0"
                        >
                            <span className="mt-0.5 text-sm text-muted-foreground">
                                {item.icon ?? DEFAULT_ICON}
                            </span>
                            <div className="min-w-0 flex-1">
                                <p className="text-sm">{item.message}</p>
                                <p className="mt-0.5 text-xs text-muted-foreground">
                                    {item.timestamp}
                                </p>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
