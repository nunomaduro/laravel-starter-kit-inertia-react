import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    Clock,
    KeyRound,
    Mail,
    Phone,
    Shield,
    ShieldCheck,
    Tag,
    User,
} from 'lucide-react';

interface Organization {
    id: number;
    name: string;
}

interface UserShape {
    id: number;
    hash_id: string;
    name: string;
    email: string;
    phone: string | null;
    avatar: string | null;
    status: string;
    onboarding_completed: boolean;
    email_verified_at: string | null;
    two_factor_confirmed_at: string | null;
    roles: string[];
    tags: string[];
    organizations: Organization[];
    created_at: string | null;
}

interface Props {
    user: UserShape;
}

type BadgeColor = 'success' | 'warning' | 'error';

const STATUS_BADGE: Record<string, { label: string; color: BadgeColor }> = {
    active: { label: 'Active', color: 'success' },
    pending: { label: 'Pending', color: 'warning' },
    deleted: { label: 'Deleted', color: 'error' },
};

export default function UserShowPage({ user }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Users', href: '/users' },
        { title: user.name, href: `/users/${user.hash_id}` },
    ];

    const status = STATUS_BADGE[user.status] ?? {
        label: user.status,
        color: 'warning' as const,
    };
    const initials = user.name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title={user.name} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href="/users" aria-label="Back to users">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex items-center gap-3">
                        {user.avatar ? (
                            <img
                                src={user.avatar}
                                alt={user.name}
                                className="h-10 w-10 rounded-full object-cover ring-1 ring-border"
                            />
                        ) : (
                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-muted text-sm font-medium text-muted-foreground ring-1 ring-border">
                                {initials}
                            </div>
                        )}
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-xl font-mono font-semibold tracking-tight">
                                    {user.name}
                                </h1>
                                <Badge variant="filled" color={status.color}>
                                    {status.label}
                                </Badge>
                            </div>
                            <p className="text-sm text-muted-foreground">
                                #{user.id}
                            </p>
                        </div>
                    </div>
                </div>

                <Separator />

                {/* Details grid */}
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <dl className="space-y-1">
                        <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <Mail className="h-3.5 w-3.5" />
                            Email
                        </dt>
                        <dd className="text-sm">
                            <a
                                href={`mailto:${user.email}`}
                                className="text-primary hover:underline"
                            >
                                {user.email}
                            </a>
                        </dd>
                    </dl>

                    {user.phone && (
                        <dl className="space-y-1">
                            <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                                <Phone className="h-3.5 w-3.5" />
                                Phone
                            </dt>
                            <dd className="text-sm">{user.phone}</dd>
                        </dl>
                    )}

                    {user.roles.length > 0 && (
                        <dl className="space-y-1">
                            <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                                <Shield className="h-3.5 w-3.5" />
                                Roles
                            </dt>
                            <dd className="flex flex-wrap gap-1.5 text-sm">
                                {user.roles.map((role) => (
                                    <Badge key={role} variant="soft" color="neutral">
                                        {role}
                                    </Badge>
                                ))}
                            </dd>
                        </dl>
                    )}

                    {user.tags.length > 0 && (
                        <dl className="space-y-1">
                            <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                                <Tag className="h-3.5 w-3.5" />
                                Tags
                            </dt>
                            <dd className="flex flex-wrap gap-1.5 text-sm">
                                {user.tags.map((tag) => (
                                    <span
                                        key={tag}
                                        className="inline-flex items-center rounded-md bg-muted px-2 py-0.5 text-xs font-medium"
                                    >
                                        {tag}
                                    </span>
                                ))}
                            </dd>
                        </dl>
                    )}

                    <dl className="space-y-1">
                        <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <ShieldCheck className="h-3.5 w-3.5" />
                            Email verified
                        </dt>
                        <dd className="text-sm">
                            {user.email_verified_at ? (
                                new Date(
                                    user.email_verified_at,
                                ).toLocaleString()
                            ) : (
                                <span className="text-muted-foreground">
                                    Not verified
                                </span>
                            )}
                        </dd>
                    </dl>

                    <dl className="space-y-1">
                        <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <KeyRound className="h-3.5 w-3.5" />
                            Two-factor auth
                        </dt>
                        <dd className="text-sm">
                            {user.two_factor_confirmed_at ? (
                                <span className="text-emerald-600 dark:text-emerald-400">
                                    Enabled
                                </span>
                            ) : (
                                <span className="text-muted-foreground">
                                    Not enabled
                                </span>
                            )}
                        </dd>
                    </dl>

                    <dl className="space-y-1">
                        <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <CheckCircle2 className="h-3.5 w-3.5" />
                            Onboarding
                        </dt>
                        <dd className="text-sm">
                            {user.onboarding_completed ? (
                                <span className="text-emerald-600 dark:text-emerald-400">
                                    Completed
                                </span>
                            ) : (
                                <span className="text-amber-600 dark:text-amber-400">
                                    Incomplete
                                </span>
                            )}
                        </dd>
                    </dl>

                    <dl className="space-y-1">
                        <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <Clock className="h-3.5 w-3.5" />
                            Member since
                        </dt>
                        <dd className="text-sm">
                            {user.created_at
                                ? new Date(user.created_at).toLocaleDateString(
                                      undefined,
                                      {
                                          year: 'numeric',
                                          month: 'long',
                                          day: 'numeric',
                                      },
                                  )
                                : '—'}
                        </dd>
                    </dl>

                    {user.organizations.length > 0 && (
                        <dl className="space-y-1 sm:col-span-2">
                            <dt className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                                <Building2 className="h-3.5 w-3.5" />
                                Organizations
                            </dt>
                            <dd className="flex flex-wrap gap-1.5 text-sm">
                                {user.organizations.map((org) => (
                                    <span
                                        key={org.id}
                                        className="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground"
                                    >
                                        <User className="h-3 w-3" />
                                        {org.name}
                                    </span>
                                ))}
                            </dd>
                        </dl>
                    )}
                </div>
            </div>
        </AppSidebarLayout>
    );
}
