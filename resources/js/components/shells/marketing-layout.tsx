import * as React from 'react';

import { SkipToContent } from '@/components/ui/skip-to-content';
import { cn } from '@/lib/utils';

interface MarketingLayoutProps {
    /** Top navigation slot */
    nav?: React.ReactNode;
    /** Footer slot */
    footer?: React.ReactNode;
    /** Main page content */
    children?: React.ReactNode;
    className?: string;
    /** Max-width constraint class for the content container (default: 'max-w-5xl') */
    maxWidth?: string;
}

/**
 * Marketing / auth layout: centered max-width content with optional nav and footer slots.
 * Suitable for landing pages, auth screens, and marketing sections.
 */
function MarketingLayout({ nav, footer, children, className, maxWidth = 'max-w-5xl' }: MarketingLayoutProps) {
    return (
        <div className={cn('flex min-h-svh flex-col bg-background', className)}>
            <SkipToContent />

            {/* Optional top navigation */}
            {nav && (
                <header className="sticky top-0 z-40 border-b bg-background/80 backdrop-blur-sm">
                    <div className={cn('mx-auto flex h-16 items-center px-4 sm:px-6', maxWidth)}>{nav}</div>
                </header>
            )}

            {/* Main content */}
            <main id="main-content" className="flex flex-1 flex-col">
                <div className={cn('mx-auto w-full flex-1 px-4 py-8 sm:px-6', maxWidth)}>{children}</div>
            </main>

            {/* Optional footer */}
            {footer && (
                <footer className="border-t bg-muted/30">
                    <div className={cn('mx-auto px-4 py-8 sm:px-6', maxWidth)}>{footer}</div>
                </footer>
            )}
        </div>
    );
}

export { MarketingLayout };
export type { MarketingLayoutProps };
