import AppLogoIcon from '@/components/app-logo-icon';
import TextLink from '@/components/text-link';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/react';

export default function LegalTerms() {
    return (
        <>
            <Head title="Terms of Service" />
            <div className="min-h-svh bg-background">
                <header className="sticky top-0 z-10 border-b bg-background/95 px-4 py-3 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="mx-auto flex max-w-3xl items-center justify-between">
                        <Link
                            href={home()}
                            className="flex items-center gap-2 font-medium text-foreground"
                        >
                            <AppLogoIcon className="size-8 fill-current" />
                            <span className="sr-only">Home</span>
                        </Link>
                        <TextLink href={home()}>Back to home</TextLink>
                    </div>
                </header>
                <main className="mx-auto max-w-3xl px-4 py-8">
                    <h1 className="mb-2 text-2xl font-mono font-semibold tracking-tight">
                        Terms of Service
                    </h1>
                    <p className="mb-6 text-sm text-muted-foreground">
                        Last updated:{' '}
                        {new Date().toLocaleDateString('en-CA', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                        })}
                    </p>
                    <div className="prose prose-neutral dark:prose-invert max-w-none">
                        <p>
                            Please read these terms of service carefully before
                            using this application.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">
                            1. Acceptance of terms
                        </h2>
                        <p>
                            By accessing or using this service, you agree to be
                            bound by these terms. If you do not agree, do not
                            use the service.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">
                            2. Use of the service
                        </h2>
                        <p>
                            You must use the service only for lawful purposes
                            and in accordance with these terms. You are
                            responsible for maintaining the confidentiality of
                            your account and for all activity under your
                            account.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">3. Changes</h2>
                        <p>
                            We may update these terms from time to time. We will
                            notify you of material changes by posting the
                            updated terms and updating the &quot;Last
                            updated&quot; date. Your continued use of the
                            service after changes constitutes acceptance of the
                            updated terms.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">4. Contact</h2>
                        <p>
                            If you have questions about these terms, please
                            contact us through the contact page.
                        </p>
                    </div>
                </main>
            </div>
        </>
    );
}
