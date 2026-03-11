import PageController from '@/actions/App/Http/Controllers/PageController';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { OrganizationSwitcher } from '@/components/organization-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { chat, dashboard } from '@/routes';
import { index as blogIndex } from '@/routes/blog';
import { index as changelogIndex } from '@/routes/changelog';
import { create as contactCreate } from '@/routes/contact';
import { index as helpIndex } from '@/routes/help';
import organizations from '@/routes/organizations';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Building2,
    CreditCard,
    ExternalLink,
    FileText,
    Folder,
    LayoutGrid,
    LifeBuoy,
    Mail,
    Megaphone,
    MessageCircle,
    ShieldCheck,
    Users,
} from 'lucide-react';
import { useMemo } from 'react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
        dataPan: 'nav-dashboard',
    },
    {
        title: 'Chat',
        href: chat().url,
        icon: MessageCircle,
        dataPan: 'nav-chat',
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
        permission: ['view users', 'org.members.view'],
        dataPan: 'nav-users',
    },
    {
        title: 'Organizations',
        href: organizations.index.url(),
        icon: Building2,
        tenancyRequired: true,
        dataPan: 'nav-organizations',
    },
    {
        title: 'Pages',
        href: PageController.index().url,
        icon: FileText,
        permission: 'org.pages.manage',
        tenancyRequired: true,
        dataPan: 'nav-pages',
    },
    {
        title: 'Billing',
        href: '/billing',
        icon: CreditCard,
        dataPan: 'nav-billing',
    },
    {
        title: 'Blog',
        href: blogIndex().url,
        icon: FileText,
        permission: 'blog.index',
        feature: 'blog',
        dataPan: 'nav-blog',
    },
    {
        title: 'Changelog',
        href: changelogIndex().url,
        icon: Megaphone,
        permission: 'changelog.index',
        feature: 'changelog',
        dataPan: 'nav-changelog',
    },
    {
        title: 'Help',
        href: helpIndex().url,
        icon: LifeBuoy,
        permission: 'help.index',
        feature: 'help',
        dataPan: 'nav-help',
    },
    {
        title: 'Contact',
        href: contactCreate().url,
        icon: Mail,
        permission: 'contact.create',
        feature: 'contact',
        dataPan: 'nav-contact',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'API docs',
        href: '/docs/api',
        icon: BookOpen,
        feature: 'scramble_api_docs',
        dataPan: 'nav-api-docs',
    },
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
        dataPan: 'nav-repository',
        superAdminOnly: true,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
        dataPan: 'nav-documentation',
        superAdminOnly: true,
    },
];

/** Hide item when it has a feature key and that feature is inactive (shared from server). */
function canShowNavItem(
    item: NavItem,
    permissions: string[],
    canBypass: boolean,
    features: SharedData['features'],
    tenancyEnabled: boolean,
    isSuperAdmin: boolean,
): boolean {
    if (item.superAdminOnly && !isSuperAdmin) {
        return false;
    }
    if (item.tenancyRequired && !tenancyEnabled) {
        return false;
    }
    if (item.feature && !features?.[item.feature]) {
        return false;
    }
    if (canBypass || !item.permission) {
        return true;
    }
    const required = Array.isArray(item.permission)
        ? item.permission
        : [item.permission];
    return required.some((p) => permissions.includes(p));
}

export function AppSidebar() {
    const { auth, features } = usePage<SharedData>().props;
    const permissions = auth.permissions ?? [];
    const canBypass = auth.can_bypass ?? false;
    const resolvedFeatures = features ?? {};
    const tenancyEnabled = auth.tenancy_enabled ?? true;
    const isSuperAdmin = auth.roles?.includes('super-admin') ?? false;

    const visibleMainNavItems = useMemo(
        () =>
            mainNavItems.filter((item) =>
                canShowNavItem(
                    item,
                    permissions,
                    canBypass,
                    resolvedFeatures,
                    tenancyEnabled,
                    isSuperAdmin,
                ),
            ),
        [
            permissions,
            canBypass,
            resolvedFeatures,
            tenancyEnabled,
            isSuperAdmin,
        ],
    );
    const visibleFooterNavItems = useMemo(
        () =>
            footerNavItems.filter((item) =>
                canShowNavItem(
                    item,
                    permissions,
                    canBypass,
                    resolvedFeatures,
                    tenancyEnabled,
                    isSuperAdmin,
                ),
            ),
        [
            permissions,
            canBypass,
            resolvedFeatures,
            tenancyEnabled,
            isSuperAdmin,
        ],
    );

    const adminPanelHref = isSuperAdmin ? '/system' : '/admin';
    const adminPanelLabel = isSuperAdmin ? 'System Panel' : 'Admin Panel';
    const canSeeAdminPanel =
        isSuperAdmin || permissions.includes('access admin panel');

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard().url} prefetch="click">
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    {tenancyEnabled && (
                        <SidebarMenuItem>
                            <OrganizationSwitcher />
                        </SidebarMenuItem>
                    )}
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={visibleMainNavItems} />
                {canSeeAdminPanel && (
                    <div className="px-2 pb-2">
                        <a
                            href={adminPanelHref}
                            data-pan="nav-admin-panel"
                            className="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        >
                            <ShieldCheck className="size-4 shrink-0" />
                            <span>{adminPanelLabel}</span>
                            <ExternalLink className="ml-auto size-3 shrink-0 opacity-50" />
                        </a>
                    </div>
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={visibleFooterNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
