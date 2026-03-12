import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    const { name, branding } = usePage<SharedData>().props;
    const logoUrl = branding?.logoUrl ?? null;
    const logoUrlDark = branding?.logoUrlDark ?? null;
    const siteName = name ?? 'Laravel Starter Kit';

    return (
        <>
            <div className="flex aspect-square size-8 shrink-0 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                {logoUrlDark && logoUrl ? (
                    <>
                        <img
                            src={logoUrl}
                            alt={siteName}
                            className="size-full object-contain dark:hidden"
                        />
                        <img
                            src={logoUrlDark}
                            alt={siteName}
                            className="hidden size-full object-contain dark:block"
                        />
                    </>
                ) : logoUrl ? (
                    <img
                        src={logoUrl}
                        alt={siteName}
                        className="size-full object-contain"
                    />
                ) : (
                    <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                )}
            </div>
            <div className="ml-1 grid min-w-0 flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    {siteName}
                </span>
            </div>
        </>
    );
}
