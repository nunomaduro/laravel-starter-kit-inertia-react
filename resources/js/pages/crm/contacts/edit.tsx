import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';

interface Contact {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    company: string | null;
    position: string | null;
    source: string | null;
    status: string;
    notes: string | null;
}

interface Props {
    contact: Contact;
}

export default function ContactsEdit() {
    const { contact } = usePage<Props & SharedData>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'CRM', href: '/crm/contacts' },
        { title: 'Contacts', href: '/crm/contacts' },
        { title: `${contact.first_name} ${contact.last_name}`, href: `/crm/contacts/${contact.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${contact.first_name} ${contact.last_name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h2 className="text-lg font-medium">Edit contact</h2>

                <Form
                    action={`/crm/contacts/${contact.id}`}
                    method="put"
                    disableWhileProcessing
                    className="max-w-lg space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField label="First name" htmlFor="first_name" error={errors.first_name} required>
                                    <Input id="first_name" name="first_name" type="text" required defaultValue={contact.first_name} />
                                </FormField>
                                <FormField label="Last name" htmlFor="last_name" error={errors.last_name} required>
                                    <Input id="last_name" name="last_name" type="text" required defaultValue={contact.last_name} />
                                </FormField>
                            </div>

                            <FormField label="Email" htmlFor="email" error={errors.email} required>
                                <Input id="email" name="email" type="email" required defaultValue={contact.email} />
                            </FormField>

                            <FormField label="Phone" htmlFor="phone" error={errors.phone}>
                                <Input id="phone" name="phone" type="tel" defaultValue={contact.phone ?? ''} />
                            </FormField>

                            <FormField label="Company" htmlFor="company" error={errors.company}>
                                <Input id="company" name="company" type="text" defaultValue={contact.company ?? ''} />
                            </FormField>

                            <FormField label="Position" htmlFor="position" error={errors.position}>
                                <Input id="position" name="position" type="text" defaultValue={contact.position ?? ''} />
                            </FormField>

                            <FormField label="Source" htmlFor="source" error={errors.source}>
                                <select
                                    id="source"
                                    name="source"
                                    defaultValue={contact.source ?? ''}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">Select source</option>
                                    <option value="website">Website</option>
                                    <option value="referral">Referral</option>
                                    <option value="social">Social Media</option>
                                    <option value="email">Email Campaign</option>
                                    <option value="cold_call">Cold Call</option>
                                    <option value="other">Other</option>
                                </select>
                            </FormField>

                            <FormField label="Status" htmlFor="status" error={errors.status}>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={contact.status}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="lead">Lead</option>
                                </select>
                            </FormField>

                            <FormField label="Notes" htmlFor="notes" error={errors.notes}>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    defaultValue={contact.notes ?? ''}
                                    className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                />
                            </FormField>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Saving...' : 'Save'}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/crm/contacts">Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
