import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface UserShape {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
}

interface Props {
    user: UserShape;
}

export default function UserShowPage({ user }: Props) {
    return (
        <AppSidebarLayout>
            <Head title={user.name} />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center gap-2">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href="/users">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <h1 className="text-2xl font-bold tracking-tight">
                        {user.name}
                    </h1>
                </div>
                <dl className="grid gap-2 text-sm">
                    <div>
                        <dt className="font-medium text-muted-foreground">
                            Email
                        </dt>
                        <dd>{user.email}</dd>
                    </div>
                    {user.created_at && (
                        <div>
                            <dt className="font-medium text-muted-foreground">
                                Created at
                            </dt>
                            <dd>
                                {new Date(user.created_at).toLocaleDateString()}
                            </dd>
                        </div>
                    )}
                </dl>
            </div>
        </AppSidebarLayout>
    );
}
