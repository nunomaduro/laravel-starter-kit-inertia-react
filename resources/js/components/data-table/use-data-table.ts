import { router } from '@inertiajs/react';
import {
    type ColumnDef,
    type ColumnFiltersState,
    type ColumnOrderState,
    type RowSelectionState,
    type SortingState,
    type VisibilityState,
    getCoreRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { useCallback, useEffect, useState } from 'react';
import type { DataTableColumnDef, DataTableResponse } from './types';

const STORAGE_PREFIX = 'dt-columns-';
const ORDER_STORAGE_PREFIX = 'dt-column-order-';

function loadVisibility(
    tableName: string,
    columns: DataTableColumnDef[],
): VisibilityState {
    const stored = localStorage.getItem(STORAGE_PREFIX + tableName);
    if (stored) {
        try {
            return JSON.parse(stored) as VisibilityState;
        } catch {
            // fall through
        }
    }
    const visibility: VisibilityState = {};
    for (const col of columns) {
        visibility[col.id] = col.visible;
    }
    return visibility;
}

function saveVisibility(tableName: string, visibility: VisibilityState) {
    localStorage.setItem(
        STORAGE_PREFIX + tableName,
        JSON.stringify(visibility),
    );
}

function loadColumnOrder(
    tableName: string,
    columns: DataTableColumnDef[],
): ColumnOrderState {
    const stored = localStorage.getItem(ORDER_STORAGE_PREFIX + tableName);
    if (stored) {
        try {
            return JSON.parse(stored) as ColumnOrderState;
        } catch {
            // fall through
        }
    }
    return columns.map((col) => col.id);
}

function saveColumnOrder(tableName: string, order: ColumnOrderState) {
    localStorage.setItem(
        ORDER_STORAGE_PREFIX + tableName,
        JSON.stringify(order),
    );
}

interface UseDataTableOptions<TData> {
    tableData: DataTableResponse<TData>;
    tableName: string;
    columnDefs: ColumnDef<TData>[];
    prefix?: string;
}

function getSearchParam(prefix?: string): string {
    const key = prefix ? `${prefix}_search` : 'search';
    if (typeof window === 'undefined') return '';
    return new URLSearchParams(window.location.search).get(key) ?? '';
}

export function useDataTable<TData>({
    tableData,
    tableName,
    columnDefs,
    prefix,
}: UseDataTableOptions<TData>) {
    const { meta } = tableData;
    const searchKey = prefix ? `${prefix}_search` : 'search';

    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>(
        () => loadVisibility(tableName, tableData.columns),
    );

    const [columnOrder, setColumnOrder] = useState<ColumnOrderState>(() =>
        loadColumnOrder(tableName, tableData.columns),
    );

    const [sorting, setSorting] = useState<SortingState>(() =>
        meta.sorts.map((s) => ({ id: s.id, desc: s.direction === 'desc' })),
    );

    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [rowSelection, setRowSelection] = useState<RowSelectionState>({});

    const navigate = useCallback((params: Record<string, unknown>) => {
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);

        for (const [key, value] of Object.entries(params)) {
            if (value === null || value === undefined || value === '') {
                searchParams.delete(key);
            } else {
                searchParams.set(key, String(value));
            }
        }

        router.get(
            currentUrl.pathname + '?' + searchParams.toString(),
            {},
            { preserveScroll: true },
        );
    }, []);

    const handleSort = useCallback(
        (columnId: string, multi: boolean) => {
            const currentSorts = meta.sorts;

            if (multi) {
                const newSorts = [...currentSorts];
                const idx = newSorts.findIndex((s) => s.id === columnId);
                if (idx === -1) {
                    newSorts.push({ id: columnId, direction: 'asc' });
                } else if (newSorts[idx].direction === 'asc') {
                    newSorts[idx] = { ...newSorts[idx], direction: 'desc' };
                } else {
                    newSorts.splice(idx, 1);
                }
                const param = newSorts
                    .map((s) => (s.direction === 'desc' ? `-${s.id}` : s.id))
                    .join(',');
                navigate({ sort: param || null, page: null });
            } else {
                const existing = currentSorts.find((s) => s.id === columnId);
                let newSort: string | null;
                if (existing?.direction === 'asc') {
                    newSort = '-' + columnId;
                } else if (existing?.direction === 'desc') {
                    newSort = null;
                } else {
                    newSort = columnId;
                }
                navigate({ sort: newSort, page: null });
            }
        },
        [meta.sorts, navigate],
    );

    const handlePageChange = useCallback(
        (page: number) => {
            navigate({ page: page > 1 ? page : null });
        },
        [navigate],
    );

    const handlePerPageChange = useCallback(
        (perPage: number) => {
            navigate({ per_page: perPage, page: null });
        },
        [navigate],
    );

    const handleApplyQuickView = useCallback(
        (params: Record<string, unknown>) => {
            const currentUrl = new URL(window.location.href);
            const searchParams = new URLSearchParams();

            for (const [key, value] of Object.entries(params)) {
                if (value !== null && value !== undefined && value !== '') {
                    searchParams.set(key, String(value));
                }
            }

            const perPage = currentUrl.searchParams.get('per_page');
            if (perPage) {
                searchParams.set('per_page', perPage);
            }

            router.get(
                currentUrl.pathname + '?' + searchParams.toString(),
                {},
                { preserveScroll: true },
            );
        },
        [],
    );

    const applyColumns = useCallback(
        (columnIds: string[]) => {
            const newVisibility: VisibilityState = {};
            for (const col of tableData.columns) {
                newVisibility[col.id] = columnIds.includes(col.id);
            }
            setColumnVisibility(newVisibility);
            setColumnOrder(columnIds);
        },
        [tableData.columns],
    );

    const table = useReactTable<TData>({
        data: tableData.data,
        columns: columnDefs,
        getRowId: (row, index) => {
            const id = (row as { id?: unknown }).id;
            return id != null ? String(id) : `row-${index}`;
        },
        manualPagination: true,
        manualSorting: true,
        manualFiltering: true,
        pageCount: meta.lastPage,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        onColumnVisibilityChange: setColumnVisibility,
        onColumnOrderChange: setColumnOrder,
        onRowSelectionChange: setRowSelection,
        enableRowSelection: true,
        getCoreRowModel: getCoreRowModel(),
        initialState: {
            columnPinning: {
                left: columnDefs.some((c) => c.id === '_select')
                    ? ['_select']
                    : [],
                right: columnDefs.some((c) => c.id === '_actions')
                    ? ['_actions']
                    : [],
            },
        },
        state: {
            sorting,
            columnFilters,
            columnVisibility,
            columnOrder,
            rowSelection,
            pagination: {
                pageIndex: meta.currentPage - 1,
                pageSize: meta.perPage,
            },
        },
    });

    useEffect(() => {
        saveVisibility(tableName, columnVisibility);
    }, [tableName, columnVisibility]);

    useEffect(() => {
        saveColumnOrder(tableName, columnOrder);
    }, [tableName, columnOrder]);

    const handleApplyCustomSearch = useCallback((search: string) => {
        const currentUrl = new URL(window.location.href);
        router.get(currentUrl.pathname + search, {}, { preserveScroll: true });
    }, []);

    const handleGlobalSearch = useCallback(
        (value: string) => {
            navigate({ [searchKey]: value || null, page: null });
        },
        [navigate, searchKey],
    );

    const currentSearch = getSearchParam(prefix);

    return {
        table,
        meta,
        columnVisibility,
        columnOrder,
        setColumnOrder,
        rowSelection,
        setRowSelection,
        applyColumns,
        handleSort,
        handlePageChange,
        handlePerPageChange,
        handleApplyQuickView,
        handleApplyCustomSearch,
        handleGlobalSearch,
        currentSearch,
        searchKey,
    };
}
