import * as React from 'react';
import { BellIcon, CheckCheckIcon, InboxIcon, Trash2Icon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';

export interface Notification {
    id: string;
    title: string;
    message?: string;
    timestamp: Date | string;
    read?: boolean;
    type?: 'info' | 'success' | 'warning' | 'error';
    icon?: React.ReactNode;
    action?: {
        label: string;
        onClick: () => void;
    };
}

export interface NotificationCenterProps {
    notifications: Notification[];
    onMarkAllRead?: () => void;
    onMarkRead?: (id: string) => void;
    onDelete?: (id: string) => void;
    onClearAll?: () => void;
    className?: string;
    maxHeight?: number;
}

const typeColors: Record<NonNullable<Notification['type']>, string> = {
    info: 'bg-blue-500',
    success: 'bg-green-500',
    warning: 'bg-amber-500',
    error: 'bg-red-500',
};

function formatTimestamp(ts: Date | string): string {
    const date = ts instanceof Date ? ts : new Date(ts);
    const diff = Date.now() - date.getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return `${hrs}h ago`;
    const days = Math.floor(hrs / 24);
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
}

function NotificationItem({
    notification,
    onMarkRead,
    onDelete,
}: {
    notification: Notification;
    onMarkRead?: (id: string) => void;
    onDelete?: (id: string) => void;
}) {
    return (
        <div
            data-slot="notification-item"
            className={cn(
                'group relative flex gap-3 px-4 py-3 transition-colors hover:bg-muted/50',
                !notification.read && 'bg-primary/5',
            )}
        >
            <div className="relative mt-0.5 shrink-0">
                {notification.icon ? (
                    <div className="flex size-8 items-center justify-center rounded-full bg-muted text-muted-foreground">
                        {notification.icon}
                    </div>
                ) : (
                    <div
                        className={cn(
                            'size-2 mt-2.5 rounded-full',
                            notification.type ? typeColors[notification.type] : 'bg-muted-foreground',
                            notification.read && 'opacity-40',
                        )}
                    />
                )}
                {!notification.read && !notification.icon && (
                    <span className="absolute -right-0.5 -top-0.5 size-2 rounded-full bg-primary" />
                )}
            </div>
            <div className="min-w-0 flex-1">
                <p className={cn('text-sm', !notification.read && 'font-semibold')}>
                    {notification.title}
                </p>
                {notification.message && (
                    <p className="mt-0.5 text-xs text-muted-foreground line-clamp-2">
                        {notification.message}
                    </p>
                )}
                <p className="mt-1 text-[10px] text-muted-foreground">
                    {formatTimestamp(notification.timestamp)}
                </p>
                {notification.action && (
                    <button
                        type="button"
                        onClick={notification.action.onClick}
                        className="mt-1.5 text-xs font-medium text-primary hover:underline"
                    >
                        {notification.action.label}
                    </button>
                )}
            </div>
            <div className="flex shrink-0 flex-col items-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                {onMarkRead && !notification.read && (
                    <button
                        type="button"
                        onClick={() => onMarkRead(notification.id)}
                        className="rounded p-0.5 text-muted-foreground hover:text-foreground"
                        aria-label="Mark as read"
                    >
                        <CheckCheckIcon className="size-3.5" />
                    </button>
                )}
                {onDelete && (
                    <button
                        type="button"
                        onClick={() => onDelete(notification.id)}
                        className="rounded p-0.5 text-muted-foreground hover:text-destructive"
                        aria-label="Delete notification"
                    >
                        <Trash2Icon className="size-3.5" />
                    </button>
                )}
            </div>
        </div>
    );
}

function NotificationCenter({
    notifications,
    onMarkAllRead,
    onMarkRead,
    onDelete,
    onClearAll,
    className,
    maxHeight = 400,
}: NotificationCenterProps) {
    const unreadCount = notifications.filter((n) => !n.read).length;
    const [open, setOpen] = React.useState(false);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className={cn('relative', className)}
                    aria-label={`Notifications${unreadCount > 0 ? `, ${unreadCount} unread` : ''}`}
                >
                    <BellIcon className="size-5" />
                    {unreadCount > 0 && (
                        <Badge
                            variant="destructive"
                            className="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center px-1 text-[10px]"
                        >
                            {unreadCount > 99 ? '99+' : unreadCount}
                        </Badge>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="w-80 p-0" data-slot="notification-center">
                <div className="flex items-center justify-between px-4 py-3">
                    <h3 className="text-sm font-semibold">Notifications</h3>
                    <div className="flex items-center gap-1">
                        {onMarkAllRead && unreadCount > 0 && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-6 px-2 text-xs"
                                onClick={() => {
                                    onMarkAllRead();
                                }}
                            >
                                Mark all read
                            </Button>
                        )}
                        {onClearAll && notifications.length > 0 && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-6 px-2 text-xs text-destructive hover:text-destructive"
                                onClick={() => {
                                    onClearAll();
                                    setOpen(false);
                                }}
                            >
                                Clear all
                            </Button>
                        )}
                    </div>
                </div>
                <Separator />
                <div
                    className="overflow-y-auto"
                    style={{ maxHeight: `${maxHeight}px` }}
                >
                    {notifications.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-10 text-muted-foreground">
                            <InboxIcon className="mb-2 size-8 opacity-40" />
                            <p className="text-sm">No notifications</p>
                        </div>
                    ) : (
                        <div className="divide-y divide-border">
                            {notifications.map((notification) => (
                                <NotificationItem
                                    key={notification.id}
                                    notification={notification}
                                    onMarkRead={onMarkRead}
                                    onDelete={onDelete}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </PopoverContent>
        </Popover>
    );
}

export { NotificationCenter };
