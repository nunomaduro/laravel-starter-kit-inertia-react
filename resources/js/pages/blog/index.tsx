import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { index as blogIndex, show as blogShow } from '@/routes/blog';
import { type BreadcrumbItem } from '@/types';
import { type Post } from '@/types/content';
import { type PaginatorLink } from '@/types/pagination';
import { Head, Link } from '@inertiajs/react';

interface Props {
    posts: {
        data: Post[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        links: PaginatorLink[];
    };
}

export default function BlogIndex({ posts }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Blog', href: blogIndex().url },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Blog" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-mono font-semibold tracking-tight">Blog</h1>
                {posts.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16 text-center">
                        <p className="text-sm font-medium text-muted-foreground">
                            No posts yet
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Check back soon for new articles.
                        </p>
                    </div>
                ) : (
                    <ul className="space-y-6">
                        {posts.data.map((post) => (
                            <li key={post.id}>
                                <Link
                                    href={blogShow({ post: post.slug }).url}
                                    className="block rounded-lg border bg-card p-4 transition-colors hover:bg-muted/50"
                                >
                                    <h2 className="font-medium text-foreground">
                                        {post.title}
                                    </h2>
                                    {post.excerpt && (
                                        <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                            {post.excerpt}
                                        </p>
                                    )}
                                    <p className="mt-2 text-xs text-muted-foreground">
                                        {post.published_at
                                            ? new Date(
                                                  post.published_at,
                                              ).toLocaleDateString('en-CA', {
                                                  year: 'numeric',
                                                  month: 'long',
                                                  day: 'numeric',
                                              })
                                            : null}
                                        {post.author
                                            ? ` · ${post.author.name}`
                                            : null}
                                    </p>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
                {(posts.prev_page_url || posts.next_page_url) && (
                    <nav
                        className="mt-8 flex items-center justify-center gap-4"
                        aria-label="Pagination"
                    >
                        {posts.prev_page_url ? (
                            <Link
                                href={posts.prev_page_url}
                                className="text-sm font-medium text-foreground underline underline-offset-4"
                            >
                                Previous
                            </Link>
                        ) : null}
                        <span className="text-sm text-muted-foreground">
                            Page {posts.current_page} of {posts.last_page}
                        </span>
                        {posts.next_page_url ? (
                            <Link
                                href={posts.next_page_url}
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
