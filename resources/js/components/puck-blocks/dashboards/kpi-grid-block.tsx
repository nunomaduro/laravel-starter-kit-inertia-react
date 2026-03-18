export interface KpiGridItem {
    label: string;
    value: string;
    trend: 'up' | 'down' | 'neutral';
    trendLabel: string;
}

export interface KpiGridBlockProps {
    items: KpiGridItem[];
    columns: 2 | 3 | 4;
}

function KpiGridCard({ label, value, trend, trendLabel }: KpiGridItem) {
    const trendColor =
        trend === 'up'
            ? 'text-green-600'
            : trend === 'down'
              ? 'text-red-600'
              : 'text-muted-foreground';

    const trendIcon =
        trend === 'up' ? '↑' : trend === 'down' ? '↓' : '→';

    return (
        <div className="rounded-lg border bg-card p-4">
            <p className="text-sm font-medium text-muted-foreground">{label}</p>
            <p className="mt-1 text-2xl font-bold">{value}</p>
            {trendLabel && (
                <p className={`mt-1 text-xs ${trendColor}`}>
                    {trendIcon} {trendLabel}
                </p>
            )}
        </div>
    );
}

export function KpiGridBlock({ items, columns }: KpiGridBlockProps) {
    const gridCols =
        columns === 4
            ? 'grid-cols-2 md:grid-cols-4'
            : columns === 3
              ? 'grid-cols-1 md:grid-cols-3'
              : 'grid-cols-1 md:grid-cols-2';

    return (
        <div className={`grid gap-4 ${gridCols}`}>
            {items.map((item, i) => (
                <KpiGridCard key={i} {...item} />
            ))}
        </div>
    );
}
