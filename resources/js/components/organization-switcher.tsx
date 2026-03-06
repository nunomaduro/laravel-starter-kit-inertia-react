import organizations from '@/routes/organizations';
import { type SharedData } from '@/types';
import { Form, Link, usePage } from '@inertiajs/react';
import { Building2, Check, ChevronsUpDown, Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export function OrganizationSwitcher() {
    const { auth } = usePage<SharedData>().props;
    const isSuperAdmin = auth.roles?.includes('super-admin') ?? false;
    const userOrganizations = auth.organizations ?? [];
    const current = auth.current_organization;

    if (isSuperAdmin) {
        return null;
    }

    if (userOrganizations.length === 0) {
        return (
            <Link
                href={organizations.create.url()}
                className="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground hover:bg-accent hover:text-accent-foreground"
            >
                <Plus className="size-4 shrink-0" />
                <span className="truncate">Create organization</span>
            </Link>
        );
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="sm"
                    className="flex w-full items-center justify-between gap-2 px-2"
                >
                    <span className="flex items-center gap-2 truncate">
                        <Building2 className="size-4 shrink-0" />
                        <span className="truncate">
                            {current?.name ?? 'Select organization'}
                        </span>
                    </span>
                    <ChevronsUpDown className="size-4 shrink-0 text-muted-foreground" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                align="start"
                className="w-[--radix-dropdown-menu-trigger-width]"
            >
                {userOrganizations.map((org) => (
                    <DropdownMenuItem key={org.id} asChild>
                        <Form
                            action={organizations.switch.url()}
                            method="post"
                            className="w-full"
                        >
                            <input
                                type="hidden"
                                name="organization_id"
                                value={org.id}
                            />
                            <button
                                type="submit"
                                className="flex w-full cursor-pointer items-center gap-2 px-2 py-1.5 text-left text-sm outline-none hover:bg-accent hover:text-accent-foreground"
                            >
                                {current?.id === org.id ? (
                                    <Check className="size-4 shrink-0" />
                                ) : (
                                    <span className="size-4 shrink-0" />
                                )}
                                <span className="truncate">{org.name}</span>
                            </button>
                        </Form>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
