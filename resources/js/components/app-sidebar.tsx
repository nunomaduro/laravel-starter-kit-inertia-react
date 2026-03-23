import PageController from '@/actions/Modules/PageBuilder/Http/Controllers/PageController';
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
    Bell,
    BookOpen,
    Building2,
    CreditCard,
    ExternalLink,
    FileText,
    Folder,
    FolderTree,
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
    // ── Platform ──
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
        group: 'Platform',
        dataPan: 'nav-dashboard',
    },
    {
        title: 'Chat',
        href: chat().url,
        icon: MessageCircle,
        group: 'Platform',
        dataPan: 'nav-chat',
    },
    // ── Organization ──
    {
        title: 'Users',
        href: '/users',
        icon: Users,
        group: 'Organization',
        permission: ['view users', 'org.members.view'],
        dataPan: 'nav-users',
    },
    {
        title: 'Organizations',
        href: organizations.index.url(),
        icon: Building2,
        group: 'Organization',
        tenancyRequired: true,
        dataPan: 'nav-organizations',
    },
    {
        title: 'Organizations (table)',
        href: '/organizations/list',
        icon: Building2,
        group: 'Organization',
        tenancyRequired: true,
        dataPan: 'nav-organizations-table',
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: FolderTree,
        group: 'Organization',
        tenancyRequired: true,
        dataPan: 'nav-categories',
    },
    {
        title: 'Announcements',
        href: '/announcements',
        icon: Bell,
        group: 'Organization',
        permission: ['announcements.manage_global', 'announcements.manage'],
        dataPan: 'nav-announcements',
    },
    // ── Content ──
    {
        title: 'Pages',
        href: PageController.index().url,
        icon: FileText,
        group: 'Content',
        permission: 'org.pages.manage',
        tenancyRequired: true,
        dataPan: 'nav-pages',
    },
    {
        title: 'Blog',
        href: blogIndex().url,
        icon: FileText,
        group: 'Content',
        permission: 'blog.index',
        feature: 'blog',
        dataPan: 'nav-blog',
    },
    {
        title: 'Posts',
        href: '/posts',
        icon: FileText,
        group: 'Content',
        permission: 'blog.index',
        feature: 'blog',
        dataPan: 'nav-posts',
    },
    {
        title: 'Changelog',
        href: changelogIndex().url,
        icon: Megaphone,
        group: 'Content',
        permission: 'changelog.index',
        feature: 'changelog',
        dataPan: 'nav-changelog',
    },
    // ── Billing ──
    {
        title: 'Billing',
        href: '/billing',
        icon: CreditCard,
        group: 'Billing',
        dataPan: 'nav-billing',
    },
    // ── Support ──
    {
        title: 'Help',
        href: helpIndex().url,
        icon: LifeBuoy,
        group: 'Support',
        permission: 'help.index',
        feature: 'help',
        dataPan: 'nav-help',
    },
    {
        title: 'Contact',
        href: contactCreate().url,
        icon: Mail,
        group: 'Support',
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
    // Super-admin sees every nav entry regardless of feature flags, tenancy, or permissions
    if (isSuperAdmin) {
        return true;
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
        <Sidebar collapsible="icon" variant="sidebar">
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
                            title={adminPanelLabel}
                            className="flex min-h-11 cursor-pointer items-center gap-2 overflow-hidden rounded-md px-2 py-1.5 text-sm text-muted-foreground transition-colors duration-150 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0"
                        >
                            <ShieldCheck className="size-4 shrink-0" />
                            <span className="truncate group-data-[collapsible=icon]:hidden">{adminPanelLabel}</span>
                            <ExternalLink className="ml-auto size-3 shrink-0 opacity-50 group-data-[collapsible=icon]:hidden" />
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
