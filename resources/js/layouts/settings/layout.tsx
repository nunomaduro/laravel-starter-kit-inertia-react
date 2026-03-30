import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { onboarding } from '@/routes';
import { show as showAchievements } from '@/routes/achievements';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editPassword } from '@/routes/password';
import { edit as editPersonalDataExport } from '@/routes/personal-data-export';
import { auditLog } from '@/routes/settings';
import { edit as editBranding } from '@/routes/settings/branding';
import { show as showFeatures } from '@/routes/settings/features';
import { show as showGeneral } from '@/routes/settings/general';
import { show as showNotifications } from '@/routes/settings/notifications';
import { index as indexRoles } from '@/routes/settings/roles';
import { index as indexEmailTemplates } from '@/routes/settings/email-templates';
import { index as indexWebhooks } from '@/routes/settings/webhooks';
import { show } from '@/routes/two-factor';
import { edit } from '@/routes/user-profile';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Bell,
    ClipboardList,
    Download,
    ExternalLink,
    Globe,
    Key,
    Palette,
    RotateCcw,
    Shield,
    ShieldCheck,
    Sparkles,
    ToggleLeft,
    UserCircle,
    Users,
    Mail,
    Webhook,
} from 'lucide-react';
import { type PropsWithChildren, useMemo } from 'react';

const sidebarNavItems: (NavItem & {
    feature?: string;
    dataPan: string;
    requiresOrgAdmin?: boolean;
})[] = [
    {
        title: 'Profile',
        href: edit(),
        icon: UserCircle,
        dataPan: 'settings-nav-profile',
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: Key,
        dataPan: 'settings-nav-password',
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: Shield,
        feature: 'two_factor_auth',
        dataPan: 'settings-nav-two-factor',
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: Palette,
        feature: 'appearance_settings',
        dataPan: 'settings-nav-appearance',
    },
    {
        title: 'Organization branding',
        href: editBranding(),
        icon: Palette,
        dataPan: 'settings-nav-branding',
        requiresOrgAdmin: true,
    },
    {
        title: 'Workspace URL & Domains',
        href: showGeneral(),
        icon: Globe,
        dataPan: 'settings-nav-domains',
        requiresOrgAdmin: true,
    },
    {
        title: 'Feature settings',
        href: showFeatures(),
        icon: ToggleLeft,
        dataPan: 'settings-nav-features',
        requiresOrgAdmin: true,
    },
    {
        title: 'Custom roles',
        href: indexRoles(),
        icon: Users,
        dataPan: 'settings-nav-roles',
        requiresOrgAdmin: true,
    },
    {
        title: 'Audit log',
        href: auditLog(),
        icon: ClipboardList,
        dataPan: 'settings-nav-audit-log',
        requiresOrgAdmin: true,
    },
    {
        title: 'Webhooks',
        href: indexWebhooks(),
        icon: Webhook,
        dataPan: 'settings-nav-webhooks',
        requiresOrgAdmin: true,
    },
    {
        title: 'Email Templates',
        href: indexEmailTemplates(),
        icon: Mail,
        dataPan: 'settings-nav-email-templates',
        requiresOrgAdmin: true,
    },
    {
        title: 'Notifications',
        href: showNotifications(),
        icon: Bell,
        dataPan: 'settings-nav-notifications',
    },
    {
        title: 'Data export',
        href: editPersonalDataExport(),
        icon: Download,
        feature: 'personal_data_export',
        dataPan: 'settings-nav-data-export',
    },
    {
        title: 'Level & achievements',
        href: showAchievements(),
        icon: Sparkles,
        feature: 'gamification',
        dataPan: 'settings-nav-achievements',
    },
    {
        title: 'Onboarding',
        href: onboarding(),
        icon: RotateCcw,
        feature: 'onboarding',
        dataPan: 'settings-nav-onboarding',
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { features, auth } = usePage<SharedData>().props;
    const currentUrl = usePage<SharedData>().url.split('?')[0];

    const isSuperAdmin = auth.roles?.includes('super-admin') ?? false;
    const isOrgAdmin =
        auth.current_organization != null &&
        (auth.can_bypass || (auth.roles?.includes('admin') ?? false));
    const adminPanelHref = isSuperAdmin ? '/system' : '/admin';
    const adminPanelLabel = isSuperAdmin ? 'System Panel' : 'Admin Panel';

    const visibleNavItems = useMemo(() => {
        if (isSuperAdmin) {
            return sidebarNavItems;
        }
        const f = features ?? {};
        return sidebarNavItems.filter((item) => {
            if (item.requiresOrgAdmin && !isOrgAdmin) {
                return false;
            }
            if (!item.feature) {
                return true;
            }
            const value = f[item.feature];
            // Fail closed: only show when feature is explicitly active
            return value === true;
        });
    }, [features, isOrgAdmin, isSuperAdmin]);

    return (
        <div className="px-4 py-6">
            <Heading
                title="Settings"
                description="Manage your profile and account settings"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full lg:w-48">
                    <nav className="flex gap-1 overflow-x-auto pb-1 lg:flex-col lg:space-y-0.5 lg:overflow-x-visible lg:pb-0">
                        {visibleNavItems.map((item) => {
                            const href =
                                typeof item.href === 'string'
                                    ? item.href
                                    : item.href.url;
                            const isActive = currentUrl === href;
                            return (
                                <Button
                                    key={href}
                                    size="sm"
                                    variant="ghost"
                                    asChild
                                    data-pan={item.dataPan}
                                    className={cn(
                                        'shrink-0 justify-start gap-2 lg:w-full',
                                        isActive &&
                                            'bg-muted font-medium text-foreground',
                                    )}
                                >
                                    <Link href={item.href}>
                                        {item.icon && (
                                            <item.icon className="size-4 shrink-0 text-muted-foreground" />
                                        )}
                                        {item.title}
                                    </Link>
                                </Button>
                            );
                        })}
                        {(isSuperAdmin || isOrgAdmin) && (
                            <Button
                                size="sm"
                                variant="ghost"
                                asChild
                                data-pan="settings-nav-admin-panel"
                                className="mt-2 shrink-0 justify-start gap-2 border-t pt-2 text-muted-foreground lg:mt-2 lg:w-full lg:pt-2"
                            >
                                <a href={adminPanelHref}>
                                    <ShieldCheck className="size-4 shrink-0" />
                                    {adminPanelLabel}
                                    <ExternalLink className="ml-auto size-3 shrink-0 opacity-50" />
                                </a>
                            </Button>
                        )}
                    </nav>
                </aside>

                <Separator className="my-4 lg:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
