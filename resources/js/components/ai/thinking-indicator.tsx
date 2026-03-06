import * as React from 'react';

import { cn } from '@/lib/utils';
import { useReducedMotion } from '@/hooks/use-reduced-motion';

export type ThinkingVariant = 'dots' | 'pulse' | 'bars';

export interface ThinkingIndicatorProps {
    /** Visual style of the indicator. */
    variant?: ThinkingVariant;
    /** Optional label rendered next to the indicator. */
    label?: string;
    className?: string;
}

/**
 * Three-variant thinking / loading indicator for AI responses.
 * Respects `prefers-reduced-motion` — animations are disabled when requested.
 *
 * Variants:
 *  - `dots`  — three bouncing dots
 *  - `pulse` — single pulsing circle
 *  - `bars`  — three animated vertical bars
 */
export function ThinkingIndicator({
    variant = 'dots',
    label,
    className,
}: ThinkingIndicatorProps) {
    const reducedMotion = useReducedMotion();

    return (
        <div
            role="status"
            aria-label={label ?? 'Thinking…'}
            className={cn('inline-flex items-center gap-2', className)}
        >
            {variant === 'dots' && (
                <span className="flex items-center gap-1">
                    {[0, 1, 2].map((i) => (
                        <span
                            key={i}
                            className={cn(
                                'size-2 rounded-full bg-current opacity-70',
                                !reducedMotion && 'animate-bounce',
                            )}
                            style={
                                !reducedMotion
                                    ? { animationDelay: `${i * 150}ms`, animationDuration: '0.9s' }
                                    : undefined
                            }
                        />
                    ))}
                </span>
            )}

            {variant === 'pulse' && (
                <span
                    className={cn(
                        'size-3 rounded-full bg-current opacity-70',
                        !reducedMotion && 'animate-pulse',
                    )}
                />
            )}

            {variant === 'bars' && (
                <span className="flex items-end gap-0.5">
                    {[0, 1, 2].map((i) => (
                        <span
                            key={i}
                            className={cn(
                                'w-1 rounded-full bg-current opacity-70',
                                !reducedMotion ? 'animate-[thinkBar_0.8s_ease-in-out_infinite_alternate]' : 'h-3',
                            )}
                            style={
                                !reducedMotion
                                    ? {
                                          animationDelay: `${i * 120}ms`,
                                          height: '12px',
                                      }
                                    : undefined
                            }
                        />
                    ))}
                </span>
            )}

            {label && (
                <span className="text-xs text-muted-foreground">{label}</span>
            )}
        </div>
    );
}
