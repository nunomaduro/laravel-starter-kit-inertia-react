import * as React from 'react';

import { Group as PanelGroup, Panel, Separator as PanelResizeHandle } from 'react-resizable-panels';

import { SkipToContent } from '@/components/ui/skip-to-content';
import { cn } from '@/lib/utils';

interface MasterDetailProps {
    /** Left list/master panel content */
    master: React.ReactNode;
    /** Right detail panel content */
    detail: React.ReactNode;
    className?: string;
    /** Default size of the master panel as a percentage (default: 33) */
    masterDefaultSize?: number;
    /** Minimum size of the master panel as a percentage (default: 20) */
    masterMinSize?: number;
}

/**
 * Master-detail layout: left list panel + right detail panel.
 * On mobile the panels stack vertically; on desktop they split side-by-side
 * with a draggable resizer powered by react-resizable-panels.
 */
function MasterDetail({ master, detail, className, masterDefaultSize = 33, masterMinSize = 20 }: MasterDetailProps) {
    return (
        <div className={cn('flex h-full w-full flex-col overflow-hidden', className)}>
            <SkipToContent />

            {/* Mobile: stacked layout */}
            <div className="flex h-full flex-col md:hidden">
                <div className="flex-1 overflow-auto border-b">{master}</div>
                <main id="main-content" className="flex-1 overflow-auto">
                    {detail}
                </main>
            </div>

            {/* Desktop: side-by-side with resizable panels */}
            <PanelGroup orientation="horizontal" className="hidden h-full md:flex">
                <Panel defaultSize={masterDefaultSize} minSize={masterMinSize}>
                    <div className="h-full overflow-auto border-r">{master}</div>
                </Panel>

                <PanelResizeHandle className="w-1 bg-border transition-colors hover:bg-primary/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />

                <Panel minSize={30}>
                    <main id="main-content" className="h-full overflow-auto">
                        {detail}
                    </main>
                </Panel>
            </PanelGroup>
        </div>
    );
}

export { MasterDetail };
export type { MasterDetailProps };
