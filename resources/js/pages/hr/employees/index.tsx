import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Employee } from '@/types/hr';
import { type PaginatedData } from '@/types/pagination';
import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/employees' },
    { title: 'Employees', href: '/hr/employees' },
];

interface Props {
    employees: PaginatedData<Employee>;
}

export default function EmployeesIndex() {
    const { employees } = usePage<Props & SharedData>().props;
    const { flash } = usePage<{ flash?: { status?: string } } & SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Employees" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-lg font-medium">Employees</h2>
                    <Button asChild>
                        <Link href="/hr/employees/create">
                            <Plus className="mr-2 size-4" />
                            New employee
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
                                <th className="px-4 py-3 text-left font-medium">Department</th>
                                <th className="px-4 py-3 text-left font-medium">Position</th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {employees.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        No employees found.
                                    </td>
                                </tr>
                            ) : (
                                employees.data.map((employee) => (
                                    <tr key={employee.id} className="transition-colors hover:bg-muted/50">
                                        <td className="px-4 py-3 font-medium">
                                            {employee.first_name} {employee.last_name}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">{employee.email}</td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {employee.department?.name ?? '-'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {employee.position ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                                employee.status === 'active'
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
                                            }`}>
                                                {employee.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={`/hr/employees/${employee.id}/edit`}>
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

                {employees.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing page {employees.current_page} of {employees.last_page} ({employees.total} total)
                        </p>
                        <div className="flex gap-2">
                            {employees.prev_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={employees.prev_page_url}>Previous</Link>
                                </Button>
                            )}
                            {employees.next_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={employees.next_page_url}>Next</Link>
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
