import * as React from 'react';
import { BotIcon, XIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { AssistantRuntimeProvider, type AssistantRuntimeProviderProps } from './assistant-runtime-provider';
import { AssistantThread, type AssistantThreadProps } from './assistant-thread';

export interface AssistantSidebarProps
    extends Pick<AssistantRuntimeProviderProps, 'endpoint' | 'model' | 'headers' | 'systemPrompt'>,
        Omit<AssistantThreadProps, 'className'> {
    /** Controls the open state of the sidebar. */
    open: boolean;
    /** Called when the sidebar should close. */
    onOpenChange: (open: boolean) => void;
    /** Sidebar title. */
    title?: string;
    /** Which side to dock to. */
    side?: 'left' | 'right';
    /** Custom class for the sheet content. */
    contentClassName?: string;
}

/**
 * A slide-over sidebar wrapping `AssistantThread` and `AssistantRuntimeProvider`.
 * Use this for persistent assistant panels docked to the page edge.
 */
export function AssistantSidebar({
    open,
    onOpenChange,
    title = 'AI Assistant',
    side = 'right',
    endpoint,
    model,
    headers,
    systemPrompt,
    placeholder,
    assistantName,
    welcomeMessage,
    contentClassName,
}: AssistantSidebarProps) {
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side={side}
                className={cn(
                    'flex flex-col gap-0 p-0 w-[400px] sm:w-[480px] sm:max-w-none',
                    contentClassName,
                )}
            >
                <SheetHeader className="border-b px-4 py-3 shrink-0">
                    <div className="flex items-center justify-between">
                        <SheetTitle className="flex items-center gap-2 text-base">
                            <BotIcon className="size-4 text-primary" />
                            {title}
                        </SheetTitle>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            onClick={() => onOpenChange(false)}
                            aria-label="Close assistant"
                        >
                            <XIcon className="size-4" />
                        </Button>
                    </div>
                </SheetHeader>

                <AssistantRuntimeProvider
                    endpoint={endpoint}
                    model={model}
                    headers={headers}
                    systemPrompt={systemPrompt}
                >
                    <AssistantThread
                        placeholder={placeholder}
                        assistantName={assistantName}
                        welcomeMessage={welcomeMessage}
                        className="flex-1 min-h-0"
                    />
                </AssistantRuntimeProvider>
            </SheetContent>
        </Sheet>
    );
}
