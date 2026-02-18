import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { index as changelogIndex } from '@/routes/changelog';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface ChangelogEntry {
    id: number;
    title: string;
    description: string;
    version: string | null;
    type: string;
    released_at: string | null;
}

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    entries: {
        data: ChangelogEntry[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        links: PaginatorLink[];
    };
}

const typeColors: Record<string, string> = {
    added: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
    changed:
        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    fixed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    removed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    security:
        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
};

export default function ChangelogIndex({ entries }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Changelog', href: changelogIndex().url },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Changelog" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-semibold">Changelog</h1>
                {entries.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16 text-center">
                        <p className="text-sm font-medium text-muted-foreground">
                            No changelog entries yet
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Updates and release notes will appear here.
                        </p>
                    </div>
                ) : (
                    <ul className="space-y-6">
                        {entries.data.map((entry) => (
                            <li
                                key={entry.id}
                                className="rounded-lg border bg-card p-4"
                            >
                                <div className="mb-2 flex flex-wrap items-center gap-2">
                                    {entry.version && (
                                        <span className="text-sm font-medium text-muted-foreground">
                                            {entry.version}
                                        </span>
                                    )}
                                    <span
                                        className={`rounded px-2 py-0.5 text-xs font-medium capitalize ${typeColors[entry.type] ?? 'bg-muted text-muted-foreground'}`}
                                    >
                                        {entry.type}
                                    </span>
                                </div>
                                <h2 className="font-medium text-foreground">
                                    {entry.title}
                                </h2>
                                <p className="mt-1 text-sm whitespace-pre-wrap text-muted-foreground">
                                    {entry.description}
                                </p>
                                {entry.released_at && (
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        {new Date(
                                            entry.released_at,
                                        ).toLocaleDateString('en-CA', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                        })}
                                    </p>
                                )}
                            </li>
                        ))}
                    </ul>
                )}
                {(entries.prev_page_url || entries.next_page_url) && (
                    <nav
                        className="mt-8 flex items-center justify-center gap-4"
                        aria-label="Pagination"
                    >
                        {entries.prev_page_url ? (
                            <Link
                                href={entries.prev_page_url}
                                className="text-sm font-medium text-foreground underline underline-offset-4"
                            >
                                Previous
                            </Link>
                        ) : null}
                        <span className="text-sm text-muted-foreground">
                            Page {entries.current_page} of {entries.last_page}
                        </span>
                        {entries.next_page_url ? (
                            <Link
                                href={entries.next_page_url}
                                className="text-sm font-medium text-foreground underline underline-offset-4"
                            >
                                Next
                            </Link>
                        ) : null}
                    </nav>
                )}
            </div>
        </AppLayout>
    );
}
