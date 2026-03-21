import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'CRM', href: '/crm/contacts' },
    { title: 'Contacts', href: '/crm/contacts' },
    { title: 'Create', href: '/crm/contacts/create' },
];

export default function ContactsCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Contact" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h2 className="text-lg font-medium">Create contact</h2>

                <Form
                    action="/crm/contacts"
                    method="post"
                    disableWhileProcessing
                    className="max-w-lg space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField label="First name" htmlFor="first_name" error={errors.first_name} required>
                                    <Input id="first_name" name="first_name" type="text" required autoFocus />
                                </FormField>
                                <FormField label="Last name" htmlFor="last_name" error={errors.last_name} required>
                                    <Input id="last_name" name="last_name" type="text" required />
                                </FormField>
                            </div>

                            <FormField label="Email" htmlFor="email" error={errors.email} required>
                                <Input id="email" name="email" type="email" required />
                            </FormField>

                            <FormField label="Phone" htmlFor="phone" error={errors.phone}>
                                <Input id="phone" name="phone" type="tel" />
                            </FormField>

                            <FormField label="Company" htmlFor="company" error={errors.company}>
                                <Input id="company" name="company" type="text" />
                            </FormField>

                            <FormField label="Position" htmlFor="position" error={errors.position}>
                                <Input id="position" name="position" type="text" />
                            </FormField>

                            <FormField label="Source" htmlFor="source" error={errors.source}>
                                <select
                                    id="source"
                                    name="source"
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

                            <FormField label="Notes" htmlFor="notes" error={errors.notes}>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                />
                            </FormField>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Creating...' : 'Create'}
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
