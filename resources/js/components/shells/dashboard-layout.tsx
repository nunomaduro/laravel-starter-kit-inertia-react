import * as React from 'react';

import { SkipToContent } from '@/components/ui/skip-to-content';
import { cn } from '@/lib/utils';

interface DashboardLayoutProps {
    /** Row of stat cards displayed at the top */
    statsRow?: React.ReactNode;
    /** Main chart / content area */
    children?: React.ReactNode;
    /** Sidebar widget area */
    widgets?: React.ReactNode;
    className?: string;
}

/**
 * Dashboard layout: header stat cards row + main chart area + optional sidebar widget area.
 * Responsive: widgets appear below main content on mobile, in a side column on desktop.
 */
function DashboardLayout({
    statsRow,
    children,
    widgets,
    className,
}: DashboardLayoutProps) {
    return (
        <div
            className={cn(
                'flex h-full w-full flex-col overflow-auto bg-background',
                className,
            )}
        >
            <SkipToContent />

            {/* Header stats row */}
            {statsRow && (
                <div className="grid grid-cols-2 gap-4 p-4 sm:grid-cols-4">
                    {statsRow}
                </div>
            )}

            {/* Main content + widgets */}
            <div className="flex min-h-0 flex-1 flex-col gap-4 p-4 pt-0 lg:flex-row">
                {/* Main chart / content area */}
                <main id="main-content" className="min-w-0 flex-1">
                    {children}
                </main>

                {/* Sidebar widget area */}
                {widgets && (
                    <aside className="w-full shrink-0 space-y-4 lg:w-80">
                        {widgets}
                    </aside>
                )}
            </div>
        </div>
    );
}

export { DashboardLayout };
export type { DashboardLayoutProps };
