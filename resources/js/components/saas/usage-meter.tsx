import { Progress } from '@/components/ui/progress';
import { cn } from '@/lib/utils';

interface UsageMeterProps {
    used: number;
    limit: number;
    label?: string;
    unit?: string;
    className?: string;
}

function UsageMeter({
    used,
    limit,
    label,
    unit = 'seats',
    className,
}: UsageMeterProps) {
    const percentage = limit > 0 ? Math.min((used / limit) * 100, 100) : 0;
    const isWarning = percentage >= 80 && percentage < 100;
    const isError = percentage >= 100;

    return (
        <div className={cn('space-y-1.5', className)}>
            <div className="flex items-center justify-between text-sm">
                {label && (
                    <span className="text-muted-foreground">{label}</span>
                )}
                <span
                    className={cn(
                        'font-medium tabular-nums',
                        isError
                            ? 'text-destructive'
                            : isWarning
                              ? 'text-warning'
                              : 'text-foreground',
                    )}
                >
                    {used}/{limit} {unit}
                </span>
            </div>
            <Progress
                value={percentage}
                className={cn(
                    'h-2',
                    isError
                        ? '[&>[data-slot=progress-indicator]]:bg-destructive'
                        : isWarning
                          ? '[&>[data-slot=progress-indicator]]:bg-warning'
                          : '',
                )}
            />
            {isError && (
                <p className="text-xs text-destructive">
                    You have reached your limit.
                </p>
            )}
        </div>
    );
}

export { UsageMeter };
export type { UsageMeterProps };
