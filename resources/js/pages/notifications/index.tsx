import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Bell, Check, CheckCheck, Trash2 } from 'lucide-react';

interface Notification {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
}

interface PaginatedNotifications {
    data: Notification[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props extends SharedData {
    notificationsList: PaginatedNotifications;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Notifications', href: '/notifications' },
];

function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;

    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function getNotificationTitle(notification: Notification): string {
    const data = notification.data;
    if (typeof data.title === 'string') return data.title;
    if (typeof data.message === 'string') return data.message;
    const typeParts = notification.type.split('\\');
    return typeParts[typeParts.length - 1] ?? 'Notification';
}

function getNotificationBody(notification: Notification): string | null {
    const data = notification.data;
    if (typeof data.body === 'string') return data.body;
    if (typeof data.description === 'string') return data.description;
    return null;
}

export default function NotificationsIndex() {
    const { notificationsList: notifications } = usePage<Props>().props;

    const markRead = (id: string) => {
        router.post(`/notifications/${id}/read`, {}, { preserveScroll: true });
    };

    const deleteNotification = (id: string) => {
        router.delete(`/notifications/${id}`, { preserveScroll: true });
    };

    const markAllRead = () => {
        router.post('/notifications/read-all', {}, { preserveScroll: true });
    };

    const clearAll = () => {
        router.delete('/notifications', { preserveScroll: true });
    };

    const unreadCount = notifications.data.filter((n) => !n.read_at).length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notifications" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Notifications
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {notifications.total} total
                            {unreadCount > 0 && `, ${unreadCount} unread`}
                        </p>
                    </div>

                    {notifications.data.length > 0 && (
                        <div className="flex gap-2">
                            {unreadCount > 0 && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={markAllRead}
                                >
                                    <CheckCheck className="mr-1.5 size-4" />
                                    Mark all read
                                </Button>
                            )}
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={clearAll}
                            >
                                <Trash2 className="mr-1.5 size-4" />
                                Clear all
                            </Button>
                        </div>
                    )}
                </div>

                {notifications.data.length === 0 ? (
                    <div className="flex flex-1 flex-col items-center justify-center gap-4 py-16 text-center">
                        <div className="rounded-full bg-muted p-4">
                            <Bell className="size-8 text-muted-foreground" />
                        </div>
                        <div>
                            <p className="font-medium">No notifications</p>
                            <p className="text-sm text-muted-foreground">
                                You're all caught up.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="rounded-xl border bg-card">
                        <ul className="divide-y">
                            {notifications.data.map((notification) => (
                                <li
                                    key={notification.id}
                                    className={`flex items-start gap-4 p-4 transition-colors hover:bg-muted/30 ${!notification.read_at ? 'bg-primary/5' : ''}`}
                                >
                                    <div
                                        className={`mt-0.5 size-2 shrink-0 rounded-full ${!notification.read_at ? 'bg-primary' : 'bg-transparent'}`}
                                    />

                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-medium leading-snug">
                                            {getNotificationTitle(notification)}
                                        </p>
                                        {getNotificationBody(notification) && (
                                            <p className="mt-0.5 text-sm text-muted-foreground">
                                                {getNotificationBody(notification)}
                                            </p>
                                        )}
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {formatDate(notification.created_at)}
                                        </p>
                                    </div>

                                    <div className="flex shrink-0 items-center gap-1">
                                        {!notification.read_at && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-7"
                                                title="Mark as read"
                                                onClick={() =>
                                                    markRead(notification.id)
                                                }
                                            >
                                                <Check className="size-3.5" />
                                            </Button>
                                        )}
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="size-7 text-muted-foreground hover:text-destructive"
                                            title="Delete"
                                            onClick={() =>
                                                deleteNotification(
                                                    notification.id,
                                                )
                                            }
                                        >
                                            <Trash2 className="size-3.5" />
                                        </Button>
                                    </div>
                                </li>
                            ))}
                        </ul>

                        {notifications.last_page > 1 && (
                            <div className="flex items-center justify-between border-t px-4 py-3">
                                <p className="text-sm text-muted-foreground">
                                    Page {notifications.current_page} of{' '}
                                    {notifications.last_page}
                                </p>
                                <div className="flex gap-2">
                                    {notifications.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                router.visit(
                                                    `/notifications?page=${notifications.current_page - 1}`,
                                                )
                                            }
                                        >
                                            Previous
                                        </Button>
                                    )}
                                    {notifications.current_page <
                                        notifications.last_page && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                router.visit(
                                                    `/notifications?page=${notifications.current_page + 1}`,
                                                )
                                            }
                                        >
                                            Next
                                        </Button>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
