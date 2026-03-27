import { Head, Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin-layout';
import type { BreadcrumbItem, PaginatedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users', href: '/admin/users' }];

type User = {
    id: string;
    name: string;
    email: string;
    role: string;
    created_at: string;
    properties_count?: number;
    commission_rate: number | null;
};

type Props = {
    users: PaginatedData<User>;
    role?: string;
    search?: string;
};

const ROLE_TABS = [
    { label: 'All', value: 'all' },
    { label: 'Guest', value: 'guest' },
    { label: 'Host', value: 'host' },
    { label: 'Admin', value: 'admin' },
];

const roleBadgeVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    admin: 'default',
    host: 'secondary',
    guest: 'outline',
};

export default function AdminUsersIndex({ users, role, search }: Props) {
    const currentRole = role ?? 'all';
    const [searchQuery, setSearchQuery] = useState(search ?? '');
    const [editDialogOpen, setEditDialogOpen] = useState(false);
    const [editUser, setEditUser] = useState<User | null>(null);
    const [editRole, setEditRole] = useState('');
    const [editCommission, setEditCommission] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/users', { search: searchQuery, role: currentRole === 'all' ? undefined : currentRole }, { preserveState: true });
    };

    const openEditDialog = (user: User) => {
        setEditUser(user);
        setEditRole(user.role);
        setEditCommission(user.commission_rate?.toString() ?? '');
        setEditDialogOpen(true);
    };

    const handleSaveUser = () => {
        if (!editUser) {
            return;
        }
        router.patch(`/admin/users/${editUser.id}`, {
            role: editRole,
            commission_rate: editRole === 'host' && editCommission ? Number(editCommission) : null,
        });
        setEditDialogOpen(false);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="User Management" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">User Management</h1>

                <form onSubmit={handleSearch} className="flex gap-2">
                    <div className="relative flex-1 max-w-sm">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Search users..."
                            className="pl-9"
                        />
                    </div>
                    <Button type="submit" variant="outline">
                        Search
                    </Button>
                </form>

                <div className="flex flex-wrap gap-2">
                    {ROLE_TABS.map((tab) => (
                        <Link
                            key={tab.value}
                            href={tab.value === 'all' ? '/admin/users' : `/admin/users?role=${tab.value}`}
                            preserveState
                        >
                            <Button variant={currentRole === tab.value ? 'default' : 'ghost'} size="sm">
                                {tab.label}
                            </Button>
                        </Link>
                    ))}
                </div>

                {users.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16">
                        <p className="text-muted-foreground">No users found.</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b bg-muted/50">
                                    <th className="px-4 py-3 text-left font-medium">Name</th>
                                    <th className="px-4 py-3 text-left font-medium">Email</th>
                                    <th className="px-4 py-3 text-left font-medium">Role</th>
                                    <th className="px-4 py-3 text-left font-medium">Commission</th>
                                    <th className="px-4 py-3 text-left font-medium">Properties</th>
                                    <th className="px-4 py-3 text-left font-medium">Joined</th>
                                    <th className="px-4 py-3 text-right font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.data.map((user) => (
                                    <tr key={user.id} className="border-b last:border-b-0">
                                        <td className="px-4 py-3 font-medium">{user.name}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{user.email}</td>
                                        <td className="px-4 py-3">
                                            <Badge variant={roleBadgeVariant[user.role] ?? 'outline'} className="capitalize">
                                                {user.role}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {user.commission_rate !== null ? `${user.commission_rate}%` : '-'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {user.properties_count ?? '-'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {new Date(user.created_at).toLocaleDateString()}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="outline" size="sm" onClick={() => openEditDialog(user)}>
                                                Edit
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {(users.prev_page_url || users.next_page_url) && (
                    <div className="flex items-center justify-between">
                        {users.prev_page_url ? (
                            <Link href={users.prev_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Previous
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                        <span className="text-sm text-muted-foreground">
                            Page {users.current_page} of {users.last_page}
                        </span>
                        {users.next_page_url ? (
                            <Link href={users.next_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Next
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                    </div>
                )}
            </div>

            <Dialog open={editDialogOpen} onOpenChange={setEditDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit User - {editUser?.name}</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div>
                            <Label htmlFor="edit-role">Role</Label>
                            <select
                                id="edit-role"
                                value={editRole}
                                onChange={(e) => setEditRole(e.target.value)}
                                className="border-input focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                            >
                                <option value="guest">Guest</option>
                                <option value="host">Host</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        {editRole === 'host' && (
                            <div>
                                <Label htmlFor="edit-commission">Commission Rate (%)</Label>
                                <Input
                                    id="edit-commission"
                                    type="number"
                                    min={0}
                                    max={100}
                                    value={editCommission}
                                    onChange={(e) => setEditCommission(e.target.value)}
                                    className="mt-1"
                                    placeholder="e.g. 10"
                                />
                            </div>
                        )}
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setEditDialogOpen(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleSaveUser}>Save Changes</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
