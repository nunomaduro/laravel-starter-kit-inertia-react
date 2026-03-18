export interface KpiCardProps {
    label: string;
    value: string;
    trend: 'up' | 'down' | 'neutral';
    trendLabel: string;
}

export function KpiCard({ label, value, trend, trendLabel }: KpiCardProps) {
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
