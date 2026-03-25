import { useEffect, useRef } from 'react';
import { X, Maximize2 } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface ChatSlideOverProps {
    open: boolean;
    onClose: () => void;
    onUnreadChange?: (count: number) => void;
}

export function ChatSlideOver({ open, onClose, onUnreadChange: _onUnreadChange }: ChatSlideOverProps) {
    const panelRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!open) return;
        function handleEsc(e: KeyboardEvent) {
            if (e.key === 'Escape') onClose();
        }
        window.addEventListener('keydown', handleEsc);
        return () => window.removeEventListener('keydown', handleEsc);
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-black/40 transition-opacity duration-200"
                onClick={onClose}
                aria-hidden
            />

            {/* Panel */}
            <div
                ref={panelRef}
                className="absolute top-0 right-0 flex h-full w-[560px] max-w-[calc(100vw-2rem)] flex-col border-l bg-background shadow-xl transition-transform duration-200"
                role="dialog"
                aria-label="AI Assistant"
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b px-4 py-3">
                    <h2 className="font-mono text-sm font-semibold tracking-tight">
                        AI Assistant
                    </h2>
                    <div className="flex items-center gap-1">
                        <Link
                            href="/chat"
                            className="rounded-md p-1.5 text-muted-foreground transition-colors duration-100 hover:bg-muted hover:text-foreground"
                            aria-label="Open full page chat"
                            data-pan="global-chat-expand"
                        >
                            <Maximize2 className="size-4" />
                        </Link>
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-md p-1.5 text-muted-foreground transition-colors duration-100 hover:bg-muted hover:text-foreground"
                            aria-label="Close chat"
                            data-pan="global-chat-close"
                        >
                            <X className="size-4" />
                        </button>
                    </div>
                </div>

                {/* Placeholder body - to be replaced in Step 3 */}
                <div className="flex flex-1 items-center justify-center text-sm text-muted-foreground">
                    <p>Chat panel loading...</p>
                </div>
            </div>
        </div>
    );
}
