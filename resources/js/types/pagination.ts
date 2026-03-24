/**
 * Shared pagination types used across multiple pages.
 *
 * PaginatedData<T> — used by CRM, HR, and other modules for server-side pagination.
 * PaginatorLink    — used by blog, changelog, and other Blade-style paginated responses.
 */

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

export interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}
