import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'CRM', href: '/crm/contacts' },
    { title: 'Contacts', href: '/crm/contacts' },
];

interface Contact {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    company: string | null;
    position: string | null;
    status: string;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface Props {
    contacts: PaginatedData<Contact>;
}

export default function ContactsIndex() {
    const { contacts } = usePage<Props & SharedData>().props;
    const { flash } = usePage<{ flash?: { status?: string } } & SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contacts" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-lg font-medium">Contacts</h2>
                    <Button asChild>
                        <Link href="/crm/contacts/create">
                            <Plus className="mr-2 size-4" />
                            New contact
                        </Link>
                    </Button>
                </div>

                {flash?.status && (
                    <p className="text-sm text-emerald-600 dark:text-emerald-400">
                        {flash.status}
                    </p>
                )}

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Name</th>
                                <th className="px-4 py-3 text-left font-medium">Email</th>
                                <th className="px-4 py-3 text-left font-medium">Company</th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {contacts.data.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                        No contacts found.
                                    </td>
                                </tr>
                            ) : (
                                contacts.data.map((contact) => (
                                    <tr key={contact.id} className="transition-colors hover:bg-muted/50">
                                        <td className="px-4 py-3 font-medium">
                                            {contact.first_name} {contact.last_name}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">{contact.email}</td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {contact.company ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                                contact.status === 'active'
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
                                            }`}>
                                                {contact.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={`/crm/contacts/${contact.id}/edit`}>
                                                    Edit
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {contacts.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing page {contacts.current_page} of {contacts.last_page} ({contacts.total} total)
                        </p>
                        <div className="flex gap-2">
                            {contacts.prev_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={contacts.prev_page_url}>Previous</Link>
                                </Button>
                            )}
                            {contacts.next_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={contacts.next_page_url}>Next</Link>
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
