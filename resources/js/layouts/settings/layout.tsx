import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { onboarding } from '@/routes';
import { show as showAchievements } from '@/routes/achievements';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editPassword } from '@/routes/password';
import { edit as editPersonalDataExport } from '@/routes/personal-data-export';
import { show } from '@/routes/two-factor';
import { edit } from '@/routes/user-profile';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren, useMemo } from 'react';

const sidebarNavItems: (NavItem & { feature?: string; dataPan: string })[] = [
    {
        title: 'Profile',
        href: edit(),
        icon: null,
        dataPan: 'settings-nav-profile',
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: null,
        dataPan: 'settings-nav-password',
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: null,
        feature: 'two_factor_auth',
        dataPan: 'settings-nav-two-factor',
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: null,
        feature: 'appearance_settings',
        dataPan: 'settings-nav-appearance',
    },
    {
        title: 'Data export',
        href: editPersonalDataExport(),
        icon: null,
        feature: 'personal_data_export',
        dataPan: 'settings-nav-data-export',
    },
    {
        title: 'Level & achievements',
        href: showAchievements(),
        icon: null,
        feature: 'gamification',
        dataPan: 'settings-nav-achievements',
    },
    {
        title: 'Onboarding',
        href: onboarding(),
        icon: null,
        feature: 'onboarding',
        dataPan: 'settings-nav-onboarding',
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { features } = usePage<SharedData>().props;

    const visibleNavItems = useMemo(() => {
        const f = features ?? {};
        return sidebarNavItems.filter((item) => {
            if (!item.feature) return true;
            const value = f[item.feature];
            // Fail closed: only show when feature is explicitly active (true or 1)
            return value === true || value === 1;
        });
    }, [features]);

    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;

    return (
        <div className="px-4 py-6">
            <Heading
                title="Settings"
                description="Manage your profile and account settings"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                        {visibleNavItems.map((item, index) => (
                            <Button
                                key={`${typeof item.href === 'string' ? item.href : item.href.url}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                data-pan={item.dataPan}
                                className={cn('w-full justify-start', {
                                    'bg-muted':
                                        currentPath ===
                                        (typeof item.href === 'string'
                                            ? item.href
                                            : item.href.url),
                                })}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
