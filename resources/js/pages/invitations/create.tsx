import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';

interface OrganizationRef {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    organization: OrganizationRef;
    assignableRoles: string[];
}

export default function InvitationsCreate({ organization, assignableRoles }: Props) {
    const orgBase = `/organizations/${organization.id}`;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: organization.name, href: `${orgBase}/edit` },
        { title: 'Invitations', href: `${orgBase}/invitations` },
        { title: 'Invite member', href: `${orgBase}/invitations/create` },
    ];

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Invite Member" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <h2 className="text-lg font-mono font-semibold tracking-tight">Invite a new member</h2>

                <Form
                    action={`${orgBase}/invitations`}
                    method="post"
                    disableWhileProcessing
                    className="max-w-lg space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <FormField label="Email" htmlFor="email" error={errors.email} required>
                                <Input id="email" name="email" type="email" required autoFocus placeholder="colleague@example.com" />
                            </FormField>

                            <FormField label="Role" htmlFor="role" error={errors.role} required>
                                <select
                                    id="role"
                                    name="role"
                                    defaultValue={assignableRoles[1] ?? assignableRoles[0]}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    {assignableRoles.map((role) => (
                                        <option key={role} value={role}>
                                            {role.charAt(0).toUpperCase() + role.slice(1)}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Sending...' : 'Send invitation'}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href={`${orgBase}/invitations`}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppSidebarLayout>
    );
}
