import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/employees' },
    { title: 'Employees', href: '/hr/employees' },
    { title: 'Create', href: '/hr/employees/create' },
];

interface Department {
    id: number;
    name: string;
}

interface Props {
    departments: Department[];
}

export default function EmployeesCreate() {
    const { departments } = usePage<Props & SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Employee" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h2 className="text-lg font-medium">Create employee</h2>

                <Form
                    action="/hr/employees"
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

                            <FormField label="Position" htmlFor="position" error={errors.position}>
                                <Input id="position" name="position" type="text" />
                            </FormField>

                            <FormField label="Hire date" htmlFor="hire_date" error={errors.hire_date} required>
                                <Input id="hire_date" name="hire_date" type="date" required />
                            </FormField>

                            <FormField label="Salary" htmlFor="salary" error={errors.salary}>
                                <Input id="salary" name="salary" type="number" step="0.01" min="0" />
                            </FormField>

                            <FormField label="Department" htmlFor="department_id" error={errors.department_id}>
                                <select
                                    id="department_id"
                                    name="department_id"
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">Select department</option>
                                    {departments.map((dept) => (
                                        <option key={dept.id} value={dept.id}>
                                            {dept.name}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Creating...' : 'Create'}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/hr/employees">Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
