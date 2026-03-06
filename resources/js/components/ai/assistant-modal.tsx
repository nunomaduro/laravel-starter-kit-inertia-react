import * as React from 'react';
import { BotIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { AssistantRuntimeProvider, type AssistantRuntimeProviderProps } from './assistant-runtime-provider';
import { AssistantThread, type AssistantThreadProps } from './assistant-thread';

export interface AssistantModalProps
    extends Pick<AssistantRuntimeProviderProps, 'endpoint' | 'model' | 'headers' | 'systemPrompt'>,
        Omit<AssistantThreadProps, 'className'> {
    /** Controls the open state of the modal. */
    open: boolean;
    /** Called when the modal should close. */
    onOpenChange: (open: boolean) => void;
    /** Modal title. */
    title?: string;
    /** Custom class for the dialog content. */
    contentClassName?: string;
}

/**
 * A modal dialog wrapping `AssistantThread` and `AssistantRuntimeProvider`.
 * Use this for on-demand AI assistant overlays.
 */
export function AssistantModal({
    open,
    onOpenChange,
    title = 'AI Assistant',
    endpoint,
    model,
    headers,
    systemPrompt,
    placeholder,
    assistantName,
    welcomeMessage,
    contentClassName,
}: AssistantModalProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                className={cn(
                    'flex flex-col p-0 gap-0 sm:max-w-2xl h-[80vh]',
                    contentClassName,
                )}
            >
                <DialogHeader className="border-b px-4 py-3 shrink-0">
                    <DialogTitle className="flex items-center gap-2 text-base">
                        <BotIcon className="size-4 text-primary" />
                        {title}
                    </DialogTitle>
                </DialogHeader>

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
            </DialogContent>
        </Dialog>
    );
}
