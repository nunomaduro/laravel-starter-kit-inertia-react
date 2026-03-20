import TextLink from '@/components/text-link';
import AppLayout from '@/layouts/app-layout';
import { sanitizeHtml } from '@/lib/sanitize-html';
import { dashboard } from '@/routes';
import {
    index as helpIndex,
    rate as helpRate,
    show as helpShow,
} from '@/routes/help';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';

interface HelpArticle {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    category: string;
}

interface RelatedArticle {
    id: number;
    title: string;
    slug: string;
}

interface Props {
    article: HelpArticle;
    related: RelatedArticle[];
}

export default function HelpShow({ article, related }: Props) {
    const { flash } = usePage<{ flash?: { status?: string } }>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Help', href: helpIndex().url },
        {
            title: article.title,
            href: helpShow({ helpArticle: article.slug }).url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={article.title} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <p className="text-sm text-muted-foreground">
                    <TextLink href={helpIndex().url}>Back to help</TextLink>
                </p>
                <article>
                    <h1 className="mb-2 text-2xl font-semibold">
                        {article.title}
                    </h1>
                    {article.excerpt && (
                        <p className="mb-6 text-muted-foreground">
                            {article.excerpt}
                        </p>
                    )}
                    <div
                        className="prose prose-neutral dark:prose-invert max-w-none"
                        dangerouslySetInnerHTML={{
                            __html: sanitizeHtml(article.content),
                        }}
                    />
                </article>
                <section className="mt-8 border-t pt-6">
                    <h2 className="mb-3 text-sm font-medium">
                        Was this helpful?
                    </h2>
                    {flash?.status ? (
                        <p className="text-sm text-muted-foreground">
                            {flash.status}
                        </p>
                    ) : (
                        <div className="flex flex-wrap items-center gap-3">
                            <Form
                                action={
                                    helpRate({ helpArticle: article.slug }).url
                                }
                                method="post"
                                className="inline-block"
                            >
                                <input
                                    type="hidden"
                                    name="is_helpful"
                                    value="1"
                                />
                                <button
                                    type="submit"
                                    className="rounded-md border bg-background px-4 py-2 text-sm font-medium hover:bg-muted"
                                >
                                    Yes
                                </button>
                            </Form>
                            <Form
                                action={
                                    helpRate({ helpArticle: article.slug }).url
                                }
                                method="post"
                                className="inline-block"
                            >
                                <input
                                    type="hidden"
                                    name="is_helpful"
                                    value="0"
                                />
                                <button
                                    type="submit"
                                    className="text-sm text-muted-foreground underline underline-offset-4 hover:text-foreground"
                                >
                                    No
                                </button>
                            </Form>
                        </div>
                    )}
                </section>
                {related.length > 0 && (
                    <section className="mt-8 border-t pt-6">
                        <h2 className="mb-3 text-lg font-medium">
                            Related articles
                        </h2>
                        <ul className="space-y-2">
                            {related.map((item) => (
                                <li key={item.id}>
                                    <Link
                                        href={
                                            helpShow({
                                                helpArticle: item.slug,
                                            }).url
                                        }
                                        className="text-foreground underline underline-offset-4 hover:no-underline"
                                    >
                                        {item.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </AppLayout>
    );
}
