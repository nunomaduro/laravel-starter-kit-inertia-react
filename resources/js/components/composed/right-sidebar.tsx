import { XIcon } from 'lucide-react';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';

export interface RightSidebarSection {
    id: string;
    title?: string;
    content: React.ReactNode;
    collapsible?: boolean;
}

export interface RightSidebarProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title?: string;
    description?: string;
    sections?: RightSidebarSection[];
    children?: React.ReactNode;
    width?: number;
    headerActions?: React.ReactNode;
    footer?: React.ReactNode;
    className?: string;
}

function RightSidebar({
    open,
    onOpenChange,
    title,
    description,
    sections = [],
    children,
    width = 360,
    headerActions,
    footer,
    className,
}: RightSidebarProps) {
    const [collapsedSections, setCollapsedSections] = React.useState<
        Set<string>
    >(() => new Set());

    const toggleSection = (id: string) => {
        setCollapsedSections((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="right"
                data-slot="right-sidebar"
                className={cn('flex flex-col gap-0 p-0', className)}
                style={{ width: `${width}px`, maxWidth: '100vw' }}
            >
                {(title || headerActions) && (
                    <>
                        <SheetHeader className="flex flex-row items-center justify-between px-4 py-3">
                            <div>
                                {title && (
                                    <SheetTitle className="text-sm">
                                        {title}
                                    </SheetTitle>
                                )}
                                {description && (
                                    <SheetDescription className="text-xs">
                                        {description}
                                    </SheetDescription>
                                )}
                            </div>
                            <div className="flex items-center gap-1">
                                {headerActions}
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="size-7 shrink-0"
                                    onClick={() => onOpenChange(false)}
                                    aria-label="Close"
                                >
                                    <XIcon className="size-4" />
                                </Button>
                            </div>
                        </SheetHeader>
                        <Separator />
                    </>
                )}

                <div className="flex-1 overflow-y-auto">
                    {children}
                    {sections.map((section, i) => (
                        <React.Fragment key={section.id}>
                            {i > 0 && <Separator />}
                            <div data-slot="right-sidebar-section">
                                {section.title && (
                                    <div className="flex items-center justify-between px-4 py-2.5">
                                        {section.collapsible ? (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    toggleSection(section.id)
                                                }
                                                className="flex w-full items-center justify-between text-left"
                                            >
                                                <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                    {section.title}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {collapsedSections.has(
                                                        section.id,
                                                    )
                                                        ? '+'
                                                        : '−'}
                                                </span>
                                            </button>
                                        ) : (
                                            <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                {section.title}
                                            </span>
                                        )}
                                    </div>
                                )}
                                {(!section.collapsible ||
                                    !collapsedSections.has(section.id)) && (
                                    <div className="px-4 pb-4">
                                        {section.content}
                                    </div>
                                )}
                            </div>
                        </React.Fragment>
                    ))}
                </div>

                {footer && (
                    <>
                        <Separator />
                        <div className="px-4 py-3">{footer}</div>
                    </>
                )}
            </SheetContent>
        </Sheet>
    );
}

export { RightSidebar };
