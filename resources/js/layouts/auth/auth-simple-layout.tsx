import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Check } from 'lucide-react';
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
            <div className="hidden flex-col items-center justify-center gap-8 bg-muted/50 p-10 md:flex md:w-1/2">
                <div className="w-full max-w-sm space-y-6">
                    <Link
                        href={home()}
                        className="flex items-center gap-3 font-semibold"
                    >
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-background ring-1 ring-border">
                            <AppLogoIcon className="size-6 fill-current text-foreground" />
                        </div>
                        <span className="text-lg">{appName}</span>
                    </Link>

                    <p className="text-sm text-muted-foreground">
                        The modern SaaS starter kit — everything you need to
                        ship a production-ready product.
                    </p>

                    <ul className="space-y-2">
                        {brandingFeatures.map((feature) => (
                            <li
                                key={feature}
                                className="flex items-center gap-2 text-sm"
                            >
                                <Check className="size-4 shrink-0 text-primary" />
                                {feature}
                            </li>
                        ))}
                    </ul>
                </div>
            </div>

            {/* Right form panel */}
            <div className="flex w-full flex-col items-center justify-center gap-6 p-6 md:w-1/2 md:p-10">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={home()}
                                className="flex flex-col items-center gap-2 font-medium md:hidden"
                            >
                                <div className="mb-1 flex h-10 w-10 items-center justify-center rounded-xl bg-muted ring-1 ring-border">
                                    <AppLogoIcon className="size-6 fill-current text-foreground dark:text-white" />
                                </div>
                                <span className="sr-only">{title}</span>
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="text-2xl font-semibold tracking-tight">
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
