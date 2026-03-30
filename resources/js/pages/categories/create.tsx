import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    categories: CategoryOption[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Categories', href: '/categories' },
    { title: 'Create', href: '/categories/create' },
];

export default function CategoriesCreate({ categories }: Props) {
    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Category" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <h2 className="text-lg font-mono font-semibold tracking-tight">Create category</h2>

                <Form
                    action="/categories"
                    method="post"
                    disableWhileProcessing
                    className="max-w-lg space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <FormField label="Name" htmlFor="name" error={errors.name} required>
                                <Input id="name" name="name" type="text" required autoFocus />
                            </FormField>

                            <FormField label="Type" htmlFor="type" error={errors.type}>
                                <Input id="type" name="type" type="text" defaultValue="default" />
                            </FormField>

                            <FormField label="Parent category" htmlFor="parent_id" error={errors.parent_id}>
                                <select
                                    id="parent_id"
                                    name="parent_id"
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">None (top-level)</option>
                                    {categories.map((cat) => (
                                        <option key={cat.id} value={cat.id}>
                                            {cat.name}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Creating...' : 'Create category'}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/categories">Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppSidebarLayout>
    );
}
