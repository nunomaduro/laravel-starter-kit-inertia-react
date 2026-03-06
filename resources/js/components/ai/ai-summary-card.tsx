import * as React from 'react';
import { SparklesIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';

export interface AiSummaryCardProps {
    /** Summary text (may contain markdown, rendered as plain text here). */
    summary: string;
    /** Source label (e.g. document name, page title). */
    source?: string;
    /** Word count or reading time string. */
    meta?: string;
    /** Key points as a list. */
    keyPoints?: string[];
    /** Show skeleton loading state. */
    skeleton?: boolean;
    /** Optional header action. */
    action?: React.ReactNode;
    className?: string;
}

/**
 * Card that displays an AI-generated summary with optional key points,
 * source attribution, and skeleton loading state.
 */
export function AiSummaryCard({
    summary,
    source,
    meta,
    keyPoints,
    skeleton = false,
    action,
    className,
}: AiSummaryCardProps) {
    if (skeleton) {
        return (
            <Card className={cn('overflow-hidden', className)}>
                <CardHeader className="pb-2">
                    <Skeleton className="h-4 w-32" />
                </CardHeader>
                <CardContent className="space-y-2">
                    <Skeleton className="h-3 w-full" />
                    <Skeleton className="h-3 w-5/6" />
                    <Skeleton className="h-3 w-4/6" />
                </CardContent>
            </Card>
        );
    }

    return (
        <Card className={cn('overflow-hidden', className)}>
            <CardHeader className="pb-2">
                <div className="flex items-center justify-between gap-2">
                    <CardTitle className="flex items-center gap-2 text-sm font-semibold">
                        <SparklesIcon className="size-4 text-primary" />
                        AI Summary
                    </CardTitle>
                    <div className="flex items-center gap-1.5">
                        {meta && (
                            <span className="text-[10px] text-muted-foreground">{meta}</span>
                        )}
                        {action}
                    </div>
                </div>
                {source && (
                    <Badge variant="outline" className="mt-1 h-5 w-fit px-2 text-[10px]">
                        {source}
                    </Badge>
                )}
            </CardHeader>

            <CardContent className="space-y-3">
                <p className="text-sm text-muted-foreground leading-relaxed">{summary}</p>

                {keyPoints && keyPoints.length > 0 && (
                    <div className="space-y-1">
                        <p className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                            Key Points
                        </p>
                        <ul className="space-y-1">
                            {keyPoints.map((point, i) => (
                                <li key={i} className="flex items-start gap-2 text-sm">
                                    <span className="mt-1.5 size-1.5 shrink-0 rounded-full bg-primary" />
                                    {point}
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
