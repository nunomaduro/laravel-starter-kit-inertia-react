import { useCallback, useEffect, useState } from 'react';
import { MessageSquare } from 'lucide-react';
import { ChatSlideOver } from './chat-slide-over';

export function GlobalChatWidget() {
    const [open, setOpen] = useState(false);
    const [unreadCount, setUnreadCount] = useState(0);

    const toggle = useCallback(() => setOpen((prev) => !prev), []);

    useEffect(() => {
        function handleKeyDown(e: KeyboardEvent) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                const target = e.target as HTMLElement | null;
                const tag = target?.tagName?.toLowerCase();
                if (
                    tag === 'input' ||
                    tag === 'textarea' ||
                    target?.isContentEditable
                ) {
                    return;
                }
                e.preventDefault();
                toggle();
            }
        }

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [toggle]);

    return (
        <>
            <button
                type="button"
                onClick={toggle}
                className="fixed right-6 bottom-6 z-50 flex size-12 items-center justify-center rounded-xl bg-[oklch(0.65_0.14_165)] text-white shadow-lg transition-all duration-100 hover:bg-[oklch(0.72_0.14_165)] focus-visible:ring-2 focus-visible:ring-[oklch(0.65_0.14_165)] focus-visible:ring-offset-2 focus-visible:outline-none active:scale-95"
                aria-label="Open AI chat"
                data-pan="global-chat-toggle"
            >
                <MessageSquare className="size-5" />
                {unreadCount > 0 && (
                    <span className="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-destructive text-[10px] font-medium text-white">
                        {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                )}
            </button>

            <span className="pointer-events-none fixed right-20 bottom-9 z-50 select-none font-mono text-[11px] text-muted-foreground/40">
                {navigator.platform?.includes('Mac') ? '\u2318K' : 'Ctrl+K'}
            </span>

            <ChatSlideOver
                open={open}
                onClose={() => setOpen(false)}
                onUnreadChange={setUnreadCount}
            />
        </>
    );
}
