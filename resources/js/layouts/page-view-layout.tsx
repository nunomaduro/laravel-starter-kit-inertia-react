import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface PageViewLayoutProps {
    children: ReactNode;
}

export default function PageViewLayout({ children }: PageViewLayoutProps) {
    const { name, branding } = usePage<SharedData>().props;
    const logoUrl = branding?.logoUrl ?? null;
    const siteName = name ?? 'Laravel Starter Kit';

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <header className="border-b">
                <div className="container flex h-14 items-center">
                    <div className="flex aspect-square size-8 shrink-0 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                        {logoUrl ? (
                            <img
                                src={logoUrl}
                                alt={siteName}
                                className="size-full object-contain"
                            />
                        ) : (
                            <span className="text-lg font-semibold text-white dark:text-black">
                                {siteName.slice(0, 1)}
                            </span>
                        )}
                    </div>
                    <span className="ml-2 font-mono font-semibold tracking-tight">{siteName}</span>
                </div>
            </header>
            <main className="flex-1">{children}</main>
        </div>
    );
}
