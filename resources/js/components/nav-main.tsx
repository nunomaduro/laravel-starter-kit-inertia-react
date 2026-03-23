import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useMemo } from 'react';

interface NavGroup {
    label: string;
    icon: LucideIcon;
    items: NavItem[];
    hasActiveItem: boolean;
}

function isItemActive(href: NavItem['href'], pageUrl: string): boolean {
    const path = typeof href === 'string' ? href : href.url;
    return (
        pageUrl === path ||
        pageUrl.startsWith(path + '/') ||
        pageUrl.startsWith(path + '?')
    );
}

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const page = usePage();
    const { state, isMobile } = useSidebar();
    const isCollapsed = !isMobile && state === 'collapsed';

    const groups = useMemo(() => {
        const map = new Map<string, NavItem[]>();
        for (const item of items) {
            const group = item.group ?? 'Platform';
            if (!map.has(group)) map.set(group, []);
            map.get(group)!.push(item);
        }

        const result: NavGroup[] = [];
        for (const [label, groupItems] of map) {
            const icon = groupItems[0]?.icon;
            const hasActiveItem = groupItems.some((item) =>
                isItemActive(item.href, page.url),
            );
            if (icon) {
                result.push({ label, icon, items: groupItems, hasActiveItem });
            }
        }
        return result;
    }, [items, page.url]);

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarMenu>
                {groups.map((group) =>
                    isCollapsed ? (
                        <CollapsedGroup
                            key={group.label}
                            group={group}
                            pageUrl={page.url}
                        />
                    ) : (
                        <ExpandedGroup
                            key={group.label}
                            group={group}
                            pageUrl={page.url}
                        />
                    ),
                )}
            </SidebarMenu>
        </SidebarGroup>
    );
}

function ExpandedGroup({
    group,
    pageUrl,
}: {
    group: NavGroup;
    pageUrl: string;
}) {
    return (
        <Collapsible
            asChild
            defaultOpen={group.hasActiveItem}
            className="group/collapsible"
        >
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton tooltip={{ children: group.label }}>
                        <group.icon />
                        <span>{group.label}</span>
                        <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {group.items.map((item) => (
                            <SidebarMenuSubItem key={item.title}>
                                <SidebarMenuSubButton
                                    asChild
                                    isActive={isItemActive(
                                        item.href,
                                        pageUrl,
                                    )}
                                >
                                    <Link
                                        href={item.href}
                                        prefetch="click"
                                        {...(item.dataPan
                                            ? {
                                                  'data-pan': item.dataPan,
                                              }
                                            : {})}
                                    >
                                        <span>{item.title}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}

function CollapsedGroup({
    group,
    pageUrl,
}: {
    group: NavGroup;
    pageUrl: string;
}) {
    return (
        <DropdownMenu>
            <SidebarMenuItem>
                <DropdownMenuTrigger asChild>
                    <SidebarMenuButton
                        tooltip={{ children: group.label }}
                        isActive={group.hasActiveItem}
                    >
                        <group.icon />
                        <span>{group.label}</span>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    side="right"
                    align="start"
                    sideOffset={4}
                    className="min-w-[180px]"
                >
                    <DropdownMenuLabel>{group.label}</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {group.items.map((item) => {
                        const active = isItemActive(item.href, pageUrl);
                        const href =
                            typeof item.href === 'string'
                                ? item.href
                                : item.href.url;
                        return (
                            <DropdownMenuItem
                                key={item.title}
                                asChild
                                className={
                                    active
                                        ? 'bg-accent text-accent-foreground'
                                        : ''
                                }
                            >
                                <Link
                                    href={href}
                                    prefetch="click"
                                    {...(item.dataPan
                                        ? { 'data-pan': item.dataPan }
                                        : {})}
                                >
                                    {item.icon && (
                                        <item.icon className="mr-2 size-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </DropdownMenuItem>
                        );
                    })}
                </DropdownMenuContent>
            </SidebarMenuItem>
        </DropdownMenu>
    );
}
