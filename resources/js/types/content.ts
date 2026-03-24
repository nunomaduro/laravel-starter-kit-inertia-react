/**
 * Shared content types used across blog, changelog, and help pages.
 */

export interface Author {
    id: number;
    name: string;
}

/** Blog post for list views (no body content). */
export interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    published_at: string | null;
    author?: Author;
}

/** Blog post for detail views (includes full content). */
export interface PostDetail extends Post {
    content: string;
}

export interface ChangelogEntry {
    id: number;
    title: string;
    description: string;
    version: string | null;
    type: string;
    released_at: string | null;
}

export interface HelpArticle {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    category: string;
}

export interface DashboardRecord {
    id: number;
    name: string;
    is_default: boolean;
    refresh_interval: number | null;
    updated_at: string;
}

/** Extended dashboard record for edit views (includes Puck JSON layout). */
export interface DashboardRecordEditable extends Omit<DashboardRecord, 'updated_at'> {
    puck_json: Record<string, unknown>;
}
