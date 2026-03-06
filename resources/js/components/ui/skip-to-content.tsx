import * as React from 'react';

import { cn } from '@/lib/utils';

interface SkipToContentProps {
    /** The id of the main content element to skip to (default: "main-content") */
    contentId?: string;
    className?: string;
}

/**
 * Visually-hidden link that becomes visible on keyboard focus and jumps
 * to the main content area — the first item in every app shell layout.
 *
 * The target element must have `id="main-content"` (or the provided contentId).
 */
function SkipToContent({ contentId = 'main-content', className }: SkipToContentProps) {
    return (
        <a
            href={`#${contentId}`}
            className={cn(
                // Hidden until focused
                'sr-only focus:not-sr-only',
                // Visible state
                'focus:fixed focus:top-4 focus:left-4 focus:z-[9999]',
                'focus:rounded-md focus:bg-background focus:px-4 focus:py-2',
                'focus:text-sm focus:font-medium focus:text-foreground',
                'focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none',
                'focus:shadow-md',
                className,
            )}
        >
            Skip to main content
        </a>
    );
}

export { SkipToContent };
export type { SkipToContentProps };
