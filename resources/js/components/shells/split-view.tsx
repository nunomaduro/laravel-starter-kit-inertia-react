import * as React from 'react';

import {
    Panel,
    Group as PanelGroup,
    Separator as PanelResizeHandle,
} from 'react-resizable-panels';

import { SkipToContent } from '@/components/ui/skip-to-content';
import { cn } from '@/lib/utils';

interface SplitViewProps {
    /** First pane content */
    first: React.ReactNode;
    /** Second pane content */
    second: React.ReactNode;
    /** Split direction */
    direction?: 'horizontal' | 'vertical';
    /** Default size of the first pane as a percentage (default: 50) */
    firstDefaultSize?: number;
    /** Minimum size of each pane as a percentage (default: 15) */
    minSize?: number;
    className?: string;
}

/**
 * Two-pane split view with a draggable resizer.
 * Supports horizontal (side-by-side) and vertical (top/bottom) splits.
 */
function SplitView({
    first,
    second,
    direction = 'horizontal',
    firstDefaultSize = 50,
    minSize = 15,
    className,
}: SplitViewProps) {
    return (
        <div className={cn('flex h-full w-full overflow-hidden', className)}>
            <SkipToContent />

            <PanelGroup orientation={direction} className="h-full w-full">
                <Panel defaultSize={firstDefaultSize} minSize={minSize}>
                    <div className="h-full overflow-auto">{first}</div>
                </Panel>

                <PanelResizeHandle
                    className={cn(
                        'bg-border transition-colors hover:bg-primary/30 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                        direction === 'horizontal' ? 'w-1' : 'h-1',
                    )}
                />

                <Panel minSize={minSize}>
                    <main id="main-content" className="h-full overflow-auto">
                        {second}
                    </main>
                </Panel>
            </PanelGroup>
        </div>
    );
}

export { SplitView };
export type { SplitViewProps };
