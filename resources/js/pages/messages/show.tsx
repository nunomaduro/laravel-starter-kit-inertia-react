import { Form, Head } from '@inertiajs/react';
import { Send } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { MessageBubble } from '@/components/booking/message-bubble';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Conversation, Message } from '@/types';

type Props = {
    conversation: Conversation;
    messages: Message[];
    auth: { user: { id: string } };
};

export default function MessageShow({ conversation, messages, auth }: Props) {
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Messages', href: '/messages' },
        { title: conversation.other_participant.name, href: `/messages/${conversation.id}` },
    ];

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Chat with ${conversation.other_participant.name}`} />
            <div className="flex h-[calc(100vh-12rem)] flex-col p-4">
                <div className="mb-4 border-b pb-4">
                    <h1 className="font-semibold">{conversation.other_participant.name}</h1>
                    <p className="text-sm text-muted-foreground">{conversation.property.name}</p>
                </div>

                <div className="flex-1 space-y-3 overflow-y-auto pr-2">
                    {messages.length === 0 ? (
                        <div className="flex h-full items-center justify-center">
                            <p className="text-muted-foreground">No messages yet. Start the conversation!</p>
                        </div>
                    ) : (
                        messages.map((message) => (
                            <MessageBubble
                                key={message.id}
                                message={message}
                                isOwn={message.sender_id === auth.user.id}
                            />
                        ))
                    )}
                    <div ref={messagesEndRef} />
                </div>

                <div className="mt-4 border-t pt-4">
                    <Form action={`/messages/${conversation.id}`} method="post" className="flex gap-2">
                        {({ processing }) => (
                            <>
                                <Input name="body" placeholder="Type a message..." className="flex-1" autoComplete="off" />
                                <Button type="submit" disabled={processing} size="icon">
                                    {processing ? <Spinner /> : <Send className="size-4" />}
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </AppLayout>
    );
}
