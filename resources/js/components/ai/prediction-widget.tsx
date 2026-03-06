import * as React from 'react';
import { TrendingUpIcon, TrendingDownIcon, MinusIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ConfidenceScore } from './confidence-score';

export type PredictionDirection = 'up' | 'down' | 'flat';

export interface PredictionWidgetProps {
    title: string;
    /** The predicted value (string for flexibility, e.g. "$1.2M", "87%"). */
    predicted: string;
    /** Current / baseline value shown for comparison. */
    current?: string;
    /** Direction of the prediction. */
    direction?: PredictionDirection;
    /** Confidence score (0–1). */
    confidence?: number;
    /** Time horizon for the prediction (e.g. "Next 30 days"). */
    horizon?: string;
    /** Supporting explanation. */
    rationale?: string;
    className?: string;
}

const DIRECTION_ICONS: Record<PredictionDirection, React.ReactNode> = {
    up: <TrendingUpIcon className="size-4 text-success" />,
    down: <TrendingDownIcon className="size-4 text-error" />,
    flat: <MinusIcon className="size-4 text-muted-foreground" />,
};

const DIRECTION_BADGE: Record<PredictionDirection, string> = {
    up: 'bg-success/10 text-success border-success/20',
    down: 'bg-error/10 text-error border-error/20',
    flat: 'bg-muted text-muted-foreground',
};

/**
 * Displays an AI prediction with direction, confidence, and optional rationale.
 */
export function PredictionWidget({
    title,
    predicted,
    current,
    direction = 'flat',
    confidence,
    horizon,
    rationale,
    className,
}: PredictionWidgetProps) {
    return (
        <Card className={cn('overflow-hidden', className)}>
            <CardHeader className="pb-2">
                <div className="flex items-center justify-between">
                    <CardTitle className="text-sm font-semibold">{title}</CardTitle>
                    {horizon && (
                        <Badge variant="outline" className="h-5 px-2 text-[10px]">
                            {horizon}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-3">
                <div className="flex items-end gap-3">
                    <div>
                        <p className="text-[10px] text-muted-foreground mb-0.5">Predicted</p>
                        <p className="text-2xl font-bold tabular-nums">{predicted}</p>
                    </div>
                    {current && (
                        <div>
                            <p className="text-[10px] text-muted-foreground mb-0.5">Current</p>
                            <p className="text-lg font-medium text-muted-foreground tabular-nums">
                                {current}
                            </p>
                        </div>
                    )}
                    <div className="ml-auto">
                        <span
                            className={cn(
                                'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium',
                                DIRECTION_BADGE[direction],
                            )}
                        >
                            {DIRECTION_ICONS[direction]}
                            {direction}
                        </span>
                    </div>
                </div>

                {confidence !== undefined && (
                    <ConfidenceScore score={confidence} size="sm" />
                )}

                {rationale && (
                    <p className="text-xs text-muted-foreground leading-relaxed border-t pt-2">
                        {rationale}
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
