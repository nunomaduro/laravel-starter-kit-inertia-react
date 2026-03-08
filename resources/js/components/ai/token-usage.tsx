import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { TokenUsage } from './assistant-runtime-provider';

export interface TokenUsageProps {
    usage: TokenUsage;
    /** Maximum token budget to calculate percentage fill (optional). */
    maxTokens?: number;
    className?: string;
}

/**
 * Displays prompt / completion / total token counts.
 * When `maxTokens` is provided, shows a usage progress bar.
 */
export function TokenUsageDisplay({
    usage,
    maxTokens,
    className,
}: TokenUsageProps) {
    const usedPct = maxTokens
        ? Math.min((usage.total / maxTokens) * 100, 100)
        : null;

    return (
        <div className={cn('space-y-1.5', className)}>
            <div className="flex flex-wrap items-center gap-2">
                <span className="text-xs text-muted-foreground">Tokens:</span>
                <Badge
                    variant="secondary"
                    className="h-4 gap-1 px-1.5 text-[10px]"
                >
                    <span className="text-muted-foreground">prompt</span>
                    {usage.prompt.toLocaleString()}
                </Badge>
                <Badge
                    variant="secondary"
                    className="h-4 gap-1 px-1.5 text-[10px]"
                >
                    <span className="text-muted-foreground">completion</span>
                    {usage.completion.toLocaleString()}
                </Badge>
                <Badge
                    variant="outline"
                    className="h-4 px-1.5 text-[10px] font-semibold"
                >
                    total {usage.total.toLocaleString()}
                </Badge>
            </div>

            {usedPct !== null && (
                <div className="space-y-0.5">
                    <div className="flex items-center justify-between text-[10px] text-muted-foreground">
                        <span>Context window</span>
                        <span>{usedPct.toFixed(1)}%</span>
                    </div>
                    <div className="h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div
                            className={cn(
                                'h-full rounded-full transition-all',
                                usedPct < 60 && 'bg-success',
                                usedPct >= 60 && usedPct < 85 && 'bg-warning',
                                usedPct >= 85 && 'bg-error',
                            )}
                            style={{ width: `${usedPct}%` }}
                        />
                    </div>
                </div>
            )}
        </div>
    );
}
