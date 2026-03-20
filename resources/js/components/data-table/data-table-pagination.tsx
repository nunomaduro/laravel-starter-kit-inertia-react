import { Button } from "@/components/ui/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from "lucide-react";
import { useCallback } from "react";
import { router } from "@inertiajs/react";
import type { DataTableTranslations } from "./i18n";
import type { DataTableMeta } from "./types";

interface DataTablePaginationProps {
    meta: DataTableMeta;
    onPageChange: (page: number) => void;
    onPerPageChange: (perPage: number) => void;
    onCursorChange?: (cursor: string | null) => void;
    t: DataTableTranslations;
    /** Prefix for multi-table URL params */
    prefix?: string;
    /** Inertia partial reload key for prefetching */
    partialReloadKey?: string;
    /** Enable Inertia v2 prefetching on hover (default: true) */
    prefetch?: boolean;
}

/**
 * Build the URL for a given page, preserving current query params.
 */
function buildPageUrl(page: number, prefix?: string): string {
    const url = new URL(window.location.href);
    const pageKey = prefix ? `${prefix}_page` : "page";
    if (page > 1) {
        url.searchParams.set(pageKey, String(page));
    } else {
        url.searchParams.delete(pageKey);
    }
    return url.pathname + "?" + url.searchParams.toString();
}

export function DataTablePagination({
    meta,
    onPageChange,
    onPerPageChange,
    onCursorChange,
    t,
    prefix,
    partialReloadKey,
    prefetch: enablePrefetch = true,
}: DataTablePaginationProps) {
    const isCursor = meta.paginationType === "cursor";
    const isSimple = meta.paginationType === "simple";

    // Inertia v2 prefetch: preload the next/prev page on hover for instant navigation
    const prefetchPage = useCallback(
        (page: number) => {
            if (!enablePrefetch || typeof router.prefetch !== "function") return;
            const url = buildPageUrl(page, prefix);
            const options: Record<string, unknown> = { method: "get" };
            if (partialReloadKey) {
                options.only = [partialReloadKey];
            }
            try {
                router.prefetch(url, options as Parameters<typeof router.prefetch>[1]);
            } catch {
                // router.prefetch may not be available in older Inertia versions
            }
        },
        [enablePrefetch, prefix, partialReloadKey],
    );

    return (
        <div className="flex items-center justify-between px-3 py-2">
            <div className="text-sm text-muted-foreground">
                {!isCursor && !isSimple && (() => {
                    const from = (meta.currentPage - 1) * meta.perPage + 1;
                    const to = Math.min(meta.currentPage * meta.perPage, meta.total);
                    return t.showingRange ? t.showingRange(from, to, meta.total) : t.totalResults(meta.total);
                })()}
            </div>
            <div className="flex items-center gap-6 lg:gap-8">
                <div className="flex items-center gap-2">
                    <p className="text-sm font-medium">{t.rowsPerPage}</p>
                    <Select
                        value={String(meta.perPage)}
                        onValueChange={(value) => onPerPageChange(Number(value))}
                    >
                        <SelectTrigger className="h-8 w-[70px]">
                            <SelectValue placeholder={String(meta.perPage)} />
                        </SelectTrigger>
                        <SelectContent side="top">
                            {[10, 25, 50, 100].map((size) => (
                                <SelectItem key={size} value={String(size)}>
                                    {size}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                {!isCursor && (
                    <div className="flex w-[100px] items-center justify-center text-sm font-medium">
                        {t.pageOf(meta.currentPage, meta.lastPage)}
                    </div>
                )}
                {isCursor ? (
                    <div className="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-8 w-8"
                            onClick={() => onCursorChange?.(meta.prevCursor ?? null)}
                            disabled={!meta.prevCursor}
                            aria-label="Previous page"
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-8 w-8"
                            onClick={() => onCursorChange?.(meta.nextCursor ?? null)}
                            disabled={!meta.nextCursor}
                            aria-label="Next page"
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                ) : (
                    <div className="flex items-center gap-1">
                        {!isSimple && (
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8"
                                onClick={() => onPageChange(1)}
                                onMouseEnter={() => meta.currentPage > 1 && prefetchPage(1)}
                                disabled={meta.currentPage <= 1}
                                aria-label="First page"
                            >
                                <ChevronsLeft className="h-4 w-4" />
                            </Button>
                        )}
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-8 w-8"
                            onClick={() => onPageChange(meta.currentPage - 1)}
                            onMouseEnter={() => meta.currentPage > 1 && prefetchPage(meta.currentPage - 1)}
                            disabled={meta.currentPage <= 1}
                            aria-label="Previous page"
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            className="h-8 w-8"
                            onClick={() => onPageChange(meta.currentPage + 1)}
                            onMouseEnter={() => meta.currentPage < meta.lastPage && prefetchPage(meta.currentPage + 1)}
                            disabled={meta.currentPage >= meta.lastPage}
                            aria-label="Next page"
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                        {!isSimple && (
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8"
                                onClick={() => onPageChange(meta.lastPage)}
                                onMouseEnter={() => meta.currentPage < meta.lastPage && prefetchPage(meta.lastPage)}
                                disabled={meta.currentPage >= meta.lastPage}
                                aria-label="Last page"
                            >
                                <ChevronsRight className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}
