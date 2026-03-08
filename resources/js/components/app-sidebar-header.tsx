import { Breadcrumbs } from '@/components/breadcrumbs';
import { ConnectedNotificationCenter } from '@/components/composed/notification-center';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { Search } from 'lucide-react';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex flex-1 items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <button
                className="hidden items-center gap-1.5 rounded-md border border-border/60 bg-muted/50 px-2.5 py-1 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground md:flex"
                onClick={() => {
                    window.dispatchEvent(new Event('open-command-palette'));
                }}
                aria-label="Open command palette"
                type="button"
            >
                <Search className="size-3" />
                <span>Search</span>
                <kbd className="pointer-events-none ml-1 flex h-4 items-center rounded border border-border/60 bg-background px-1 font-mono text-[10px] font-medium select-none">
                    ⌘K
                </kbd>
            </button>
            <ConnectedNotificationCenter />
        </header>
    );
}
