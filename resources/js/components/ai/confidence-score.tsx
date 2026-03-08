import { cn } from '@/lib/utils';

export interface ConfidenceScoreProps {
    /** Value between 0 and 1. */
    score: number;
    /** Show the numeric label. */
    showLabel?: boolean;
    /** Visual size preset. */
    size?: 'sm' | 'md' | 'lg';
    className?: string;
}

const SIZE_CLASSES = {
    sm: 'h-1 text-xs',
    md: 'h-1.5 text-sm',
    lg: 'h-2 text-base',
} satisfies Record<NonNullable<ConfidenceScoreProps['size']>, string>;

function scoreColor(score: number): string {
    if (score >= 0.8) return 'bg-success';
    if (score >= 0.5) return 'bg-warning';
    return 'bg-error';
}

/**
 * Horizontal progress bar indicating an AI confidence score (0–1).
 * Color transitions from red → amber → green based on the score.
 */
export function ConfidenceScore({
    score,
    showLabel = true,
    size = 'md',
    className,
}: ConfidenceScoreProps) {
    const clamped = Math.min(Math.max(score, 0), 1);
    const pct = Math.round(clamped * 100);

    return (
        <div className={cn('space-y-1', className)}>
            {showLabel && (
                <div className="flex items-center justify-between">
                    <span
                        className={cn(
                            'font-medium text-muted-foreground',
                            SIZE_CLASSES[size],
                        )}
                    >
                        Confidence
                    </span>
                    <span
                        className={cn(
                            'font-semibold tabular-nums',
                            SIZE_CLASSES[size],
                        )}
                    >
                        {pct}%
                    </span>
                </div>
            )}
            <div
                className={cn(
                    'w-full overflow-hidden rounded-full bg-muted',
                    SIZE_CLASSES[size],
                )}
            >
                <div
                    className={cn(
                        'h-full rounded-full transition-all duration-500',
                        scoreColor(clamped),
                    )}
                    style={{ width: `${pct}%` }}
                    role="meter"
                    aria-valuenow={clamped}
                    aria-valuemin={0}
                    aria-valuemax={1}
                    aria-label={`Confidence ${pct}%`}
                />
            </div>
        </div>
    );
}
