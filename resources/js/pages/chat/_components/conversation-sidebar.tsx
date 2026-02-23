import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import { toast } from 'sonner';

type ConversationItem = {
    id: string;
    title: string;
    created_at: string;
    updated_at: string;
};

function groupConversations(conversations: ConversationItem[]) {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today.getTime() - 86400000);
    const weekAgo = new Date(today.getTime() - 7 * 86400000);

    const groups: { label: string; items: ConversationItem[] }[] = [
        { label: 'Today', items: [] },
        { label: 'Yesterday', items: [] },
        { label: 'Previous 7 Days', items: [] },
        { label: 'Older', items: [] },
    ];

    for (const c of conversations) {
        const d = new Date(c.updated_at);
        if (d >= today) groups[0].items.push(c);
        else if (d >= yesterday) groups[1].items.push(c);
        else if (d >= weekAgo) groups[2].items.push(c);
        else groups[3].items.push(c);
    }

    return groups.filter((g) => g.items.length > 0);
}

export function ConversationSidebar({
    conversations,
    conversationsLoading,
    activeConversationId,
    onNewChat,
    onSelectConversation,
    onConversationDeleted,
    onConversationRenamed,
    isMobile,
}: {
    conversations: ConversationItem[];
    conversationsLoading: boolean;
    activeConversationId: string | null;
    onNewChat: () => void;
    onSelectConversation: (id: string) => void;
    onConversationDeleted: () => void;
    onConversationRenamed?: (id: string, title: string) => void;
    isMobile?: boolean;
}) {
    const [deleteTarget, setDeleteTarget] = useState<ConversationItem | null>(
        null,
    );
    const [deleting, setDeleting] = useState(false);
    const [editingId, setEditingId] = useState<string | null>(null);
    const [editTitle, setEditTitle] = useState('');
    const editInputRef = useRef<HTMLInputElement>(null);

    const handleDelete = useCallback(async () => {
        if (!deleteTarget) return;
        setDeleting(true);
        try {
            const res = await fetch(`/api/conversations/${deleteTarget.id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
            });
            if (res.ok || res.status === 204) {
                toast.success('Conversation deleted');
                onConversationDeleted();
            } else {
                toast.error('Failed to delete conversation');
            }
        } catch {
            toast.error('Failed to delete conversation');
        } finally {
            setDeleting(false);
            setDeleteTarget(null);
        }
    }, [deleteTarget, onConversationDeleted]);

    const startEditing = useCallback((c: ConversationItem) => {
        setEditingId(c.id);
        setEditTitle(c.title);
        setTimeout(() => editInputRef.current?.select(), 0);
    }, []);

    const cancelEditing = useCallback(() => {
        setEditingId(null);
        setEditTitle('');
    }, []);

    const saveEditing = useCallback(async () => {
        if (!editingId) return;
        const trimmed = editTitle.trim();
        if (!trimmed) {
            cancelEditing();
            return;
        }

        const originalTitle = conversations.find(
            (c) => c.id === editingId,
        )?.title;
        if (trimmed === originalTitle) {
            cancelEditing();
            return;
        }

        // Optimistic update
        onConversationRenamed?.(editingId, trimmed);
        cancelEditing();

        try {
            const res = await fetch(`/api/conversations/${editingId}`, {
                method: 'PATCH',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ title: trimmed }),
            });
            if (!res.ok) {
                // Revert on failure
                if (originalTitle)
                    onConversationRenamed?.(editingId, originalTitle);
                toast.error('Failed to rename conversation');
            } else {
                toast.success('Conversation renamed');
            }
        } catch {
            if (originalTitle)
                onConversationRenamed?.(editingId, originalTitle);
            toast.error('Failed to rename conversation');
        }
    }, [
        editingId,
        editTitle,
        conversations,
        onConversationRenamed,
        cancelEditing,
    ]);

    const groups = groupConversations(conversations);

    return (
        <aside
            className={
                isMobile
                    ? 'flex h-full flex-col overflow-hidden'
                    : 'flex w-64 shrink-0 flex-col overflow-hidden rounded-xl border bg-card'
            }
            data-pan="chat-conversation-list"
        >
            <div className="flex items-center justify-between border-b px-3 py-2.5">
                <span className="text-sm font-medium">Conversations</span>
                <Button
                    variant="ghost"
                    size="icon-xs"
                    onClick={onNewChat}
                    data-pan="chat-new-conversation"
                >
                    <Plus className="size-4" />
                </Button>
            </div>

            <div className="flex-1 overflow-y-auto px-2 py-2">
                {conversationsLoading ? (
                    <div className="space-y-2 p-2">
                        {[1, 2, 3].map((i) => (
                            <div
                                key={i}
                                className="h-8 animate-pulse rounded-lg bg-muted"
                            />
                        ))}
                    </div>
                ) : conversations.length === 0 ? (
                    <p className="p-2 text-center text-xs text-muted-foreground">
                        No conversations yet
                    </p>
                ) : (
                    groups.map((group) => (
                        <div key={group.label} className="mb-3">
                            <p className="mb-1 px-2 text-xs font-medium text-muted-foreground">
                                {group.label}
                            </p>
                            <ul className="space-y-0.5">
                                {group.items.map((c) => (
                                    <li key={c.id} className="group relative">
                                        {editingId === c.id ? (
                                            <input
                                                ref={editInputRef}
                                                type="text"
                                                value={editTitle}
                                                onChange={(e) =>
                                                    setEditTitle(e.target.value)
                                                }
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter')
                                                        saveEditing();
                                                    if (e.key === 'Escape')
                                                        cancelEditing();
                                                }}
                                                onBlur={saveEditing}
                                                className="w-full rounded-lg border bg-background px-2 py-1.5 text-sm outline-none focus:ring-1 focus:ring-ring"
                                                autoFocus
                                            />
                                        ) : (
                                            <>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        onSelectConversation(
                                                            c.id,
                                                        )
                                                    }
                                                    className={`w-full rounded-lg px-2 py-1.5 pr-14 text-left text-sm transition-colors ${
                                                        activeConversationId ===
                                                        c.id
                                                            ? 'bg-accent text-accent-foreground'
                                                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                                    }`}
                                                >
                                                    <span className="line-clamp-1">
                                                        {c.title}
                                                    </span>
                                                </button>
                                                <div className="absolute top-1/2 right-1.5 flex -translate-y-1/2 gap-0.5 opacity-0 transition-opacity group-hover:opacity-100">
                                                    <button
                                                        type="button"
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            startEditing(c);
                                                        }}
                                                        className="rounded p-1 text-muted-foreground hover:text-foreground"
                                                        data-pan="chat-rename-conversation"
                                                    >
                                                        <Pencil className="size-3.5" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            setDeleteTarget(c);
                                                        }}
                                                        className="rounded p-1 text-muted-foreground hover:text-destructive"
                                                        data-pan="chat-delete-conversation"
                                                    >
                                                        <Trash2 className="size-3.5" />
                                                    </button>
                                                </div>
                                            </>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))
                )}
            </div>

            <Dialog
                open={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete conversation</DialogTitle>
                        <DialogDescription>
                            This will permanently delete &ldquo;
                            {deleteTarget?.title}&rdquo; and all its messages.
                            This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline" disabled={deleting}>
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={deleting}
                        >
                            {deleting ? 'Deleting...' : 'Delete'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </aside>
    );
}

function getCsrfToken(): string {
    if (typeof document === 'undefined') return '';
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    const raw = match ? match[1] : '';
    try {
        return raw ? decodeURIComponent(raw) : '';
    } catch {
        return raw;
    }
}
