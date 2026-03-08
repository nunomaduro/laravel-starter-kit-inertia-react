import { BotIcon, XIcon } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import {
    AssistantRuntimeProvider,
    type AssistantRuntimeProviderProps,
} from './assistant-runtime-provider';
import { AssistantThread, type AssistantThreadProps } from './assistant-thread';

export interface AssistantSidebarProps
    extends
        Pick<
            AssistantRuntimeProviderProps,
            'endpoint' | 'model' | 'headers' | 'systemPrompt'
        >,
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
                    'flex w-[400px] flex-col gap-0 p-0 sm:w-[480px] sm:max-w-none',
                    contentClassName,
                )}
            >
                <SheetHeader className="shrink-0 border-b px-4 py-3">
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
                        className="min-h-0 flex-1"
                    />
                </AssistantRuntimeProvider>
            </SheetContent>
        </Sheet>
    );
}
