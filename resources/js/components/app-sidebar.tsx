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
import { dashboard } from '@/routes';
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
    FileText,
    Folder,
    LayoutGrid,
    LifeBuoy,
    Mail,
    Megaphone,
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
        title: 'Organizations',
        href: organizations.index.url(),
        icon: Building2,
        dataPan: 'nav-organizations',
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
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
        dataPan: 'nav-documentation',
    },
];

/** Hide item when it has a feature key and that feature is inactive (shared from server). */
function canShowNavItem(
    item: NavItem,
    permissions: string[],
    canBypass: boolean,
    features: SharedData['features'],
): boolean {
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
    const visibleMainNavItems = useMemo(
        () =>
            mainNavItems.filter((item) =>
                canShowNavItem(
                    item,
                    auth.permissions ?? [],
                    auth.can_bypass ?? false,
                    features ?? {},
                ),
            ),
        [auth.permissions, auth.can_bypass, features],
    );

    const visibleFooterNavItems = useMemo(() => {
        const f = features ?? {};
        return footerNavItems.filter(
            (item) =>
                !item.feature || Boolean(f[item.feature as keyof typeof f]),
        );
    }, [features]);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard().url} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem>
                        <OrganizationSwitcher />
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={visibleMainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={visibleFooterNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
