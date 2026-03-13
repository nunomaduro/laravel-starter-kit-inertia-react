import type { DataTableTranslations } from "./i18n";

export interface DataTableColumnDef {
    id: string;
    label: string;
    type: "text" | "number" | "date" | "option" | "multiOption" | "boolean" | "image" | "badge" | "currency" | "percentage" | "link" | "email" | "phone" | "icon" | "color" | "select";
    sortable: boolean;
    filterable: boolean;
    visible: boolean;
    options?: { label: string; value: string; variant?: string }[] | null;
    min?: number | null;
    max?: number | null;
    icon?: string | null;
    searchThreshold?: number | null;
    group?: string | null;
    editable?: boolean;
    currency?: string | null;
    locale?: string | null;
    /** Summary aggregation type: 'sum' | 'count' | 'avg' | 'min' | 'max' | 'range' */
    summary?: string | null;
    /** Whether this column supports boolean toggle switch */
    toggleable?: boolean;
    /** Responsive priority (lower = hidden first on small screens). null = always visible */
    responsivePriority?: number | null;
    /** Internal database column name or dot-notation relation path (e.g., 'user.name') */
    internalName?: string | null;
    /** Relationship name for eager loading (e.g., 'user', 'category.parent') */
    relation?: string | null;
    /** Text displayed before the cell value (e.g., '$', '#') */
    prefix?: string | null;
    /** Text displayed after the cell value (e.g., 'kg', '%', ' items') */
    suffix?: string | null;
    /** Tooltip text on hover. Can be a static string or a column ID to read from the row */
    tooltip?: string | null;
    /** Description text below the column header label */
    description?: string | null;
    /** CSS line-clamp value to truncate long text (e.g., 2 = max 2 lines) */
    lineClamp?: number | null;
    /** Map of values to icon names for icon columns */
    iconMap?: Record<string, string> | null;
    /** Map of values to color classes for conditional cell coloring */
    colorMap?: Record<string, string> | null;
    /** Options for inline select dropdown editing */
    selectOptions?: { label: string; value: string }[] | null;
    /** Whether to render the cell value as HTML (sanitized) */
    html?: boolean;
    /** Whether to render the cell value as Markdown */
    markdown?: boolean;
    /** Display array values as a bulleted list */
    bulleted?: boolean;
    /** Array of column IDs to display vertically (stacked) in this cell */
    stacked?: string[] | null;
    /** Whether this is a row index column (auto-incrementing row number) */
    rowIndex?: boolean;
    /** Column ID that holds the avatar/image URL for composite avatar+text display */
    avatarColumn?: string | null;
    /** Whether this column has a dynamic (closure-based) suffix resolved server-side */
    hasDynamicSuffix?: boolean;
    /** Column IDs this computed column depends on */
    computedFrom?: string[] | null;
    /** Number of columns this cell should span */
    colSpan?: number | null;
    /** Whether this column should auto-size row heights based on content */
    autoHeight?: boolean;
    /** valueGetter: column ID or dot-path to derive value for sorting/filtering */
    valueGetter?: string | null;
    /** valueFormatter: format string for display (e.g., '{value} USD') */
    valueFormatter?: string | null;
    /** Whether this column has an inline header filter */
    headerFilter?: boolean;
    /** Column ID that holds per-row currency code, overriding the static currency */
    currencyColumn?: string | null;
    /** Sparkline chart type: 'line' | 'bar' | null */
    sparkline?: string | null;
    /** Tree data parent column reference */
    treeParent?: string | null;
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
    paginationType?: "standard" | "simple" | "cursor";
    nextCursor?: string | null;
    prevCursor?: string | null;
}

/** Server-side table configuration passed from backend */
export interface DataTableConfig {
    detailRowEnabled?: boolean;
    /** Display mode for detail rows: 'inline' (expandable row), 'modal' (dialog), or 'drawer' (side sheet) */
    detailDisplay?: "inline" | "modal" | "drawer";
    softDeletesEnabled?: boolean;
    pollingInterval?: number;
    persistState?: boolean;
    deferLoading?: boolean;
    asyncFilterColumns?: string[];
    cascadingFilters?: Record<string, string>;
    rules?: DataTableRule[];
    /** Whether tree data (hierarchical rows) is enabled */
    treeDataEnabled?: boolean;
    /** Column ID holding the parent reference for tree data */
    treeDataParentKey?: string;
    /** Column ID used as tree node label */
    treeDataLabelKey?: string;
    /** Whether infinite scroll is enabled */
    infiniteScroll?: boolean;
    /** Whether pivot mode is available */
    pivotEnabled?: boolean;
    /** Pivot configuration */
    pivotConfig?: { rowFields?: string[]; columnFields?: string[]; valueField?: string; aggregation?: string };
    /** Default layout mode set from server: 'table' | 'grid' | 'cards' | 'kanban' */
    defaultLayout?: "table" | "grid" | "cards" | "kanban";
}

/** Conditional row/cell styling rule */
export interface DataTableRule {
    column: string;
    operator: string;
    value: unknown;
    row?: { class?: string };
    cell?: { class?: string };
}

export type DataTableDensity = "compact" | "comfortable" | "spacious";

export type DataTableLayoutMode = "table" | "grid" | "cards" | "kanban";

/** Conditional formatting rule created by the user via the rules builder UI */
export interface DataTableConditionalFormatRule {
    id: string;
    column: string;
    operator: "gt" | "gte" | "lt" | "lte" | "eq" | "neq" | "contains" | "between" | "empty" | "notEmpty";
    value: unknown;
    value2?: unknown;
    style: {
        backgroundColor?: string;
        textColor?: string;
        fontWeight?: "normal" | "bold";
        icon?: string;
    };
}

/** User presence for collaborative indicators */
export interface DataTablePresenceUser {
    id: string | number;
    name: string;
    avatar?: string;
    color?: string;
    activeRow?: string | number | null;
}

/** Faceted filter option with count */
export interface DataTableFacetedOption {
    value: string;
    label: string;
    count: number;
    icon?: string;
}

/** Column statistics computed from data */
export interface DataTableColumnStats {
    count: number;
    nullCount: number;
    uniqueCount: number;
    min?: number;
    max?: number;
    sum?: number;
    avg?: number;
    median?: number;
    distribution?: { bucket: string; count: number }[];
}

export interface DataTableOptions {
    quickViews: boolean;
    customQuickViews: boolean;
    exports: boolean;
    filters: boolean;
    columnVisibility: boolean;
    columnOrdering: boolean;
    columnResizing: boolean;
    stickyHeader: boolean;
    globalSearch: boolean;
    loading: boolean;
    keyboardNavigation: boolean;
    printable: boolean;
    density: boolean;
    copyCell: boolean;
    contextMenu: boolean;
    virtualScrolling: boolean;
    rowGrouping: boolean;
    rowReorder: boolean;
    batchEdit: boolean;
    searchHighlight: boolean;
    undoRedo: boolean;
    columnPinning: boolean;
    persistSelection: boolean;
    shortcutsOverlay: boolean;
    exportProgress: boolean;
    emptyStateIllustration: boolean;
    /** Enable cell flashing when values change (via polling/realtime) */
    cellFlashing: boolean;
    /** Enable status bar with aggregate info for selected cells */
    statusBar: boolean;
    /** Enable multi-row clipboard paste for editable cells */
    clipboardPaste: boolean;
    /** Enable drag-to-fill for editable cells */
    dragToFill: boolean;
    /** Enable inline header filters below column headers */
    headerFilters: boolean;
    /** Enable infinite scroll instead of pagination */
    infiniteScroll: boolean;
    /** Enable column auto-sizing (double-click resize handle to fit content) */
    columnAutoSize: boolean;
    /** Enable column virtualization (only render visible columns) */
    columnVirtualization: boolean;
    /** Enable cell range selection (spreadsheet-like) */
    cellRangeSelection: boolean;
    /** Enable AutoSizer (responsive container sizing) */
    autoSizer: boolean;
    /** Enable CellMeasurer (content-based variable row heights) */
    cellMeasurer: boolean;
    /** Enable scroll-aware simplified rendering during fast scroll */
    scrollAwareRendering: boolean;
    /** Enable window scroller (table scrolls with browser window) */
    windowScroller: boolean;
    /** Enable directional overscan (more rows pre-rendered in scroll direction) */
    directionalOverscan: boolean;
    /** Enable layout switcher (table/grid/cards/kanban) in toolbar */
    layoutSwitcher: boolean;
    /** Enable column statistics popover on header click */
    columnStatistics: boolean;
    /** Enable conditional formatting rules builder */
    conditionalFormatting: boolean;
    /** Enable faceted filters with counts */
    facetedFilters: boolean;
    /** Enable collaborative presence indicators */
    presence: boolean;
    /** Enable spreadsheet-mode Tab/Enter cell navigation */
    spreadsheetMode: boolean;
    /** Enable kanban board view */
    kanbanView: boolean;
    /** Enable master/detail nested sub-tables */
    masterDetail: boolean;
    /** Enable integrated charts (chart from column/selection) */
    integratedCharts: boolean;
    /** Enable find & replace (Ctrl+F with match highlighting) */
    findReplace: boolean;
}

/** Server-driven action visibility rule */
export interface DataTableActionRule {
    column: string;
    operator: string;
    value: unknown;
}

/** Analytics KPI card definition from the server */
export interface DataTableAnalytic {
    label: string;
    value: number | string;
    /** Format: 'number' | 'currency' | 'percentage' | 'text' */
    format?: string | null;
    /** Percentage change (positive = up, negative = down) */
    change?: number | null;
    /** Text before value (e.g., '$') */
    prefix?: string | null;
    /** Text after value (e.g., ' units') */
    suffix?: string | null;
    /** Tailwind color class (e.g., 'text-emerald-600') */
    color?: string | null;
    /** Icon name or emoji */
    icon?: string | null;
    /** Description text below the value */
    description?: string | null;
}

export interface DataTableResponse<TData = object> {
    data: TData[];
    columns: DataTableColumnDef[];
    quickViews: DataTableQuickView[];
    meta: DataTableMeta;
    exportUrl?: string | null;
    footer?: Record<string, unknown> | null;
    /** URL for fetching all row IDs matching current filters (server-side selection) */
    selectAllUrl?: string | null;
    /** Full-dataset summary aggregations */
    summary?: Record<string, unknown> | null;
    /** Server-side table configuration */
    config?: DataTableConfig | null;
    /** URL for boolean toggle updates */
    toggleUrl?: string | null;
    /** Enum filter options resolved from PHP enums */
    enumOptions?: Record<string, { label: string; value: string }[]> | null;
    /** URL for row reorder PATCH requests */
    reorderUrl?: string | null;
    /** URL for data import POST requests */
    importUrl?: string | null;
    /** Column ID to group rows by */
    groupByColumn?: string | null;
    /** Pinned rows displayed at top of table */
    pinnedTopRows?: Record<string, unknown>[] | null;
    /** Pinned rows displayed at bottom of table */
    pinnedBottomRows?: Record<string, unknown>[] | null;
    /** Server-driven action visibility rules: action label → condition */
    actionRules?: Record<string, DataTableActionRule> | null;
    /** Analytics KPI cards displayed above the table */
    analytics?: DataTableAnalytic[] | null;
    /** Faceted filter counts: column ID → { optionValue → count } */
    facetedCounts?: Record<string, Record<string, number>> | null;
}

export interface DataTableConfirmOptions {
    title?: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: "default" | "destructive";
}

export interface DataTableAction<TData> {
    /** Stable action identifier used for server-driven action rules matching. Falls back to label if not set. */
    id?: string;
    label: string;
    icon?: string;
    onClick: (row: TData) => void;
    variant?: "default" | "destructive";
    visible?: (row: TData) => boolean;
    confirm?: boolean | DataTableConfirmOptions;
    /** Nested actions displayed as a submenu group */
    group?: DataTableAction<TData>[];
    /** Form fields for a modal form action */
    form?: DataTableFormField[];
}

/** Form field definition for forms-in-actions */
export interface DataTableFormField {
    name: string;
    label: string;
    type: "text" | "number" | "select" | "textarea" | "checkbox";
    options?: { label: string; value: string }[];
    required?: boolean;
    placeholder?: string;
    defaultValue?: unknown;
}

/** Header action button displayed in the table toolbar */
export interface DataTableHeaderAction {
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
    onClick: () => void;
    variant?: "default" | "outline" | "destructive" | "ghost";
}

export interface DataTableBulkAction<TData> {
    id: string;
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
    variant?: "default" | "destructive";
    disabled?: (rows: TData[]) => boolean;
    onClick: (rows: TData[]) => void;
    confirm?: boolean | DataTableConfirmOptions;
}

export interface DataTableProps<TData extends object> {
    className?: string;
    tableData: DataTableResponse<TData>;
    tableName: string;
    /** Prefix for URL query params — enables multiple tables per page */
    prefix?: string;
    actions?: DataTableAction<TData>[];
    bulkActions?: DataTableBulkAction<TData>[];
    renderCell?: (columnId: string, value: unknown, row: TData) => React.ReactNode | undefined;
    renderHeader?: Record<string, React.ReactNode>;
    renderFooterCell?: (columnId: string, value: unknown) => React.ReactNode | undefined;
    /** Custom filter component per column */
    renderFilter?: Record<string, (value: unknown, onChange: (value: unknown) => void) => React.ReactNode>;
    rowClassName?: (row: TData) => string;
    /** Add custom data-* attributes to each row */
    rowDataAttributes?: (row: TData) => Record<string, string>;
    groupClassName?: Record<string, string>;
    options?: Partial<DataTableOptions>;
    translations?: Partial<DataTableTranslations>;
    /** Called when a row is clicked (non-link navigation) */
    onRowClick?: (row: TData) => void;
    /** Returns an href for each row, making rows clickable links */
    rowLink?: (row: TData) => string;
    /** Custom empty state when table has no data */
    emptyState?: React.ReactNode;
    /** Debounce delay in ms for filter/search inputs (default: 300) */
    debounceMs?: number;
    /** Inertia partial reload key for optimized data fetching */
    partialReloadKey?: string;
    /** Called when a cell is edited inline — return a promise to save */
    onInlineEdit?: (row: TData, columnId: string, value: unknown) => Promise<void> | void;
    /** Laravel Echo channel name for real-time updates */
    realtimeChannel?: string;
    /** Laravel Echo event name to listen for (default: '.updated') */
    realtimeEvent?: string;
    /** Render function for detail/expandable row content */
    renderDetailRow?: (row: TData) => React.ReactNode;
    /** Selection mode: 'checkbox' (default) or 'radio' (single select) */
    selectionMode?: "checkbox" | "radio";
    /** Called when rows are reordered via drag and drop */
    onReorder?: (ids: unknown[], newPositions: number[]) => Promise<void> | void;
    /** Called when a batch edit is applied to multiple rows */
    onBatchEdit?: (rows: TData[], columnId: string, value: unknown) => Promise<void> | void;
    /** Custom SVG/component for the empty state illustration */
    emptyStateIllustration?: React.ReactNode;
    /** Slot overrides for composability */
    slots?: {
        toolbar?: React.ReactNode;
        /** Custom analytics/charts section above the table. Receives data and columns for building custom visualizations. */
        analytics?: React.ReactNode | ((props: { data: TData[]; columns: import("./types").DataTableColumnDef[]; analytics: import("./types").DataTableAnalytic[] }) => React.ReactNode);
        beforeTable?: React.ReactNode;
        afterTable?: React.ReactNode;
        pagination?: React.ReactNode;
        /** Custom status bar content */
        statusBar?: React.ReactNode;
    };
    /** Called whenever table state changes (sorting, filtering, pagination, visibility, etc.) */
    onStateChange?: (state: import("./use-data-table").DataTableState) => void;
    /** Called when a new row is created inline */
    onRowCreate?: (data: Record<string, unknown>) => Promise<void> | void;
    /** Breakpoint in px below which the mobile card layout is shown (0 = disabled) */
    mobileBreakpoint?: number;
    /** JSX children — use <DataTable.Column> for declarative column configuration */
    children?: React.ReactNode;
    /** Header action buttons displayed in the table toolbar */
    headerActions?: DataTableHeaderAction[];
    /** Column IDs available for user-selectable grouping */
    groupByOptions?: string[];
    /** Callback when user changes the group-by column */
    onGroupByChange?: (columnId: string | null) => void;
    /** Row spanning configuration: maps column ID to a function that returns span count */
    rowSpan?: Record<string, (row: TData, index: number, allRows: TData[]) => number>;
    /** Column spanning configuration: maps column ID to a function that returns span count */
    columnSpan?: Record<string, (row: TData) => number>;
    /** Called when cells are pasted from clipboard */
    onClipboardPaste?: (startRowIndex: number, startColumnId: string, data: string[][]) => Promise<void> | void;
    /** Called when drag-to-fill is completed */
    onDragToFill?: (columnId: string, value: unknown, targetRowIds: unknown[]) => Promise<void> | void;
    /** Called when cell range is selected */
    onCellRangeSelect?: (startRow: number, startCol: string, endRow: number, endCol: string) => void;
    /** Imperative API ref for programmatic control */
    apiRef?: React.MutableRefObject<DataTableApiRef | null>;
    /** Called when infinite scroll needs more data */
    onLoadMore?: (page: number) => Promise<void> | void;
    /** Whether more data is available for infinite scroll */
    hasMore?: boolean;
    /** Sparkline data: maps column ID to sparkline values per row.
     * Accepts either an array indexed by row position (legacy) or an object keyed by row ID (preferred).
     * Row ID keying is stable across sort/pagination changes. */
    sparklineData?: Record<string, number[][] | Record<string | number, number[]>>;
    /** AI assistant prompt handler: receives natural language query, returns filter/sort config */
    onAiQuery?: (query: string) => Promise<{ filters?: Record<string, unknown>; sort?: string } | void>;
    /** Base URL for AI endpoints (e.g., '/data-table/ai/products'). Enables built-in AI features. */
    aiBaseUrl?: string;
    /** Enable Thesys C1 generative UI visualizations (requires Thesys API key on the backend). */
    aiThesys?: boolean;
    /** Pivot mode state callback */
    onPivotChange?: (config: { rowFields: string[]; columnFields: string[]; valueField: string; aggregation: string }) => void;
    /** Column ID to use as kanban lane grouping (e.g., 'status') */
    kanbanColumnId?: string;
    /** Called when a kanban card is moved to a different lane */
    onKanbanMove?: (rowId: unknown, fromLane: string, toLane: string) => Promise<void> | void;
    /** Faceted filter counts from server: column ID → option value → count */
    facetedCounts?: Record<string, Record<string, number>>;
    /** Laravel Echo presence channel name for collaborative indicators */
    presenceChannel?: string;
    /** Current user info for presence tracking */
    currentUser?: DataTablePresenceUser;
    /** Image column ID to use as card thumbnail in grid/card layouts */
    cardImageColumn?: string;
    /** Column ID to use as card title in grid/card layouts */
    cardTitleColumn?: string;
    /** Column ID to use as card subtitle in grid/card layouts */
    cardSubtitleColumn?: string;
    /** Render function for master/detail nested sub-table content */
    renderMasterDetail?: (row: TData) => React.ReactNode;
    /** Called when find & replace executes a replacement */
    onFindReplace?: (rowId: unknown, columnId: string, oldValue: unknown, newValue: unknown) => Promise<void> | void;
    /** Chart types available for integrated charts (default: ['bar', 'line', 'pie']) */
    chartTypes?: ("bar" | "line" | "pie" | "doughnut")[];
}

/** Imperative API ref for programmatic grid control.
 * Methods return Promise<void> so callers can await completion. */
export interface DataTableApiRef {
    /** Scroll to a specific row by index */
    scrollToRow: (index: number, alignment?: "start" | "center" | "end" | "auto") => Promise<void>;
    /** Auto-size all columns to fit content */
    autosizeColumns: () => Promise<void>;
    /** Trigger export programmatically */
    triggerExport: (format: "xlsx" | "csv" | "pdf") => Promise<void>;
    /** Reset all filters */
    resetFilters: () => Promise<void>;
    /** Get current table state */
    getState: () => Record<string, unknown>;
    /** Focus a specific cell */
    focusCell: (rowIndex: number, columnId: string) => Promise<void>;
}
