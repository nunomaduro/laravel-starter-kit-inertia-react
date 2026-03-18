import ContactSubmissionController from '@/actions/Modules/Contact/Http/Controllers/ContactSubmissionController';
import { dashboard, home } from '@/routes';
import { create as contactCreate } from '@/routes/contact';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import HoneypotFields from '@/components/honeypot-fields';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
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
                            <FormField
                                label="Name"
                                htmlFor="name"
                                error={errors.name}
                            >
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
                            </FormField>
                            <FormField
                                label="Email"
                                htmlFor="email"
                                error={errors.email}
                            >
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autoComplete="email"
                                    name="email"
                                    placeholder="you@example.com"
                                    defaultValue={user?.email}
                                />
                            </FormField>
                            <FormField
                                label="Subject"
                                htmlFor="subject"
                                error={errors.subject}
                            >
                                <Input
                                    id="subject"
                                    type="text"
                                    required
                                    name="subject"
                                    placeholder="Subject"
                                />
                            </FormField>
                            <FormField
                                label="Message"
                                htmlFor="message"
                                error={errors.message}
                            >
                                <Textarea
                                    id="message"
                                    name="message"
                                    required
                                    rows={5}
                                    className="min-h-[120px]"
                                    placeholder="Your message..."
                                />
                            </FormField>
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
    const flashStatus = (flash as { status?: string } | null | undefined)
        ?.status;

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
                    <ContactFormContent isLoggedIn flashStatus={flashStatus} />
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
            <ContactFormContent isLoggedIn={false} flashStatus={flashStatus} />
        </AuthLayout>
    );
}
