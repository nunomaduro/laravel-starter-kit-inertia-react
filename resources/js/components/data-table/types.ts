export interface DataTableColumnDef {
    id: string;
    label: string;
    type:
        | 'text'
        | 'number'
        | 'date'
        | 'option'
        | 'multiOption'
        | 'boolean'
        | 'email'
        | 'image'
        | 'badge'
        | 'currency'
        | 'percentage'
        | 'link'
        | 'phone'
        | 'icon'
        | 'color'
        | 'select';
    sortable: boolean;
    filterable: boolean;
    visible: boolean;
    editable?: boolean;
    toggleable?: boolean;
    options?: { label: string; value: string; variant?: string }[] | null;
    min?: number | null;
    max?: number | null;
    icon?: string | null;
    searchThreshold?: number | null;
    group?: string | null;
    summary?: string | null;
    description?: string | null;
    responsivePriority?: number | null;
    lineClamp?: number | null;
    prefix?: string | null;
    suffix?: string | null;
    tooltip?: string | null;
    selectOptions?: { label: string; value: string }[] | null;
    rowIndex?: boolean;
}

export interface DataTableQuickView {
    id: string;
    label: string;
    params: Record<string, unknown>;
    icon?: string | null;
    active: boolean;
    columns?: string[] | null;
}

export interface DataTableSort {
    id: string;
    direction: 'asc' | 'desc';
}

export interface DataTableMeta {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
    sorts: DataTableSort[];
    filters: Record<string, unknown>;
}

export interface DataTableOptions {
    quickViews: boolean;
    customQuickViews: boolean;
    exports: boolean;
    filters: boolean;
    columnVisibility: boolean;
    columnOrdering: boolean;
    stickyHeader?: boolean;
    globalSearch?: boolean;
    columnResizing?: boolean;
    keyboardNavigation?: boolean;
    printable?: boolean;
    density?: boolean | 'compact' | 'comfortable' | 'spacious';
    copyCell?: boolean;
    contextMenu?: boolean;
    rowGrouping?: boolean;
    rowReorder?: boolean;
    batchEdit?: boolean;
    searchHighlight?: boolean;
    undoRedo?: boolean;
    columnPinning?: boolean;
    persistSelection?: boolean;
    shortcutsOverlay?: boolean;
    exportProgress?: boolean;
    emptyStateIllustration?: boolean;
}

export interface DataTableConfig {
    detailRowEnabled?: boolean;
    detailDisplay?: 'inline' | 'modal' | 'drawer';
    softDeletesEnabled?: boolean;
    pollingInterval?: number;
    persistState?: boolean;
    deferLoading?: boolean;
    asyncFilterColumns?: string[];
    cascadingFilters?: Record<string, string>;
    rules?: Array<{
        column: string;
        operator: string;
        value: unknown;
        row?: { class?: string };
        cell?: { class?: string };
    }>;
}

export interface DataTableResponse<TData = object> {
    data: TData[];
    columns: DataTableColumnDef[];
    quickViews: DataTableQuickView[];
    meta: DataTableMeta;
    exportUrl?: string | null;
    footer?: Record<string, unknown> | null;
    selectAllUrl?: string | null;
    summary?: Record<string, unknown> | null;
    config?: DataTableConfig | null;
    toggleUrl?: string | null;
    enumOptions?: Record<string, { label: string; value: string }[]> | null;
    reorderUrl?: string | null;
    importUrl?: string | null;
    groupByColumn?: string | null;
}

export interface DataTableConfirmOptions {
    title?: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'destructive';
}

export interface DataTableAction<TData> {
    label: string;
    icon?: string;
    onClick: (row: TData) => void;
    variant?: 'default' | 'destructive';
    visible?: (row: TData) => boolean;
    confirm?: boolean | DataTableConfirmOptions;
    group?: DataTableAction<TData>[];
}

export interface DataTableBulkAction<TData> {
    id: string;
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
    variant?: 'default' | 'destructive';
    disabled?: (rows: TData[]) => boolean;
    onClick: (rows: TData[]) => void;
    confirm?: boolean | DataTableConfirmOptions;
}

export interface DataTableHeaderAction {
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
    onClick: () => void;
    variant?: 'default' | 'outline' | 'destructive' | 'ghost';
}

export interface DataTableProps<TData extends object> {
    className?: string;
    tableData: DataTableResponse<TData>;
    tableName: string;
    /** Column IDs for server-side global search. When set, a search input is shown. */
    searchableColumns?: string[];
    /** Debounce delay in ms for global search and filter inputs. */
    debounceMs?: number;
    /** Custom content when table has no data. */
    emptyState?: React.ReactNode;
    /** URL for each row; row becomes a link (cmd/ctrl+click opens in new tab). */
    rowLink?: (row: TData) => string;
    /** Optional URL param prefix for multiple tables on one page. */
    prefix?: string;
    /** Inertia partial reload key (e.g. 'tableData') for efficient refetches. */
    partialReloadKey?: string;
    actions?: DataTableAction<TData>[];
    bulkActions?: DataTableBulkAction<TData>[];
    headerActions?: DataTableHeaderAction[];
    renderCell?: (
        columnId: string,
        value: unknown,
        row: TData,
    ) => React.ReactNode | undefined;
    renderHeader?: Record<string, React.ReactNode>;
    renderFooterCell?: (
        columnId: string,
        value: unknown,
    ) => React.ReactNode | undefined;
    /** Render expandable detail row content. Requires config.detailRowEnabled. */
    renderDetailRow?: (row: TData, detail?: Record<string, unknown>) => React.ReactNode;
    rowClassName?: (row: TData) => string;
    groupClassName?: Record<string, string>;
    options?: Partial<DataTableOptions>;
    /** Callback when a cell is inline-edited. */
    onInlineEdit?: (row: TData, columnId: string, value: unknown) => void | Promise<void>;
    /** Callback when rows are reordered (ids and new positions). */
    onReorder?: (ids: unknown[], newPositions: number[]) => void | Promise<void>;
    /** Callback when table state changes (sort, filter, page, etc.). */
    onStateChange?: (state: Record<string, unknown>) => void;
    /** Layout slots for full usage examples. */
    slots?: {
        toolbar?: React.ReactNode;
        beforeTable?: React.ReactNode;
        afterTable?: React.ReactNode;
        pagination?: React.ReactNode;
    };
    /** Width in px below which table switches to mobile card layout (0 = disabled). */
    mobileBreakpoint?: number;
    /** Override translation strings (full usage example). */
    translations?: Partial<DataTableTranslations>;
}

/** Translation overrides for data table UI strings. */
export interface DataTableTranslations {
    noData?: string;
    loading?: string;
    search?: string;
    export?: string;
    import?: string;
    selectAll?: string;
    selectAllMatching?: (count: number) => string;
    clearFilters?: string;
    density?: string;
    keyboardShortcuts?: string;
    [key: string]: string | ((...args: never[]) => string) | undefined;
}
