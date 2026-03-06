import * as React from 'react';

import { cn } from '@/lib/utils';
import { useReducedMotion } from '@/hooks/use-reduced-motion';

export interface StreamingTextProps {
    /** The full target text to display. */
    text: string;
    /** Whether the text is still being streamed. Shows a cursor when true. */
    isStreaming?: boolean;
    /** Character reveal speed in ms per character (only used without reducedMotion). */
    speed?: number;
    className?: string;
}

/**
 * Renders text that appears to stream in character-by-character.
 * When `isStreaming` is true a blinking cursor is shown at the end.
 * Respects `prefers-reduced-motion` — when reduced motion is requested
 * the full text is shown immediately without animation.
 */
export function StreamingText({
    text,
    isStreaming = false,
    speed = 18,
    className,
}: StreamingTextProps) {
    const reducedMotion = useReducedMotion();
    const [displayed, setDisplayed] = React.useState('');
    const indexRef = React.useRef(0);
    const prevTextRef = React.useRef('');

    React.useEffect(() => {
        if (reducedMotion) {
            setDisplayed(text);
            indexRef.current = text.length;
            prevTextRef.current = text;
            return;
        }

        // If text grew (streaming append), continue from where we left off
        if (text.startsWith(prevTextRef.current)) {
            prevTextRef.current = text;
        } else {
            // Text was replaced entirely — restart
            setDisplayed('');
            indexRef.current = 0;
            prevTextRef.current = text;
        }

        const tick = () => {
            if (indexRef.current >= text.length) return;
            indexRef.current++;
            setDisplayed(text.slice(0, indexRef.current));
        };

        const interval = setInterval(tick, speed);
        return () => clearInterval(interval);
    }, [text, speed, reducedMotion]);

    return (
        <span className={cn('whitespace-pre-wrap', className)}>
            {displayed}
            {isStreaming && (
                <span
                    aria-hidden
                    className={cn(
                        'ml-px inline-block h-[1em] w-[2px] translate-y-[2px] rounded-full bg-current',
                        !reducedMotion && 'animate-pulse',
                    )}
                />
            )}
        </span>
    );
}
