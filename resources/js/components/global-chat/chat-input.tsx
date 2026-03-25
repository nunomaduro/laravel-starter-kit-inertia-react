import { useCallback, useRef } from 'react';
import { FileUpload, type FileAttachment } from './file-upload';
import { VoiceInput } from './voice-input';

interface ChatInputProps {
    onSend: (content: string, files?: FileAttachment[]) => void;
    onStop: () => void;
    isStreaming: boolean;
    disabled?: boolean;
}

export function ChatInput({ onSend, onStop, isStreaming, disabled }: ChatInputProps) {
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const filesRef = useRef<FileAttachment[]>([]);

    const adjustHeight = useCallback(() => {
        const el = textareaRef.current;
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = `${Math.min(el.scrollHeight, 120)}px`;
    }, []);

    const handleSubmit = useCallback(() => {
        const el = textareaRef.current;
        if (!el) return;
        const content = el.value.trim();
        if ((!content && filesRef.current.length === 0) || isStreaming || disabled) return;
        onSend(content, filesRef.current.length > 0 ? [...filesRef.current] : undefined);
        el.value = '';
        el.style.height = 'auto';
        filesRef.current = [];
    }, [onSend, isStreaming, disabled]);

    const handleKeyDown = useCallback(
        (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSubmit();
            }
        },
        [handleSubmit],
    );

    const handleVoiceResult = useCallback((transcript: string) => {
        const el = textareaRef.current;
        if (!el) return;
        el.value = el.value ? `${el.value} ${transcript}` : transcript;
        adjustHeight();
    }, [adjustHeight]);

    const handleFilesChange = useCallback((files: FileAttachment[]) => {
        filesRef.current = files;
    }, []);

    return (
        <div className="border-t px-3 py-2" data-pan="global-chat-input">
            <FileUpload onChange={handleFilesChange} />
            <div className="flex items-end gap-1.5">
                <VoiceInput onResult={handleVoiceResult} />
                <textarea
                    ref={textareaRef}
                    placeholder={disabled ? 'Preparing...' : 'Type a message...'}
                    disabled={disabled}
                    rows={1}
                    onInput={adjustHeight}
                    onKeyDown={handleKeyDown}
                    className="flex max-h-[120px] min-h-8 flex-1 resize-none rounded-lg border bg-transparent px-3 py-1.5 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-[oklch(0.65_0.14_165)] focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                />
                {isStreaming ? (
                    <button
                        type="button"
                        onClick={onStop}
                        className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-destructive text-white transition-colors duration-100"
                        aria-label="Stop"
                    >
                        <span className="size-3 rounded-sm bg-white" />
                    </button>
                ) : (
                    <button
                        type="button"
                        onClick={handleSubmit}
                        disabled={disabled}
                        className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-[oklch(0.65_0.14_165)] text-white transition-colors duration-100 hover:bg-[oklch(0.72_0.14_165)] disabled:opacity-50"
                        aria-label="Send"
                    >
                        <svg className="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M12 19V5M5 12l7-7 7 7" />
                        </svg>
                    </button>
                )}
            </div>
            <p className="mt-1 text-center text-[10px] text-muted-foreground/50">
                Enter to send, Shift+Enter for newline
            </p>
        </div>
    );
}
