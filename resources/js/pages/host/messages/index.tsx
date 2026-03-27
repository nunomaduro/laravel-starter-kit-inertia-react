import { Head, Link } from '@inertiajs/react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useInitials } from '@/hooks/use-initials';
import HostLayout from '@/layouts/host-layout';
import type { BreadcrumbItem, Conversation, PaginatedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Messages', href: '/host/messages' }];

type Props = {
    conversations: PaginatedData<Conversation>;
};

export default function HostMessagesIndex({ conversations }: Props) {
    const getInitials = useInitials();

    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title="Messages" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Messages</h1>

                {conversations.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16">
                        <p className="text-muted-foreground">No conversations yet.</p>
                    </div>
                ) : (
                    <div className="flex flex-col divide-y rounded-lg border">
                        {conversations.data.map((conversation) => (
                            <Link
                                key={conversation.id}
                                href={`/messages/${conversation.id}`}
                                className="flex items-center gap-4 p-4 transition-colors hover:bg-accent"
                            >
                                <Avatar className="size-10">
                                    <AvatarImage
                                        src={conversation.other_participant.avatar ?? undefined}
                                        alt={conversation.other_participant.name}
                                    />
                                    <AvatarFallback className="bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {getInitials(conversation.other_participant.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center justify-between gap-2">
                                        <span className="truncate font-medium">{conversation.other_participant.name}</span>
                                        <span className="shrink-0 text-xs text-muted-foreground">
                                            {new Date(conversation.updated_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                    <p className="truncate text-sm text-muted-foreground">{conversation.property.name}</p>
                                    {conversation.last_message && (
                                        <p className="mt-0.5 truncate text-sm text-muted-foreground">
                                            {conversation.last_message.body}
                                        </p>
                                    )}
                                </div>
                                {conversation.unread_count > 0 && (
                                    <Badge variant="default" className="shrink-0">
                                        {conversation.unread_count}
                                    </Badge>
                                )}
                            </Link>
                        ))}
                    </div>
                )}

                {(conversations.prev_page_url || conversations.next_page_url) && (
                    <div className="flex items-center justify-between">
                        {conversations.prev_page_url ? (
                            <Link href={conversations.prev_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Previous
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                        <span className="text-sm text-muted-foreground">
                            Page {conversations.current_page} of {conversations.last_page}
                        </span>
                        {conversations.next_page_url ? (
                            <Link href={conversations.next_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Next
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                    </div>
                )}
            </div>
        </HostLayout>
    );
}
