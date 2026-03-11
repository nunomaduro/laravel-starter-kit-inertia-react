import OrganizationController from '@/actions/App/Http/Controllers/OrganizationController';
import AppLayout from '@/layouts/app-layout';
import organizations from '@/routes/organizations';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Organizations', href: organizations.index.url() },
    { title: 'Create', href: OrganizationController.create.url() },
];

export default function OrganizationsCreate() {
    const { flash } = usePage<
        {
            flash?: { status?: string };
            errors?: Record<string, string>;
        } & SharedData
    >().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create organization" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h2 className="text-lg font-medium">Create organization</h2>

                {flash?.status && (
                    <p className="text-sm text-muted-foreground">
                        {flash.status}
                    </p>
                )}

                <Form
                    action={OrganizationController.store.url()}
                    method="post"
                    disableWhileProcessing
                    className="max-w-md space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <FormField
                                label="Name"
                                htmlFor="name"
                                error={errors.name}
                            >
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    required
                                    autoFocus
                                    placeholder="Acme Inc."
                                />
                            </FormField>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Creating…' : 'Create'}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href={organizations.index.url()}>
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
