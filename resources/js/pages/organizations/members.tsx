import OrganizationMemberController from '@/actions/App/Http/Controllers/OrganizationMemberController';
import AppLayout from '@/layouts/app-layout';
import organizations from '@/routes/organizations';
import { type BreadcrumbItem, type OrganizationSummary, type SharedData } from '@/types';
import { type OrgMember, type PendingInvitation } from '@/types/organizations';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    organization: OrganizationSummary;
    members: OrgMember[];
    pendingInvitations: PendingInvitation[];
}

export default function OrganizationsMembers() {
    const { organization, members, pendingInvitations, flash } = usePage<
        Props & {
            flash?: { status?: string };
            errors?: Record<string, string>;
        } & SharedData
    >().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Organizations', href: organizations.index.url() },
        {
            title: organization.name,
            href: organizations.show.url({ organization: organization.slug }),
        },
        {
            title: 'Members',
            href: organizations.members.index.url({
                organization: organization.slug,
            }),
        },
    ];

    const canManage = true; // Policy enforced on server

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Members – ${organization.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-lg font-medium">Members</h2>
                    <Button variant="outline" asChild>
                        <Link
                            href={organizations.show.url({
                                organization: organization.slug,
                            })}
                        >
                            Back to settings
                        </Link>
                    </Button>
                </div>

                {flash?.status && (
                    <p className="text-sm text-muted-foreground">
                        {flash.status}
                    </p>
                )}

                {canManage && (
                    <Form
                        action={organizations.invitations.store.url({
                            organization: organization.slug,
                        })}
                        method="post"
                        disableWhileProcessing
                        className="flex flex-wrap items-end gap-4 rounded-lg border p-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="email">
                                        Invite by email
                                    </Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        placeholder="colleague@example.com"
                                    />
                                    <InputError message={errors.email} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="role">Role</Label>
                                    <select
                                        id="role"
                                        name="role"
                                        className="flex h-9 w-[120px] rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                                        defaultValue="member"
                                    >
                                        <option value="member">Member</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <InputError message={errors.role} />
                                </div>
                                <Button type="submit" disabled={processing}>
                                    <UserPlus className="mr-2 size-4" />
                                    {processing ? 'Sending…' : 'Invite'}
                                </Button>
                            </>
                        )}
                    </Form>
                )}

                <div className="space-y-4">
                    <h3 className="font-medium">Members</h3>
                    <ul className="space-y-2">
                        {members.map((member) => (
                            <li
                                key={member.id}
                                className="flex flex-wrap items-center justify-between gap-2 rounded-lg border p-3"
                            >
                                <div>
                                    <span className="font-medium">
                                        {member.name}
                                    </span>
                                    <span className="text-muted-foreground">
                                        {' '}
                                        ({member.email})
                                    </span>
                                    {member.is_owner && (
                                        <span className="ml-2 text-xs text-muted-foreground">
                                            Owner
                                        </span>
                                    )}
                                    {!member.is_owner && (
                                        <span className="ml-2 text-xs text-muted-foreground capitalize">
                                            {member.role}
                                        </span>
                                    )}
                                </div>
                                {canManage && !member.is_owner && (
                                    <div className="flex gap-2">
                                        <Form
                                            action={OrganizationMemberController.update.url(
                                                {
                                                    organization:
                                                        organization.slug,
                                                    member: member.id,
                                                },
                                            )}
                                            method="put"
                                            disableWhileProcessing
                                        >
                                            {({ processing }) => (
                                                <>
                                                    <select
                                                        name="role"
                                                        defaultValue={
                                                            member.role
                                                        }
                                                        className="rounded-md border bg-background px-2 py-1 text-sm"
                                                        disabled={processing}
                                                    >
                                                        <option value="member">
                                                            Member
                                                        </option>
                                                        <option value="admin">
                                                            Admin
                                                        </option>
                                                    </select>
                                                    <Button
                                                        type="submit"
                                                        variant="ghost"
                                                        size="sm"
                                                        disabled={processing}
                                                    >
                                                        Update
                                                    </Button>
                                                </>
                                            )}
                                        </Form>
                                        <Form
                                            action={OrganizationMemberController.destroy.url(
                                                {
                                                    organization:
                                                        organization.slug,
                                                    member: member.id,
                                                },
                                            )}
                                            method="delete"
                                            disableWhileProcessing
                                            onSubmit={(e) => {
                                                if (
                                                    !confirm(
                                                        'Remove this member?',
                                                    )
                                                ) {
                                                    e.preventDefault();
                                                }
                                            }}
                                        >
                                            <Button
                                                type="submit"
                                                variant="outline"
                                                size="sm"
                                            >
                                                Remove
                                            </Button>
                                        </Form>
                                    </div>
                                )}
                            </li>
                        ))}
                    </ul>
                </div>

                {pendingInvitations.length > 0 && (
                    <div className="space-y-4">
                        <h3 className="font-medium">Pending invitations</h3>
                        <ul className="space-y-2">
                            {pendingInvitations.map((inv) => (
                                <li
                                    key={inv.id}
                                    className="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-dashed p-3"
                                >
                                    <span className="text-sm">
                                        {inv.email} – {inv.role}
                                    </span>
                                    {canManage && (
                                        <div className="flex gap-2">
                                            <Form
                                                action={organizations.invitations.resend.url(
                                                    {
                                                        organization:
                                                            organization.slug,
                                                        invitation: inv.id,
                                                    },
                                                )}
                                                method="put"
                                                disableWhileProcessing
                                            >
                                                <Button
                                                    type="submit"
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    Resend
                                                </Button>
                                            </Form>
                                            <Form
                                                action={organizations.invitations.destroy.url(
                                                    {
                                                        organization:
                                                            organization.slug,
                                                        invitation: inv.id,
                                                    },
                                                )}
                                                method="delete"
                                                disableWhileProcessing
                                            >
                                                <Button
                                                    type="submit"
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    Cancel
                                                </Button>
                                            </Form>
                                        </div>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
