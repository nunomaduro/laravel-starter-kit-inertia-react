import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    Calendar,
    CalendarCheck,
    DollarSign,
    LayoutGrid,
    MessageSquare,
    Search,
    Star,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const guestNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'Search Properties', href: '/search', icon: Search },
    { title: 'My Bookings', href: '/bookings', icon: CalendarCheck },
    { title: 'Messages', href: '/messages', icon: MessageSquare },
];

const hostNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'My Bookings', href: '/bookings', icon: CalendarCheck },
    { title: 'Messages', href: '/messages', icon: MessageSquare },
];

const hostPanelItems: NavItem[] = [
    { title: 'Host Dashboard', href: '/host/dashboard', icon: Building2 },
    { title: 'Properties', href: '/host/properties', icon: Building2 },
    { title: 'Host Bookings', href: '/host/bookings', icon: CalendarCheck },
    { title: 'Calendar', href: '/host/calendar', icon: Calendar },
    { title: 'Earnings', href: '/host/earnings', icon: DollarSign },
    { title: 'Host Messages', href: '/host/messages', icon: MessageSquare },
];

const adminPanelItems: NavItem[] = [
    { title: 'Users', href: '/admin/users', icon: Users },
    { title: 'Listings', href: '/admin/listings', icon: Building2 },
    { title: 'Reviews', href: '/admin/reviews', icon: Star },
];

export function AppSidebar() {
    const { auth } = usePage().props as { auth: { user: { role?: string } } };
    const role = auth.user?.role;

    const mainItems = role === 'host' ? hostNavItems : guestNavItems;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainItems} label="Main" />
                {role === 'host' && (
                    <NavMain items={hostPanelItems} label="Host Panel" />
                )}
                {role === 'admin' && (
                    <>
                        <NavMain items={hostPanelItems} label="Host Panel" />
                        <NavMain items={adminPanelItems} label="Admin" />
                    </>
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
