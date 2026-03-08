import { Icon } from '@/components/icon';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { type ComponentPropsWithoutRef } from 'react';

function isExternalHref(href: string): boolean {
    return href.startsWith('http://') || href.startsWith('https://');
}

export function NavFooter({
    items,
    className,
    ...props
}: ComponentPropsWithoutRef<typeof SidebarGroup> & {
    items: NavItem[];
}) {
    return (
        <SidebarGroup
            {...props}
            className={`group-data-[collapsible=icon]:p-0 ${className || ''}`}
        >
            <SidebarGroupContent>
                <SidebarMenu>
                    {items.map((item) => {
                        const url =
                            typeof item.href === 'string'
                                ? item.href
                                : item.href.url;
                        const external = isExternalHref(url);
                        return (
                            <SidebarMenuItem key={`${item.title}-${url}`}>
                                <SidebarMenuButton
                                    asChild
                                    className="text-sidebar-foreground/70 hover:text-sidebar-foreground"
                                >
                                    {external ? (
                                        <a
                                            href={url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            {...(item.dataPan
                                                ? { 'data-pan': item.dataPan }
                                                : {})}
                                        >
                                            {item.icon && (
                                                <Icon
                                                    iconNode={item.icon}
                                                    className="h-5 w-5"
                                                />
                                            )}
                                            <span>{item.title}</span>
                                        </a>
                                    ) : (
                                        <Link
                                            href={url}
                                            {...(item.dataPan
                                                ? { 'data-pan': item.dataPan }
                                                : {})}
                                        >
                                            {item.icon && (
                                                <Icon
                                                    iconNode={item.icon}
                                                    className="h-5 w-5"
                                                />
                                            )}
                                            <span>{item.title}</span>
                                        </Link>
                                    )}
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        );
                    })}
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    );
}
