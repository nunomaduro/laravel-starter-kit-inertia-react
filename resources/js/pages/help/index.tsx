import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { index as helpIndex, show as helpShow } from '@/routes/help';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface HelpArticle {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    category: string;
}

interface Props {
    featured: HelpArticle[];
    byCategory: Record<string, HelpArticle[]>;
}

export default function HelpIndex({ featured, byCategory }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Help', href: helpIndex().url },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Help Center" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-semibold">Help Center</h1>
                {featured.length === 0 &&
                Object.keys(byCategory).length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16 text-center">
                        <p className="text-sm font-medium text-muted-foreground">
                            No help articles yet
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Help articles and guides will appear here once
                            published.
                        </p>
                    </div>
                ) : null}
                {featured.length > 0 && (
                    <section className="mb-8">
                        <h2 className="mb-3 text-lg font-medium">
                            Featured articles
                        </h2>
                        <ul className="grid gap-3 sm:grid-cols-2">
                            {featured.map((article) => (
                                <li key={article.id}>
                                    <Link
                                        href={
                                            helpShow({
                                                helpArticle: article.slug,
                                            }).url
                                        }
                                        className="block rounded-lg border bg-card p-4 transition-colors hover:bg-muted/50"
                                    >
                                        <span className="font-medium text-foreground">
                                            {article.title}
                                        </span>
                                        {article.excerpt && (
                                            <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                                {article.excerpt}
                                            </p>
                                        )}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
                <section>
                    {Object.entries(byCategory).map(
                        ([category, articles]) =>
                            articles.length > 0 && (
                                <div key={category} className="mb-8">
                                    <h2 className="mb-3 text-lg font-medium capitalize">
                                        {category}
                                    </h2>
                                    <ul className="space-y-2">
                                        {articles.map((article) => (
                                            <li key={article.id}>
                                                <Link
                                                    href={
                                                        helpShow({
                                                            helpArticle:
                                                                article.slug,
                                                        }).url
                                                    }
                                                    className="block rounded-md py-2 text-foreground underline underline-offset-4 hover:no-underline"
                                                >
                                                    {article.title}
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ),
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
