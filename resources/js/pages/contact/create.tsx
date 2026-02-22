import ContactSubmissionController from '@/actions/App/Http/Controllers/ContactSubmissionController';
import { dashboard, home } from '@/routes';
import { create as contactCreate } from '@/routes/contact';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import HoneypotFields from '@/components/honeypot-fields';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';

function ContactFormContent({
    isLoggedIn,
    flashStatus,
}: {
    isLoggedIn: boolean;
    flashStatus?: string;
}) {
    const { auth } = usePage<SharedData>().props;
    const user = auth?.user ?? null;

    return (
        <>
            {flashStatus && (
                <p
                    className={
                        isLoggedIn
                            ? 'mb-4 text-sm text-muted-foreground'
                            : 'mb-4 text-center text-sm text-muted-foreground'
                    }
                >
                    {flashStatus}
                </p>
            )}
            <Form
                action={ContactSubmissionController.store.url()}
                method="post"
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <HoneypotFields />
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus={!isLoggedIn}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Your name"
                                    defaultValue={user?.name}
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autoComplete="email"
                                    name="email"
                                    placeholder="you@example.com"
                                    defaultValue={user?.email}
                                />
                                <InputError message={errors.email} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="subject">Subject</Label>
                                <Input
                                    id="subject"
                                    type="text"
                                    required
                                    name="subject"
                                    placeholder="Subject"
                                />
                                <InputError message={errors.subject} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="message">Message</Label>
                                <textarea
                                    id="message"
                                    name="message"
                                    required
                                    rows={5}
                                    className="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="Your message..."
                                />
                                <InputError message={errors.message} />
                            </div>
                            <Button type="submit" className="w-full">
                                {processing && (
                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                )}
                                Send message
                            </Button>
                        </div>
                        {!isLoggedIn && (
                            <div className="text-center text-sm text-muted-foreground">
                                <TextLink href={home()}>Back to home</TextLink>
                            </div>
                        )}
                    </>
                )}
            </Form>
        </>
    );
}

export default function ContactCreate() {
    const { auth, flash } = usePage<SharedData>().props;
    const isLoggedIn = Boolean(auth?.user);
    const flashStatus = flash?.status;

    if (isLoggedIn) {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Dashboard', href: dashboard().url },
            { title: 'Contact', href: contactCreate().url },
        ];
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Contact" />
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                    <h1 className="text-2xl font-semibold">Contact support</h1>
                    <p className="text-muted-foreground">
                        Send us a message and we&apos;ll get back to you.
                    </p>
                    <ContactFormContent
                        isLoggedIn
                        flashStatus={flashStatus}
                    />
                </div>
            </AppLayout>
        );
    }

    return (
        <AuthLayout
            title="Contact us"
            description="Send us a message and we'll get back to you."
        >
            <Head title="Contact" />
            <ContactFormContent
                isLoggedIn={false}
                flashStatus={flashStatus}
            />
        </AuthLayout>
    );
}
