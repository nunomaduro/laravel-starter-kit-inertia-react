import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

const brandingFeatures = [
    'Multi-tenant organizations & teams',
    'Subscription billing with Stripe',
    'Built-in AI chat & assistants',
];

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    const appName = usePage<SharedData>().props.name;

    return (
        <div className="flex min-h-svh">
            {/* Left branding panel — hidden on mobile */}
            <div className="hidden flex-col justify-between bg-card p-10 md:flex md:min-h-svh md:w-1/2">
                <div className="flex flex-col justify-center flex-1">
                    <div className="w-full max-w-sm mx-auto space-y-8">
                        <Link
                            href={home()}
                            className="flex items-center gap-3"
                        >
                            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary">
                                <AppLogoIcon className="size-5 fill-current text-primary-foreground" />
                            </div>
                            <span className="font-mono text-lg font-semibold tracking-tight">
                                {appName}
                            </span>
                        </Link>

                        <p className="text-sm leading-relaxed text-muted-foreground">
                            The modern SaaS starter kit — everything you need to
                            ship a production-ready product.
                        </p>

                        <ul className="space-y-3">
                            {brandingFeatures.map((feature) => (
                                <li
                                    key={feature}
                                    className="flex items-center gap-3 text-sm text-foreground/80"
                                >
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded border border-primary/30 bg-primary/10 font-mono text-[10px] font-medium text-primary">
                                        ✓
                                    </span>
                                    {feature}
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                <p className="text-xs font-mono text-muted-foreground/60">
                    // built for developers who ship
                </p>
            </div>

            {/* Right form panel */}
            <div className="flex w-full flex-col items-center justify-center gap-6 p-6 md:w-1/2 md:p-10">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={home()}
                                className="flex flex-col items-center gap-2 md:hidden"
                            >
                                <div className="mb-1 flex h-9 w-9 items-center justify-center rounded-lg bg-primary">
                                    <AppLogoIcon className="size-5 fill-current text-primary-foreground" />
                                </div>
                                <span className="sr-only">{title}</span>
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="font-mono text-xl font-semibold tracking-tight">
                                    {title}
                                </h1>
                                <p className="text-center text-sm text-muted-foreground">
                                    {description}
                                </p>
                            </div>
                        </div>
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
