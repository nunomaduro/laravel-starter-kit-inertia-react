import {
    LightbulbIcon,
    MinusIcon,
    TrendingDownIcon,
    TrendingUpIcon,
} from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { ConfidenceScore } from './confidence-score';

export type InsightTrend = 'up' | 'down' | 'neutral';
export type InsightSeverity = 'info' | 'success' | 'warning' | 'error';

export interface AiInsightCardProps {
    title: string;
    description: string;
    trend?: InsightTrend;
    severity?: InsightSeverity;
    confidence?: number;
    tags?: string[];
    icon?: React.ReactNode;
    action?: React.ReactNode;
    className?: string;
}

const SEVERITY_CLASSES: Record<InsightSeverity, string> = {
    info: 'border-info/30 bg-info/5',
    success: 'border-success/30 bg-success/5',
    warning: 'border-warning/30 bg-warning/5',
    error: 'border-error/30 bg-error/5',
};

const TREND_ICONS: Record<InsightTrend, React.ReactNode> = {
    up: <TrendingUpIcon className="size-4 text-success" />,
    down: <TrendingDownIcon className="size-4 text-error" />,
    neutral: <MinusIcon className="size-4 text-muted-foreground" />,
};

/**
 * Card component for displaying an AI-generated insight with optional trend,
 * severity badge, confidence score, and tags.
 */
export function AiInsightCard({
    title,
    description,
    trend,
    severity = 'info',
    confidence,
    tags,
    icon,
    action,
    className,
}: AiInsightCardProps) {
    return (
        <Card
            className={cn(
                'overflow-hidden',
                SEVERITY_CLASSES[severity],
                className,
            )}
        >
            <CardHeader className="pb-2">
                <div className="flex items-start justify-between gap-2">
                    <div className="flex items-center gap-2">
                        {icon ?? (
                            <LightbulbIcon className="size-4 shrink-0 text-primary" />
                        )}
                        <CardTitle className="text-sm font-semibold">
                            {title}
                        </CardTitle>
                    </div>
                    <div className="flex items-center gap-1.5">
                        {trend && TREND_ICONS[trend]}
                        <Badge
                            variant="secondary"
                            className={cn(
                                'h-4 px-1.5 text-[10px]',
                                severity === 'success' &&
                                    'bg-success/20 text-success',
                                severity === 'warning' &&
                                    'bg-warning/20 text-warning',
                                severity === 'error' &&
                                    'bg-error/20 text-error',
                            )}
                        >
                            {severity}
                        </Badge>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="space-y-3">
                <p className="text-sm leading-relaxed text-muted-foreground">
                    {description}
                </p>

                {confidence !== undefined && (
                    <ConfidenceScore score={confidence} size="sm" />
                )}

                {tags && tags.length > 0 && (
                    <div className="flex flex-wrap gap-1">
                        {tags.map((tag) => (
                            <Badge
                                key={tag}
                                variant="outline"
                                className="h-4 px-1.5 text-[10px]"
                            >
                                {tag}
                            </Badge>
                        ))}
                    </div>
                )}

                {action && <div className="pt-1">{action}</div>}
            </CardContent>
        </Card>
    );
}
