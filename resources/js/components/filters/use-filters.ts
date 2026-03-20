import { router } from "@inertiajs/react";
import { useCallback, useMemo, useRef } from "react";
import type { ActiveFilters, FilterValue } from "./types";

function parseFilterParam(raw: string): FilterValue {
    const match = raw.match(/^([a-z_]+):(.+)$/i);
    if (match) {
        return { operator: match[1], values: match[2].split(",") };
    }
    return { operator: "", values: raw.split(",") };
}

function navigate(params: Record<string, unknown>, partialReloadKey?: string) {
    const url = new URL(window.location.href);
    const sp = new URLSearchParams(url.search);

    for (const [k, v] of Object.entries(params)) {
        if (v === null || v === undefined || v === "") sp.delete(k);
        else sp.set(k, String(v));
    }

    const options: Record<string, unknown> = { preserveScroll: true };
    if (partialReloadKey) {
        options.only = [partialReloadKey];
    }

    router.get(url.pathname + "?" + sp.toString(), {}, options);
}

export function useFilters(
    serverFilters: Record<string, unknown>,
    options?: { prefix?: string; debounceMs?: number; partialReloadKey?: string },
) {
    const prefix = options?.prefix;
    const debounceMs = options?.debounceMs ?? 0;
    const partialReloadKey = options?.partialReloadKey;
    const debounceTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const activeFilters = useMemo<ActiveFilters>(() => {
        const result: ActiveFilters = {};
        for (const [key, raw] of Object.entries(serverFilters)) {
            if (raw !== null && raw !== undefined && raw !== "") {
                result[key] = parseFilterParam(String(raw));
            }
        }
        return result;
    }, [serverFilters]);

    const filterKey = prefix ? `${prefix}_filter` : "filter";
    const pageKey = prefix ? `${prefix}_page` : "page";

    const debouncedNavigate = useCallback(
        (params: Record<string, unknown>) => {
            if (debounceTimer.current) clearTimeout(debounceTimer.current);
            if (debounceMs > 0) {
                debounceTimer.current = setTimeout(() => {
                    navigate(params, partialReloadKey);
                }, debounceMs);
            } else {
                navigate(params, partialReloadKey);
            }
        },
        [debounceMs, partialReloadKey],
    );

    const setFilter = useCallback(
        (columnId: string, operator: string, values: string[]) => {
            if (values.length === 0) {
                debouncedNavigate({ [`${filterKey}[${columnId}]`]: null, [pageKey]: null });
                return;
            }
            debouncedNavigate({
                [`${filterKey}[${columnId}]`]: `${operator}:${values.join(",")}`,
                [pageKey]: null,
            });
        },
        [filterKey, pageKey, debouncedNavigate],
    );

    const clearFilter = useCallback((columnId: string) => {
        navigate({ [`${filterKey}[${columnId}]`]: null, [pageKey]: null }, partialReloadKey);
    }, [filterKey, pageKey, partialReloadKey]);

    const clearAllFilters = useCallback(() => {
        const params: Record<string, unknown> = { [pageKey]: null };
        const url = new URL(window.location.href);
        for (const k of url.searchParams.keys()) {
            if (k.startsWith(`${filterKey}[`)) params[k] = null;
        }
        navigate(params, partialReloadKey);
    }, [filterKey, pageKey, partialReloadKey]);

    return { activeFilters, setFilter, clearFilter, clearAllFilters };
}
