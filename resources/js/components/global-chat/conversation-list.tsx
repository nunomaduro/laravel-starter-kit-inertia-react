import { Plus, Search } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

export interface ConversationItem {
    id: string;
    title: string;
    created_at: string;
    updated_at: string;
}

interface ConversationListProps {
    activeId: string | null;
    onSelect: (id: string) => void;
    onNewChat: () => void;
    conversations: ConversationItem[];
    loading: boolean;
}

export function ConversationList({
    activeId,
    onSelect,
    onNewChat,
    conversations,
    loading,
}: ConversationListProps) {
    const [search, setSearch] = useState('');

    const filtered = search
        ? conversations.filter((c) =>
              c.title.toLowerCase().includes(search.toLowerCase()),
          )
        : conversations;

    return (
        <div className="flex h-full w-40 shrink-0 flex-col border-r">
            {/* New chat button */}
            <div className="border-b p-2">
                <button
                    type="button"
                    onClick={onNewChat}
                    className="flex w-full items-center justify-center gap-1.5 rounded-md bg-[oklch(0.65_0.14_165)] px-2 py-1.5 text-xs font-medium text-white transition-colors duration-100 hover:bg-[oklch(0.72_0.14_165)]"
                    data-pan="global-chat-new"
                >
                    <Plus className="size-3" />
                    New Chat
                </button>
            </div>

            {/* Search */}
            <div className="border-b p-2">
                <div className="relative">
                    <Search className="absolute top-1/2 left-2 size-3 -translate-y-1/2 text-muted-foreground" />
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search..."
                        className="h-7 w-full rounded-md border bg-transparent pl-7 pr-2 text-xs placeholder:text-muted-foreground focus:ring-1 focus:ring-[oklch(0.65_0.14_165)] focus:outline-none"
                    />
                </div>
            </div>

            {/* List */}
            <div className="flex-1 overflow-y-auto">
                {loading ? (
                    <div className="space-y-1 p-2">
                        {[1, 2, 3].map((i) => (
                            <div
                                key={i}
                                className="h-10 animate-pulse rounded-md bg-muted"
                            />
                        ))}
                    </div>
                ) : filtered.length === 0 ? (
                    <p className="p-3 text-center text-[11px] text-muted-foreground">
                        {search ? 'No matches' : 'No conversations'}
                    </p>
                ) : (
                    <ul className="space-y-0.5 p-1">
                        {filtered.map((c) => (
                            <li key={c.id}>
                                <button
                                    type="button"
                                    onClick={() => onSelect(c.id)}
                                    className={`w-full rounded-md px-2 py-1.5 text-left transition-colors duration-100 ${
                                        activeId === c.id
                                            ? 'border-l-2 border-[oklch(0.65_0.14_165)] bg-muted/60 text-foreground'
                                            : 'text-muted-foreground hover:bg-muted/40 hover:text-foreground'
                                    }`}
                                >
                                    <span className="line-clamp-1 text-[11px] leading-tight">
                                        {c.title}
                                    </span>
                                    <span className="mt-0.5 block font-mono text-[9px] text-muted-foreground/60">
                                        {formatRelativeTime(c.updated_at)}
                                    </span>
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </div>
    );
}

function formatRelativeTime(dateStr: string): string {
    const d = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffMin = Math.floor(diffMs / 60000);

    if (diffMin < 1) return 'now';
    if (diffMin < 60) return `${diffMin}m`;
    const diffHrs = Math.floor(diffMin / 60);
    if (diffHrs < 24) return `${diffHrs}h`;
    const diffDays = Math.floor(diffHrs / 24);
    if (diffDays < 7) return `${diffDays}d`;
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}
