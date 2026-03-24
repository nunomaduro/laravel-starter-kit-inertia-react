import TextLink from '@/components/text-link';
import AppLayout from '@/layouts/app-layout';
import { sanitizeHtml } from '@/lib/sanitize-html';
import { dashboard } from '@/routes';
import { index as blogIndex, show as blogShow } from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { type PostDetail as Post } from '@/types/content';
import { Head } from '@inertiajs/react';

interface Props {
    post: Post;
}

export default function BlogShow({ post }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Blog', href: blogIndex().url },
        { title: post.title, href: blogShow({ post: post.slug }).url },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={post.title} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <p className="text-sm text-muted-foreground">
                    <TextLink href={blogIndex().url}>Back to blog</TextLink>
                </p>
                <article>
                    <h1 className="mb-2 text-2xl font-mono font-semibold tracking-tight">
                        {post.title}
                    </h1>
                    <p className="mb-6 text-sm text-muted-foreground">
                        {post.published_at
                            ? new Date(post.published_at).toLocaleDateString(
                                  'en-CA',
                                  {
                                      year: 'numeric',
                                      month: 'long',
                                      day: 'numeric',
                                  },
                              )
                            : null}
                        {post.author ? ` · ${post.author.name}` : null}
                    </p>
                    <div
                        className="prose prose-neutral dark:prose-invert max-w-none"
                        dangerouslySetInnerHTML={{
                            __html: sanitizeHtml(post.content),
                        }}
                    />
                </article>
            </div>
        </AppLayout>
    );
}
