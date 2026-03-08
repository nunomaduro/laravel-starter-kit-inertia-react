import * as React from 'react';

import { SkipToContent } from '@/components/ui/skip-to-content';
import { cn } from '@/lib/utils';

interface AppShellProps {
    /** Sidebar content */
    sidebar?: React.ReactNode;
    /** Top header content */
    header?: React.ReactNode;
    /** Optional right panel content */
    rightPanel?: React.ReactNode;
    /** Main page content */
    children?: React.ReactNode;
    className?: string;
    /** Whether the sidebar is collapsed (narrow icon-only mode) */
    sidebarCollapsed?: boolean;
}

/**
 * Primary app shell: collapsible sidebar + top header + main content + optional right panel.
 * Includes SkipToContent for accessibility and id="main-content" on <main>.
 */
function AppShell({
    sidebar,
    header,
    rightPanel,
    children,
    className,
    sidebarCollapsed = false,
}: AppShellProps) {
    return (
        <div
            className={cn(
                'flex h-svh w-full overflow-hidden bg-background',
                className,
            )}
        >
            <SkipToContent />

            {/* Sidebar */}
            {sidebar && (
                <aside
                    className={cn(
                        'flex shrink-0 flex-col border-r bg-sidebar transition-[width] duration-200',
                        sidebarCollapsed ? 'w-16' : 'w-64',
                    )}
                >
                    {sidebar}
                </aside>
            )}

            {/* Main column: header + content + right panel */}
            <div className="flex min-w-0 flex-1 flex-col">
                {/* Top header */}
                {header && (
                    <header className="flex h-14 shrink-0 items-center border-b bg-background px-4">
                        {header}
                    </header>
                )}

                {/* Content row */}
                <div className="flex min-h-0 flex-1">
                    {/* Main content */}
                    <main
                        id="main-content"
                        className="min-w-0 flex-1 overflow-auto p-4"
                    >
                        {children}
                    </main>

                    {/* Optional right panel */}
                    {rightPanel && (
                        <aside className="w-80 shrink-0 overflow-auto border-l bg-muted/30 p-4">
                            {rightPanel}
                        </aside>
                    )}
                </div>
            </div>
        </div>
    );
}

export { AppShell };
export type { AppShellProps };
