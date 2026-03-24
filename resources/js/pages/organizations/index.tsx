import AppLayout from '@/layouts/app-layout';
import organizations from '@/routes/organizations';
import { type BreadcrumbItem, type OrganizationSummary, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Building2, Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Organizations', href: organizations.index.url() },
];

interface Props {
    organizations: OrganizationSummary[];
    currentOrganization: OrganizationSummary | null;
}

export default function OrganizationsIndex() {
    const { organizations: userOrganizations, currentOrganization } = usePage<
        Props & SharedData
    >().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Organizations" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-lg font-medium">Organizations</h2>
                    <Button asChild>
                        <Link href={organizations.create.url()}>
                            <Plus className="mr-2 size-4" />
                            New organization
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Building2 className="size-5" />
                            Your organizations
                        </CardTitle>
                        <CardDescription>
                            Switch between organizations or create a new one.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {userOrganizations.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                You are not in any organizations yet.{' '}
                                <Link
                                    href={organizations.create.url()}
                                    className="font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    Create one
                                </Link>
                            </p>
                        ) : (
                            <ul className="space-y-2">
                                {(
                                    userOrganizations as OrganizationSummary[]
                                ).map((org) => (
                                    <li key={org.id}>
                                        <Link
                                            href={organizations.show.url({
                                                organization: org.slug,
                                            })}
                                            className="flex items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                                        >
                                            <span className="font-medium">
                                                {org.name}
                                            </span>
                                            {currentOrganization?.id ===
                                                org.id && (
                                                <span className="text-xs text-muted-foreground">
                                                    Current
                                                </span>
                                            )}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
