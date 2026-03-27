import { cn } from '@/lib/utils';
import type { Message } from '@/types';

type MessageBubbleProps = {
    message: Message;
    isOwn: boolean;
};

export function MessageBubble({ message, isOwn }: MessageBubbleProps) {
    return (
        <div className={cn('flex', isOwn ? 'justify-end' : 'justify-start')}>
            <div
                className={cn(
                    'max-w-[75%] rounded-2xl px-4 py-2',
                    isOwn
                        ? 'rounded-br-md bg-primary text-primary-foreground'
                        : 'rounded-bl-md bg-neutral-100 text-foreground dark:bg-neutral-800',
                )}
            >
                <p className="text-sm leading-relaxed">{message.body}</p>
                <time
                    className={cn(
                        'mt-1 block text-[10px]',
                        isOwn ? 'text-primary-foreground/70' : 'text-muted-foreground',
                    )}
                >
                    {new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </time>
            </div>
        </div>
    );
}
