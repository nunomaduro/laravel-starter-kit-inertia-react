import * as React from 'react';
import { AlertTriangleIcon, XIcon, CheckIcon, ZapIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

export type AnomalySeverity = 'low' | 'medium' | 'high' | 'critical';

export interface AnomalyAlertProps {
    title: string;
    description: string;
    severity?: AnomalySeverity;
    /** Metric value where the anomaly was detected. */
    value?: string;
    /** Expected / baseline value. */
    expected?: string;
    /** Timestamp string. */
    detectedAt?: string;
    /** Whether the anomaly has been acknowledged. */
    acknowledged?: boolean;
    /** Called when user clicks Acknowledge. */
    onAcknowledge?: () => void;
    /** Called when user clicks Dismiss. */
    onDismiss?: () => void;
    className?: string;
}

const SEVERITY_STYLES: Record<AnomalySeverity, { container: string; badge: string; icon: string }> = {
    low: {
        container: 'border-info/30 bg-info/5',
        badge: 'bg-info/10 text-info',
        icon: 'text-info',
    },
    medium: {
        container: 'border-warning/30 bg-warning/5',
        badge: 'bg-warning/10 text-warning',
        icon: 'text-warning',
    },
    high: {
        container: 'border-error/30 bg-error/5',
        badge: 'bg-error/10 text-error',
        icon: 'text-error',
    },
    critical: {
        container: 'border-error bg-error/10',
        badge: 'bg-error text-white',
        icon: 'text-error',
    },
};

/**
 * Alert component for AI-detected anomalies with severity levels,
 * optional metric values, and acknowledge/dismiss actions.
 */
export function AnomalyAlert({
    title,
    description,
    severity = 'medium',
    value,
    expected,
    detectedAt,
    acknowledged = false,
    onAcknowledge,
    onDismiss,
    className,
}: AnomalyAlertProps) {
    const styles = SEVERITY_STYLES[severity];

    return (
        <div
            role="alert"
            aria-live="polite"
            className={cn(
                'rounded-lg border p-4 space-y-3',
                styles.container,
                acknowledged && 'opacity-60',
                className,
            )}
        >
            {/* Header */}
            <div className="flex items-start gap-3">
                <AlertTriangleIcon
                    className={cn('size-4 shrink-0 mt-0.5', styles.icon)}
                />
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 flex-wrap">
                        <span className="text-sm font-semibold">{title}</span>
                        <Badge className={cn('h-4 px-1.5 text-[10px]', styles.badge)}>
                            {severity}
                        </Badge>
                        {acknowledged && (
                            <Badge variant="outline" className="h-4 px-1.5 text-[10px]">
                                Acknowledged
                            </Badge>
                        )}
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground leading-relaxed">
                        {description}
                    </p>
                </div>
                {onDismiss && (
                    <Button
                        variant="ghost"
                        size="icon-xs"
                        className="shrink-0 text-muted-foreground"
                        onClick={onDismiss}
                        aria-label="Dismiss anomaly"
                    >
                        <XIcon className="size-3" />
                    </Button>
                )}
            </div>

            {/* Metric comparison */}
            {(value ?? expected) && (
                <div className="flex items-center gap-4 text-xs">
                    {value && (
                        <div>
                            <span className="text-muted-foreground">Detected: </span>
                            <span className="font-semibold text-error">{value}</span>
                        </div>
                    )}
                    {expected && (
                        <div>
                            <span className="text-muted-foreground">Expected: </span>
                            <span className="font-semibold">{expected}</span>
                        </div>
                    )}
                    {detectedAt && (
                        <div className="ml-auto text-muted-foreground">
                            <ZapIcon className="inline size-2.5 mr-0.5" />
                            {detectedAt}
                        </div>
                    )}
                </div>
            )}

            {/* Actions */}
            {!acknowledged && onAcknowledge && (
                <Button
                    variant="outline"
                    size="xs"
                    className="h-6 px-2 text-xs gap-1"
                    onClick={onAcknowledge}
                >
                    <CheckIcon className="size-3" />
                    Acknowledge
                </Button>
            )}
        </div>
    );
}
