import AppLogoIcon from '@/components/app-logo-icon';
import TextLink from '@/components/text-link';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/react';

export default function LegalPrivacy() {
    return (
        <>
            <Head title="Privacy Policy" />
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
                        Privacy Policy
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
                            This privacy policy describes how we collect, use,
                            and protect your information when you use this
                            application.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">
                            1. Information we collect
                        </h2>
                        <p>
                            We collect information you provide when registering
                            (such as name and email), usage data, and technical
                            data (e.g. IP address, browser type) necessary to
                            operate the service.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">
                            2. How we use your information
                        </h2>
                        <p>
                            We use your information to provide and improve the
                            service, to communicate with you, and to comply with
                            legal obligations. We do not sell your personal
                            data.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">
                            3. Data retention and your rights
                        </h2>
                        <p>
                            We retain your data for as long as your account is
                            active or as needed for legal purposes. You may
                            request access, correction, export, or deletion of
                            your data through your account settings or by
                            contacting us.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">
                            4. Cookies and similar technologies
                        </h2>
                        <p>
                            We use cookies and similar technologies for
                            authentication, preferences, and analytics. You can
                            manage cookie preferences in your browser or through
                            our cookie consent when applicable.
                        </p>
                        <h2 className="mt-6 font-mono text-lg font-medium tracking-tight">5. Contact</h2>
                        <p>
                            For privacy-related questions or to exercise your
                            rights, please contact us through the contact page.
                        </p>
                    </div>
                </main>
            </div>
        </>
    );
}
