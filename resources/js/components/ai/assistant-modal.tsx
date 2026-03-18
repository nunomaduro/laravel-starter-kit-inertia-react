import { BotIcon } from 'lucide-react';

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { AssistantThreadAui } from './assistant-thread-aui';
import type { LaravelAdapterConfig } from './laravel-chat-adapter';

export interface AssistantModalProps extends LaravelAdapterConfig {
    /** Controls the open state of the modal. */
    open: boolean;
    /** Called when the modal should close. */
    onOpenChange: (open: boolean) => void;
    /** Modal title. */
    title?: string;
    /** Placeholder for the composer input. */
    placeholder?: string;
    /** Custom class for the dialog content. */
    contentClassName?: string;
}

/**
 * A modal dialog wrapping the assistant-ui Thread (Laravel streaming backend).
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
    contentClassName,
}: AssistantModalProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                className={cn(
                    'flex h-[80vh] flex-col gap-0 p-0 sm:max-w-2xl',
                    contentClassName,
                )}
            >
                <DialogHeader className="shrink-0 border-b px-4 py-3">
                    <DialogTitle className="flex items-center gap-2 text-base">
                        <BotIcon className="size-4 text-primary" />
                        {title}
                    </DialogTitle>
                </DialogHeader>

                <AssistantThreadAui
                    endpoint={endpoint}
                    model={model}
                    headers={headers}
                    systemPrompt={systemPrompt}
                    placeholder={placeholder}
                    className="min-h-0 flex-1"
                />
            </DialogContent>
        </Dialog>
    );
}
