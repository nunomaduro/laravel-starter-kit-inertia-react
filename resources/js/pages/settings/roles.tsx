import { Deferred, Head, router, useForm, usePage } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface CustomRole {
    id: number;
    name: string;
    label: string;
    permissions: string[];
}

interface RoleTemplate {
    name: string;
    label: string;
    visible_to_orgs: boolean;
    plan_required: string | null;
    permissions: string[];
}

interface PageProps extends SharedData {
    customRoles: CustomRole[];
    roleTemplates: RoleTemplate[];
    grantablePermissions: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Custom Roles', href: '/settings/roles' },
];

const PERMISSION_LABELS: Record<string, string> = {
    'org.members.view': 'View members',
    'org.members.invite': 'Invite members',
    'org.billing.view': 'View billing',
    'org.pages.manage': 'Manage pages',
};

function PermissionBadge({ permission }: { permission: string }) {
    return (
        <span className="inline-flex items-center rounded-full bg-secondary px-2 py-0.5 text-xs font-medium text-secondary-foreground">
            {PERMISSION_LABELS[permission] ?? permission}
        </span>
    );
}

function CreateRoleForm({
    grantablePermissions,
}: {
    grantablePermissions: string[];
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        label: '',
        permissions: [] as string[],
    });

    const togglePermission = (perm: string) => {
        setData(
            'permissions',
            data.permissions.includes(perm)
                ? data.permissions.filter((p) => p !== perm)
                : [...data.permissions, perm],
        );
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/settings/roles', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-4 rounded-lg border p-4"
        >
            <h3 className="text-sm font-semibold">Create custom role</h3>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-1.5">
                    <Label htmlFor="role-name">Identifier</Label>
                    <Input
                        id="role-name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="e.g. support_agent"
                    />
                    <InputError message={errors.name} />
                </div>

                <div className="space-y-1.5">
                    <Label htmlFor="role-label">Display name</Label>
                    <Input
                        id="role-label"
                        value={data.label}
                        onChange={(e) => setData('label', e.target.value)}
                        placeholder="e.g. Support Agent"
                    />
                    <InputError message={errors.label} />
                </div>
            </div>

            <div className="space-y-1.5">
                <Label>Permissions</Label>
                <div className="flex flex-wrap gap-2">
                    {grantablePermissions.map((perm) => (
                        <button
                            key={perm}
                            type="button"
                            onClick={() => togglePermission(perm)}
                            className={`inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium transition-colors ${
                                data.permissions.includes(perm)
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border bg-background text-foreground hover:bg-muted'
                            }`}
                        >
                            {PERMISSION_LABELS[perm] ?? perm}
                        </button>
                    ))}
                </div>
                {data.permissions.length === 0 && (
                    <p className="text-xs text-muted-foreground">
                        Select at least one permission.
                    </p>
                )}
                <InputError message={errors.permissions} />
            </div>

            <Button
                type="submit"
                disabled={processing || data.permissions.length === 0}
            >
                {processing ? 'Creating…' : 'Create role'}
            </Button>
        </form>
    );
}

export default function Roles() {
    const { customRoles, roleTemplates, grantablePermissions } =
        usePage<PageProps>().props;
    const [deleting, setDeleting] = useState<number | null>(null);

    const handleDelete = (role: CustomRole) => {
        if (deleting === role.id) {
            router.delete(`/settings/roles/${role.id}`, {
                preserveScroll: true,
            });
            setDeleting(null);
        } else {
            setDeleting(role.id);
            setTimeout(() => setDeleting(null), 3000);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Custom Roles" />

            <SettingsLayout>
                <div className="space-y-8">
                    <HeadingSmall
                        title="Custom Roles"
                        description="Create organization-specific roles with a subset of grantable permissions. Assign them when inviting members."
                    />

                    {/* Role Templates */}
                    {roleTemplates.length > 0 && (
                        <div className="space-y-3">
                            <h3 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                                System templates
                            </h3>
                            <div className="divide-y rounded-lg border">
                                {roleTemplates.map((template) => (
                                    <div
                                        key={template.name}
                                        className="flex items-center justify-between gap-4 px-4 py-3"
                                    >
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <p className="text-sm font-medium">
                                                    {template.label}
                                                </p>
                                                {template.plan_required && (
                                                    <span className="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                                        {template.plan_required}
                                                    </span>
                                                )}
                                            </div>
                                            <div className="mt-1 flex flex-wrap gap-1">
                                                {template.permissions.map(
                                                    (perm) => (
                                                        <PermissionBadge
                                                            key={perm}
                                                            permission={perm}
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                        <span className="font-mono text-xs text-muted-foreground">
                                            {template.name}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Custom Roles List */}
                    <div className="space-y-3">
                        <h3 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                            Your custom roles
                        </h3>

                        {customRoles.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No custom roles yet.
                            </p>
                        ) : (
                            <div className="divide-y rounded-lg border">
                                {customRoles.map((role) => (
                                    <div
                                        key={role.id}
                                        className="flex items-center justify-between gap-4 px-4 py-3"
                                    >
                                        <div>
                                            <p className="text-sm font-medium">
                                                {role.label}
                                            </p>
                                            <p className="font-mono text-xs text-muted-foreground">
                                                {role.name}
                                            </p>
                                            <div className="mt-1 flex flex-wrap gap-1">
                                                {role.permissions.map(
                                                    (perm) => (
                                                        <PermissionBadge
                                                            key={perm}
                                                            permission={perm}
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                        <Button
                                            variant={
                                                deleting === role.id
                                                    ? 'destructive'
                                                    : 'ghost'
                                            }
                                            size="sm"
                                            onClick={() => handleDelete(role)}
                                            title={
                                                deleting === role.id
                                                    ? 'Click again to confirm deletion'
                                                    : 'Delete role'
                                            }
                                        >
                                            <Trash2 className="h-4 w-4" />
                                            {deleting === role.id && (
                                                <span className="ml-1.5">
                                                    Confirm
                                                </span>
                                            )}
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Create Role Form */}
                    <Deferred
                        data="grantablePermissions"
                        fallback={
                            <div className="space-y-2 rounded-lg border p-4">
                                <Skeleton className="h-5 w-32" />
                                <Skeleton className="h-8 w-full" />
                                <Skeleton className="h-8 w-full" />
                                <Skeleton className="h-9 w-24" />
                            </div>
                        }
                    >
                        <CreateRoleForm
                            grantablePermissions={grantablePermissions}
                        />
                    </Deferred>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
