import { MessageSquare } from 'lucide-react';

const suggestions = [
    'Explain how this app works',
    'Help me write a blog post',
    'What can you help me with?',
];

export function EmptyState({ onSend }: { onSend: (content: string) => void }) {
    return (
        <div className="flex min-h-0 flex-1 items-center justify-center p-4">
            <div className="flex max-w-sm flex-col items-center text-center">
                <div className="mb-4 flex size-12 items-center justify-center rounded-full bg-muted">
                    <MessageSquare className="size-6 text-muted-foreground" />
                </div>
                <h2 className="text-lg font-semibold">How can I help?</h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    Start a conversation or try one of these:
                </p>
                <div className="mt-4 flex flex-wrap justify-center gap-2">
                    {suggestions.map((s) => (
                        <button
                            key={s}
                            type="button"
                            onClick={() => onSend(s)}
                            className="rounded-full border bg-background px-3 py-1.5 text-xs text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                        >
                            {s}
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
