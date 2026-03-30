import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { MailPlus, RefreshCw, Trash2 } from 'lucide-react';

interface Inviter {
    id: number;
    name: string;
}

interface Invitation {
    id: number;
    email: string;
    role: string;
    status: string;
    inviter: Inviter | null;
    expires_at: string | null;
    accepted_at: string | null;
    created_at: string;
}

interface PaginatedInvitations {
    data: Invitation[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    total: number;
}

interface OrganizationRef {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    organization: OrganizationRef;
    invitations: PaginatedInvitations;
    assignableRoles: string[];
}

type BadgeColor = 'success' | 'warning' | 'error' | 'neutral';

const STATUS_COLOR: Record<string, BadgeColor> = {
    pending: 'warning',
    accepted: 'success',
    expired: 'error',
    cancelled: 'neutral',
};

function statusLabel(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

export default function InvitationsIndex({ organization, invitations }: Props) {
    const orgBase = `/organizations/${organization.id}`;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: organization.name, href: `${orgBase}/edit` },
        { title: 'Invitations', href: `${orgBase}/invitations` },
    ];

    function handleRevoke(inv: Invitation) {
        if (confirm('Revoke this invitation?')) {
            router.delete(`${orgBase}/invitations/${inv.id}`);
        }
    }

    function handleResend(inv: Invitation) {
        router.put(`${orgBase}/invitations/${inv.id}/resend`);
    }

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Invitations" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-mono font-semibold tracking-tight">Invitations</h1>
                        <p className="text-sm text-muted-foreground">{invitations.total} total</p>
                    </div>
                    <Button asChild>
                        <Link href={`${orgBase}/invitations/create`}>
                            <MailPlus className="mr-2 h-4 w-4" />
                            Invite member
                        </Link>
                    </Button>
                </div>

                <div className="rounded-lg border border-border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-border bg-muted/50">
                                <th className="px-4 py-3 text-left font-medium text-muted-foreground">Email</th>
                                <th className="hidden px-4 py-3 text-left font-medium text-muted-foreground sm:table-cell">Role</th>
                                <th className="hidden px-4 py-3 text-left font-medium text-muted-foreground md:table-cell">Status</th>
                                <th className="hidden px-4 py-3 text-left font-medium text-muted-foreground lg:table-cell">Invited by</th>
                                <th className="hidden px-4 py-3 text-left font-medium text-muted-foreground lg:table-cell">Expires</th>
                                <th className="hidden px-4 py-3 text-left font-medium text-muted-foreground xl:table-cell">Accepted</th>
                                <th className="hidden px-4 py-3 text-left font-medium text-muted-foreground xl:table-cell">Created</th>
                                <th className="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {invitations.data.map((inv) => {
                                const statusKey = typeof inv.status === 'string' ? inv.status : String(inv.status);
                                const normalizedStatus = statusKey.toLowerCase();

                                return (
                                    <tr key={inv.id} className="hover:bg-muted/50">
                                        <td className="px-4 py-3">{inv.email}</td>
                                        <td className="hidden px-4 py-3 capitalize sm:table-cell">{inv.role}</td>
                                        <td className="hidden px-4 py-3 md:table-cell">
                                            <Badge variant="soft" color={STATUS_COLOR[normalizedStatus] ?? 'neutral'}>
                                                {statusLabel(normalizedStatus)}
                                            </Badge>
                                        </td>
                                        <td className="hidden px-4 py-3 lg:table-cell">{inv.inviter?.name ?? '\u2014'}</td>
                                        <td className="hidden px-4 py-3 lg:table-cell">
                                            {inv.expires_at ? new Date(inv.expires_at).toLocaleDateString() : '\u2014'}
                                        </td>
                                        <td className="hidden px-4 py-3 xl:table-cell">
                                            {inv.accepted_at ? new Date(inv.accepted_at).toLocaleDateString() : '\u2014'}
                                        </td>
                                        <td className="hidden px-4 py-3 xl:table-cell">
                                            {inv.created_at ? new Date(inv.created_at).toLocaleDateString() : '\u2014'}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="flex items-center justify-end gap-1">
                                                {normalizedStatus === 'pending' && (
                                                    <>
                                                        <Button variant="ghost" size="icon" onClick={() => handleResend(inv)} title="Resend invitation">
                                                            <RefreshCw className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="ghost" size="icon" onClick={() => handleRevoke(inv)} title="Revoke invitation">
                                                            <Trash2 className="h-4 w-4 text-destructive" />
                                                        </Button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                            {invitations.data.length === 0 && (
                                <tr>
                                    <td colSpan={8} className="px-4 py-12 text-center text-muted-foreground">
                                        No invitations yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {invitations.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Page {invitations.current_page} of {invitations.last_page}
                        </p>
                        <div className="flex gap-2">
                            {invitations.prev_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={invitations.prev_page_url}>Previous</Link>
                                </Button>
                            )}
                            {invitations.next_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={invitations.next_page_url}>Next</Link>
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppSidebarLayout>
    );
}
