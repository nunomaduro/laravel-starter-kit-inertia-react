import { dashboard, login, register } from '@/routes';
import { index as blogIndex } from '@/routes/blog';
import { index as changelogIndex } from '@/routes/changelog';
import { create as contactCreate } from '@/routes/contact';
import { index as helpIndex } from '@/routes/help';
import { Link } from '@inertiajs/react';

interface WelcomeHeaderProps {
    name: string;
    isAuthenticated: boolean;
    flags: Record<string, boolean | undefined>;
}

export function WelcomeHeader({ name, isAuthenticated, flags }: WelcomeHeaderProps) {
    return (
        <header className="border-b border-border">
            <div className="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-4">
                <span className="font-mono text-sm font-semibold tracking-tight">{name}</span>
                <nav className="flex items-center gap-1">
                    {isAuthenticated ? (
                        <Link
                            href={dashboard()}
                            data-pan="welcome-dashboard"
                            className="rounded-md border border-border px-4 py-1.5 text-sm font-medium transition-colors duration-100 hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link
                                href="/wizard"
                                data-pan="welcome-wizard"
                                className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                Wizard
                            </Link>
                            {flags.blog && (
                                <Link href={blogIndex().url} data-pan="welcome-blog" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                    Blog
                                </Link>
                            )}
                            {flags.changelog && (
                                <Link href={changelogIndex().url} data-pan="welcome-changelog" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                    Changelog
                                </Link>
                            )}
                            {flags.help && (
                                <Link href={helpIndex().url} data-pan="welcome-help" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                    Help
                                </Link>
                            )}
                            {flags.contact && (
                                <Link href={contactCreate().url} data-pan="welcome-contact" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                    Contact
                                </Link>
                            )}
                            <Link href={login()} data-pan="welcome-log-in" className="rounded-md px-4 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                Log in
                            </Link>
                            {flags.registration && (
                                <Link href={register()} data-pan="welcome-register" className="rounded-md border border-border bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors duration-100 hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                    Get started
                                </Link>
                            )}
                        </>
                    )}
                </nav>
            </div>
        </header>
    );
}
