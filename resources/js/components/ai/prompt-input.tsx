import * as React from 'react';
import { SendIcon, SquareIcon, PaperclipIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

export interface PromptInputProps {
    placeholder?: string;
    isLoading?: boolean;
    /** Called when the user submits a prompt. */
    onSubmit?: (value: string) => void;
    /** Called when the user clicks the stop button during streaming. */
    onStop?: () => void;
    /** Show file attachment button. */
    showAttach?: boolean;
    /** Called when a file is selected. */
    onAttach?: (file: File) => void;
    /** Disable the input. */
    disabled?: boolean;
    /** Maximum rows before scrolling (default 6). */
    maxRows?: number;
    className?: string;
}

/**
 * Enhanced prompt input with auto-resize textarea, send/stop toggle, and optional
 * file attachment. Submits on Enter (Shift+Enter for newline).
 */
export function PromptInput({
    placeholder = 'Ask anything…',
    isLoading = false,
    onSubmit,
    onStop,
    showAttach = false,
    onAttach,
    disabled = false,
    maxRows = 6,
    className,
}: PromptInputProps) {
    const [value, setValue] = React.useState('');
    const textareaRef = React.useRef<HTMLTextAreaElement>(null);
    const fileInputRef = React.useRef<HTMLInputElement>(null);

    // Auto-resize textarea
    React.useEffect(() => {
        const el = textareaRef.current;
        if (!el) return;
        el.style.height = 'auto';
        const lineHeight = parseInt(getComputedStyle(el).lineHeight, 10) || 20;
        const maxHeight = lineHeight * maxRows;
        el.style.height = `${Math.min(el.scrollHeight, maxHeight)}px`;
        el.style.overflowY = el.scrollHeight > maxHeight ? 'auto' : 'hidden';
    }, [value, maxRows]);

    const handleSubmit = React.useCallback(() => {
        const trimmed = value.trim();
        if (!trimmed || isLoading || disabled) return;
        onSubmit?.(trimmed);
        setValue('');
    }, [value, isLoading, disabled, onSubmit]);

    const handleKeyDown = React.useCallback(
        (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSubmit();
            }
        },
        [handleSubmit],
    );

    const handleFileChange = React.useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            const file = e.target.files?.[0];
            if (file) onAttach?.(file);
            if (fileInputRef.current) fileInputRef.current.value = '';
        },
        [onAttach],
    );

    const canSubmit = value.trim().length > 0 && !disabled;

    return (
        <div
            className={cn(
                'flex items-end gap-2 rounded-xl border bg-background shadow-sm px-3 py-2',
                (disabled || isLoading) && 'opacity-80',
                className,
            )}
        >
            {/* File attachment */}
            {showAttach && (
                <>
                    <input
                        ref={fileInputRef}
                        type="file"
                        className="hidden"
                        onChange={handleFileChange}
                        aria-label="Attach file"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        className="shrink-0 text-muted-foreground"
                        onClick={() => fileInputRef.current?.click()}
                        disabled={disabled}
                        aria-label="Attach file"
                    >
                        <PaperclipIcon className="size-4" />
                    </Button>
                </>
            )}

            {/* Textarea */}
            <textarea
                ref={textareaRef}
                value={value}
                onChange={(e) => setValue(e.target.value)}
                onKeyDown={handleKeyDown}
                placeholder={placeholder}
                disabled={disabled}
                rows={1}
                aria-label="Message input"
                className={cn(
                    'flex-1 resize-none bg-transparent text-sm leading-relaxed outline-none',
                    'placeholder:text-muted-foreground',
                    'disabled:cursor-not-allowed',
                )}
            />

            {/* Send / Stop */}
            {isLoading ? (
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    className="shrink-0 text-muted-foreground hover:text-destructive"
                    onClick={onStop}
                    aria-label="Stop generation"
                >
                    <SquareIcon className="size-4 fill-current" />
                </Button>
            ) : (
                <Button
                    type="button"
                    size="icon-sm"
                    disabled={!canSubmit}
                    onClick={handleSubmit}
                    aria-label="Send message"
                    className="shrink-0"
                >
                    <SendIcon className="size-4" />
                </Button>
            )}
        </div>
    );
}
