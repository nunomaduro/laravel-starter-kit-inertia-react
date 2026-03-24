import { Skeleton } from '@/components/ui/skeleton';

interface StatCardProps {
    label: string;
    value: number | string | undefined;
    href: string;
    icon: React.FC<{ className?: string }>;
    dataPan: string;
    trend?: { value: number; direction: 'up' | 'down' } | null;
}

export function StatCard({
    label,
    value,
    href,
    icon: Icon,
    dataPan,
    trend,
}: StatCardProps) {
    return (
        <a
            href={href}
            className="flex flex-col gap-1 rounded-xl border bg-card p-6 transition-colors hover:bg-accent/50"
            data-pan={dataPan}
        >
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <span className="text-sm">{label}</span>
            </div>
            <div className="flex items-end justify-between gap-2">
                <p className="text-2xl font-mono font-semibold tabular-nums">
                    {value !== undefined ? (
                        value
                    ) : (
                        <Skeleton className="h-8 w-16" />
                    )}
                </p>
                {trend != null && (
                    <span
                        className={
                            trend.direction === 'up'
                                ? 'mb-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400'
                                : 'mb-0.5 text-xs font-medium text-destructive'
                        }
                    >
                        {trend.direction === 'up' ? '↑' : '↓'}{' '}
                        {Math.abs(trend.value)}%
                    </span>
                )}
            </div>
        </a>
    );
}
