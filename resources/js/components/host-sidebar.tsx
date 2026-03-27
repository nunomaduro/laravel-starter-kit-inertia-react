import { Link } from "@inertiajs/react";
import {
    Building2,
    Calendar,
    CalendarCheck,
    DollarSign,
    LayoutGrid,
    MessageSquare,
} from "lucide-react";
import AppLogo from "@/components/app-logo";
import { NavFooter } from "@/components/nav-footer";
import { NavMain } from "@/components/nav-main";
import { NavUser } from "@/components/nav-user";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";
import type { NavItem } from "@/types";

const mainNavItems: NavItem[] = [
    {
        title: "Dashboard",
        href: "/host/dashboard",
        icon: LayoutGrid,
    },
    {
        title: "Properties",
        href: "/host/properties",
        icon: Building2,
    },
    {
        title: "Bookings",
        href: "/host/bookings",
        icon: CalendarCheck,
    },
    {
        title: "Calendar",
        href: "/host/calendar",
        icon: Calendar,
    },
    {
        title: "Earnings",
        href: "/host/earnings",
        icon: DollarSign,
    },
    {
        title: "Messages",
        href: "/host/messages",
        icon: MessageSquare,
    },
];

export function HostSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/host/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
