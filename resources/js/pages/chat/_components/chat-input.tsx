import { Button } from '@/components/ui/button';
import { ArrowUp, Square } from 'lucide-react';
import { useCallback, useRef } from 'react';

export function ChatInput({
    onSend,
    onStop,
    isLoading,
    disabled,
}: {
    onSend: (content: string) => void;
    onStop: () => void;
    isLoading: boolean;
    disabled: boolean;
}) {
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    const adjustHeight = useCallback(() => {
        const el = textareaRef.current;
        if (!el) return;
        el.style.height = 'auto';
        const maxHeight = 6 * 24; // ~6 rows
        el.style.height = `${Math.min(el.scrollHeight, maxHeight)}px`;
    }, []);

    const handleSubmit = useCallback(() => {
        const el = textareaRef.current;
        if (!el) return;
        const content = el.value.trim();
        if (!content || isLoading || disabled) return;
        onSend(content);
        el.value = '';
        el.style.height = 'auto';
    }, [onSend, isLoading, disabled]);

    const handleKeyDown = useCallback(
        (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSubmit();
            }
        },
        [handleSubmit],
    );

    return (
        <div className="border-t px-4 py-3" data-pan="chat-send-message">
            <div className="flex items-end gap-2">
                <textarea
                    ref={textareaRef}
                    placeholder={disabled ? 'Preparing chat...' : 'Type a message...'}
                    disabled={disabled}
                    rows={1}
                    onInput={adjustHeight}
                    onKeyDown={handleKeyDown}
                    className="border-input bg-background ring-offset-background placeholder:text-muted-foreground flex max-h-36 min-h-9 flex-1 resize-none rounded-lg border px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                />
                {isLoading ? (
                    <Button type="button" size="icon" variant="destructive" onClick={onStop}>
                        <Square className="size-4" />
                    </Button>
                ) : (
                    <Button type="button" size="icon" onClick={handleSubmit} disabled={disabled}>
                        <ArrowUp className="size-4" />
                    </Button>
                )}
            </div>
            <p className="mt-1.5 text-center text-xs text-muted-foreground">
                Press Enter to send, Shift+Enter for a new line
            </p>
        </div>
    );
}
