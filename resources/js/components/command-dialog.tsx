'use client';

import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { dashboard, logout } from '@/routes';
import { index as blogIndex } from '@/routes/blog';
import { index as changelogIndex } from '@/routes/changelog';
import { create as contactCreate } from '@/routes/contact';
import { index as helpIndex } from '@/routes/help';
import organizations from '@/routes/organizations';
import { edit as editUserProfile } from '@/routes/user-profile';
import { type NavItem, type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { getHotkeyManager } from '@tanstack/hotkeys';
import {
    Building2,
    CreditCard,
    FileText,
    LayoutGrid,
    LifeBuoy,
    LogOut,
    Mail,
    Megaphone,
    Settings,
} from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard().url, icon: LayoutGrid },
    {
        title: 'Organizations',
        href: organizations.index.url(),
        icon: Building2,
        tenancyRequired: true,
    },
    { title: 'Billing', href: '/billing', icon: CreditCard },
    {
        title: 'Blog',
        href: blogIndex().url,
        icon: FileText,
        permission: 'blog.index',
        feature: 'blog',
    },
    {
        title: 'Changelog',
        href: changelogIndex().url,
        icon: Megaphone,
        permission: 'changelog.index',
        feature: 'changelog',
    },
    {
        title: 'Help',
        href: helpIndex().url,
        icon: LifeBuoy,
        permission: 'help.index',
        feature: 'help',
    },
    {
        title: 'Contact',
        href: contactCreate().url,
        icon: Mail,
        permission: 'contact.create',
        feature: 'contact',
    },
];

function canShowNavItem(
    item: NavItem,
    permissions: string[],
    canBypass: boolean,
    features: SharedData['features'],
    tenancyEnabled: boolean,
): boolean {
    if (item.tenancyRequired && !tenancyEnabled) return false;
    if (item.feature && !features?.[item.feature]) return false;
    if (canBypass || !item.permission) return true;
    const required = Array.isArray(item.permission)
        ? item.permission
        : [item.permission];
    return required.some((p) => permissions.includes(p));
}

export function CommandPalette(): React.ReactElement {
    const [open, setOpen] = useState(false);
    const { auth, features } = usePage<SharedData>().props;

    const visibleNavItems = useMemo(
        () =>
            mainNavItems.filter((item) =>
                canShowNavItem(
                    item,
                    auth.permissions ?? [],
                    auth.can_bypass ?? false,
                    features ?? {},
                    auth.tenancy_enabled ?? true,
                ),
            ),
        [auth.permissions, auth.can_bypass, auth.tenancy_enabled, features],
    );

    useEffect(() => {
        const manager = getHotkeyManager();
        const handle = manager.register('Mod+k', () => setOpen((o) => !o));
        return () => handle.unregister();
    }, []);

    const run = useCallback((href: string, isLogout = false) => {
        setOpen(false);
        if (isLogout) {
            router.post(href);
        } else {
            router.visit(href);
        }
    }, []);

    const logoutUrl = logout();

    return (
        <CommandDialog
            open={open}
            onOpenChange={setOpen}
            title="Command Palette"
            description="Navigate or run an action"
            data-pan="command-palette"
        >
            <CommandInput placeholder="Search..." />
            <CommandList>
                <CommandEmpty>No results found.</CommandEmpty>
                <CommandGroup heading="Navigation">
                    {visibleNavItems.map((item) => {
                        const Icon = item.icon;
                        const href =
                            typeof item.href === 'string'
                                ? item.href
                                : (item.href.url ?? item.href.url());
                        return (
                            <CommandItem
                                key={item.title}
                                value={item.title}
                                onSelect={() => run(href)}
                            >
                                {Icon && <Icon className="size-4" />}
                                {item.title}
                            </CommandItem>
                        );
                    })}
                </CommandGroup>
                <CommandGroup heading="Account">
                    <CommandItem
                        value="Settings"
                        onSelect={() => run(editUserProfile())}
                    >
                        <Settings className="size-4" />
                        Settings
                    </CommandItem>
                    <CommandItem
                        value="Log out"
                        onSelect={() => run(logoutUrl, true)}
                    >
                        <LogOut className="size-4" />
                        Log out
                    </CommandItem>
                </CommandGroup>
            </CommandList>
        </CommandDialog>
    );
}
