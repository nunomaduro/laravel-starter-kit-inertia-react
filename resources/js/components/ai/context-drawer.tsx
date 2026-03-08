import { FileTextIcon, LinkIcon, PlusIcon, XIcon } from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';

export type ContextItemType = 'document' | 'url' | 'text' | 'image';

export interface ContextItem {
    id: string;
    type: ContextItemType;
    title: string;
    preview?: string;
    /** File size, word count, etc. */
    meta?: string;
}

export interface ContextDrawerProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    /** Current context items. */
    items?: ContextItem[];
    /** Called when an item is removed. */
    onRemove?: (id: string) => void;
    /** Called when user triggers add action. */
    onAdd?: () => void;
    /** Max total items allowed. */
    maxItems?: number;
    title?: string;
    className?: string;
}

const TYPE_ICONS: Record<ContextItemType, React.ReactNode> = {
    document: <FileTextIcon className="size-3.5 text-primary" />,
    url: <LinkIcon className="size-3.5 text-info" />,
    text: <FileTextIcon className="size-3.5 text-muted-foreground" />,
    image: <FileTextIcon className="size-3.5 text-warning" />,
};

const TYPE_LABELS: Record<ContextItemType, string> = {
    document: 'Document',
    url: 'URL',
    text: 'Text',
    image: 'Image',
};

/**
 * Slide-over drawer for managing AI context items (documents, URLs, pasted text).
 * Context items inform the AI about additional information for the session.
 */
export function ContextDrawer({
    open,
    onOpenChange,
    items = [],
    onRemove,
    onAdd,
    maxItems = 10,
    title = 'Context',
    className,
}: ContextDrawerProps) {
    const atLimit = items.length >= maxItems;

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="left"
                className={cn('flex w-[360px] flex-col gap-0 p-0', className)}
            >
                <SheetHeader className="shrink-0 border-b px-4 py-3">
                    <div className="flex items-center justify-between">
                        <SheetTitle className="flex items-center gap-2 text-base">
                            {title}
                            <Badge
                                variant="secondary"
                                className="h-4 px-1.5 text-[10px]"
                            >
                                {items.length}/{maxItems}
                            </Badge>
                        </SheetTitle>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            onClick={() => onOpenChange(false)}
                            aria-label="Close context drawer"
                        >
                            <XIcon className="size-4" />
                        </Button>
                    </div>
                    <p className="text-xs text-muted-foreground">
                        Add documents, links, or text to give the AI more
                        context for your conversation.
                    </p>
                </SheetHeader>

                <div className="flex-1 overflow-y-auto">
                    {items.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 px-4 py-12 text-center">
                            <FileTextIcon className="size-8 text-muted-foreground/50" />
                            <p className="text-sm text-muted-foreground">
                                No context items yet.
                            </p>
                            <p className="text-xs text-muted-foreground">
                                Add documents or links to help the AI.
                            </p>
                        </div>
                    ) : (
                        <ul className="space-y-2 p-3">
                            {items.map((item, i) => (
                                <React.Fragment key={item.id}>
                                    {i > 0 && <Separator />}
                                    <li className="flex items-start gap-3 py-1">
                                        <span className="mt-0.5 shrink-0">
                                            {TYPE_ICONS[item.type]}
                                        </span>
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-1.5">
                                                <p className="truncate text-sm font-medium">
                                                    {item.title}
                                                </p>
                                                <Badge
                                                    variant="outline"
                                                    className="h-3.5 shrink-0 px-1 text-[9px]"
                                                >
                                                    {TYPE_LABELS[item.type]}
                                                </Badge>
                                            </div>
                                            {item.preview && (
                                                <p className="mt-0.5 line-clamp-2 text-xs text-muted-foreground">
                                                    {item.preview}
                                                </p>
                                            )}
                                            {item.meta && (
                                                <p className="mt-0.5 text-[10px] text-muted-foreground">
                                                    {item.meta}
                                                </p>
                                            )}
                                        </div>
                                        {onRemove && (
                                            <Button
                                                variant="ghost"
                                                size="icon-xs"
                                                className="shrink-0 text-muted-foreground hover:text-destructive"
                                                onClick={() =>
                                                    onRemove(item.id)
                                                }
                                                aria-label={`Remove ${item.title}`}
                                            >
                                                <XIcon className="size-3" />
                                            </Button>
                                        )}
                                    </li>
                                </React.Fragment>
                            ))}
                        </ul>
                    )}
                </div>

                {onAdd && (
                    <div className="shrink-0 border-t p-3">
                        <Button
                            variant="outline"
                            size="sm"
                            className="w-full gap-1.5"
                            onClick={onAdd}
                            disabled={atLimit}
                        >
                            <PlusIcon className="size-3.5" />
                            {atLimit
                                ? `Limit reached (${maxItems})`
                                : 'Add context'}
                        </Button>
                    </div>
                )}
            </SheetContent>
        </Sheet>
    );
}
