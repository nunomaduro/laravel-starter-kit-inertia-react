import PageController from '@/actions/App/Http/Controllers/PageController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { useAutoAnimate } from '@formkit/auto-animate/react';
import { Form, Head, Link } from '@inertiajs/react';
import { Copy, FileText, Pencil, Plus, Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';

interface PageRecord {
    id: number;
    name: string;
    slug: string;
    is_published: boolean;
    updated_at: string;
}

interface Props {
    pages: PageRecord[];
}

export default function PagesIndex({ pages }: Props) {
    const [autoAnimateParent] = useAutoAnimate({ duration: 200 });
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Pages', href: PageController.index().url },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pages" />
            <div
                className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
                data-pan="pages-index"
            >
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Pages</h1>
                    <Button asChild data-pan="pages-create">
                        <Link href={PageController.create().url}>
                            <Plus className="mr-2 size-4" />
                            New page
                        </Link>
                    </Button>
                </div>

                {pages.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16 text-center">
                        <FileText className="size-10 text-muted-foreground" />
                        <p className="mt-2 text-sm font-medium text-muted-foreground">
                            No pages yet
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Create a page to get started.
                        </p>
                        <Button
                            asChild
                            className="mt-4"
                            data-pan="pages-create"
                        >
                            <Link href={PageController.create().url}>
                                <Plus className="mr-2 size-4" />
                                Create page
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <ul
                        ref={autoAnimateParent}
                        className="space-y-2"
                    >
                        {pages.map((page) => (
                            <li
                                key={page.id}
                                className="flex items-center justify-between gap-4 rounded-lg border bg-card p-4"
                            >
                                <div className="min-w-0 flex-1">
                                    <Link
                                        href={PageController.edit.url({
                                            page: page.id,
                                        })}
                                        className="font-medium text-foreground hover:underline"
                                    >
                                        {page.name}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">
                                        /{page.slug}
                                        {!page.is_published && (
                                            <span className="ml-2 rounded bg-muted px-1.5 py-0.5 text-[10px]">
                                                Draft
                                            </span>
                                        )}
                                    </p>
                                </div>
                                <div className="flex shrink-0 items-center gap-2">
                                    {page.is_published && (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            asChild
                                        >
                                            <Link href={`/p/${page.slug}`}>
                                                View
                                            </Link>
                                        </Button>
                                    )}
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={PageController.edit.url({
                                                page: page.id,
                                            })}
                                            data-pan="pages-edit-save"
                                        >
                                            <Pencil className="mr-1 size-3.5" />
                                            Edit
                                        </Link>
                                    </Button>
                                    <Form
                                        action={PageController.duplicate.url({
                                            page: page.id,
                                        })}
                                        method="post"
                                    >
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            size="sm"
                                            title="Duplicate"
                                            data-pan="pages-duplicate"
                                        >
                                            <Copy className="size-3.5" />
                                        </Button>
                                    </Form>
                                    <Form
                                        action={PageController.destroy.url({
                                            page: page.id,
                                        })}
                                        method="delete"
                                        onSubmit={(e) => {
                                            if (
                                                !confirm(
                                                    'Delete this page? This cannot be undone.',
                                                )
                                            ) {
                                                e.preventDefault();
                                            }
                                        }}
                                    >
                                        <Button
                                            type="submit"
                                            variant="ghost"
                                            size="sm"
                                            data-pan="pages-delete"
                                        >
                                            <Trash2 className="size-3.5 text-destructive" />
                                        </Button>
                                    </Form>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AppLayout>
    );
}
