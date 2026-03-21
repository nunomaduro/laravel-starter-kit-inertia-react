import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/employees' },
    { title: 'Leave Requests', href: '/hr/leave-requests' },
];

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
}

interface LeaveRequest {
    id: number;
    employee_id: number;
    type: string;
    start_date: string;
    end_date: string;
    reason: string | null;
    status: string;
    employee: Employee | null;
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
    leaveRequests: PaginatedData<LeaveRequest>;
}

function statusBadgeClass(status: string): string {
    switch (status) {
        case 'approved':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
        case 'rejected':
            return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        case 'pending':
            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
        default:
            return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
    }
}

export default function LeaveRequestsIndex() {
    const { leaveRequests } = usePage<Props & SharedData>().props;
    const { flash } = usePage<{ flash?: { status?: string } } & SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Leave Requests" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-lg font-medium">Leave Requests</h2>
                    <Button asChild>
                        <Link href="/hr/leave-requests/create">
                            <Plus className="mr-2 size-4" />
                            New request
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
                                <th className="px-4 py-3 text-left font-medium">Employee</th>
                                <th className="px-4 py-3 text-left font-medium">Type</th>
                                <th className="px-4 py-3 text-left font-medium">Start Date</th>
                                <th className="px-4 py-3 text-left font-medium">End Date</th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {leaveRequests.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        No leave requests found.
                                    </td>
                                </tr>
                            ) : (
                                leaveRequests.data.map((request) => (
                                    <tr key={request.id} className="transition-colors hover:bg-muted/50">
                                        <td className="px-4 py-3 font-medium">
                                            {request.employee
                                                ? `${request.employee.first_name} ${request.employee.last_name}`
                                                : '-'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground capitalize">{request.type}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{request.start_date}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{request.end_date}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${statusBadgeClass(request.status)}`}>
                                                {request.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={`/hr/leave-requests/${request.id}/edit`}>
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

                {leaveRequests.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing page {leaveRequests.current_page} of {leaveRequests.last_page} ({leaveRequests.total} total)
                        </p>
                        <div className="flex gap-2">
                            {leaveRequests.prev_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={leaveRequests.prev_page_url}>Previous</Link>
                                </Button>
                            )}
                            {leaveRequests.next_page_url && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={leaveRequests.next_page_url}>Next</Link>
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
