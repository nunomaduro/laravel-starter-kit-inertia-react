import {
    CheckIcon,
    ChevronDownIcon,
    LoaderIcon,
    WrenchIcon,
    XIcon,
} from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { ToolCall } from './assistant-runtime-provider';

export interface ToolCallCardProps {
    toolCall: ToolCall;
    defaultOpen?: boolean;
    className?: string;
}

const STATUS_ICONS = {
    pending: <LoaderIcon className="size-3 text-muted-foreground" />,
    running: <LoaderIcon className="size-3 animate-spin text-primary" />,
    done: <CheckIcon className="size-3 text-success" />,
    error: <XIcon className="size-3 text-error" />,
} satisfies Record<ToolCall['status'], React.ReactNode>;

const STATUS_LABELS = {
    pending: 'Pending',
    running: 'Running',
    done: 'Done',
    error: 'Error',
} satisfies Record<ToolCall['status'], string>;

/**
 * Displays an AI tool call with its name, input arguments, and optional output.
 * Collapsible — shows arguments when expanded.
 */
export function ToolCallCard({
    toolCall,
    defaultOpen = false,
    className,
}: ToolCallCardProps) {
    const [open, setOpen] = React.useState(defaultOpen);

    const inputJson = React.useMemo(
        () => JSON.stringify(toolCall.input, null, 2),
        [toolCall.input],
    );

    return (
        <div
            className={cn(
                'overflow-hidden rounded-lg border bg-muted/30 text-sm',
                className,
            )}
        >
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                className="flex w-full items-center gap-2 px-3 py-2 text-left transition-colors hover:bg-muted/50"
                aria-expanded={open}
            >
                <WrenchIcon className="size-3.5 shrink-0 text-muted-foreground" />
                <span className="flex-1 font-mono text-xs font-medium">
                    {toolCall.name}
                </span>
                <span className="flex items-center gap-1.5">
                    {STATUS_ICONS[toolCall.status]}
                    <Badge
                        variant="secondary"
                        className="h-4 px-1.5 text-[10px]"
                    >
                        {STATUS_LABELS[toolCall.status]}
                    </Badge>
                </span>
                <ChevronDownIcon
                    className={cn(
                        'size-3.5 shrink-0 text-muted-foreground transition-transform',
                        open && 'rotate-180',
                    )}
                />
            </button>

            {open && (
                <div className="space-y-2 border-t px-3 py-2">
                    <div>
                        <p className="mb-1 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">
                            Input
                        </p>
                        <pre className="overflow-x-auto rounded bg-background p-2 font-mono text-xs leading-relaxed">
                            {inputJson}
                        </pre>
                    </div>

                    {toolCall.output !== undefined && (
                        <div>
                            <p className="mb-1 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">
                                Output
                            </p>
                            <pre className="overflow-x-auto rounded bg-background p-2 font-mono text-xs leading-relaxed">
                                {toolCall.output}
                            </pre>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
