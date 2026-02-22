export interface DataTableColumnDef {
    id: string;
    label: string;
    type: "text" | "number" | "date" | "option" | "multiOption" | "boolean";
    sortable: boolean;
    filterable: boolean;
    visible: boolean;
    options?: { label: string; value: string }[] | null;
    min?: number | null;
    max?: number | null;
    icon?: string | null;
    searchThreshold?: number | null;
    group?: string | null;
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
}

export interface DataTableResponse<TData = object> {
    data: TData[];
    columns: DataTableColumnDef[];
    quickViews: DataTableQuickView[];
    meta: DataTableMeta;
    exportUrl?: string | null;
    footer?: Record<string, unknown> | null;
}

export interface DataTableAction<TData> {
    label: string;
    icon?: string;
    onClick: (row: TData) => void;
    variant?: "default" | "destructive";
    visible?: (row: TData) => boolean;
}

export interface DataTableBulkAction<TData> {
    id: string;
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
    variant?: "default" | "destructive";
    disabled?: (rows: TData[]) => boolean;
    onClick: (rows: TData[]) => void;
}

export interface DataTableProps<TData extends object> {
    className?: string;
    tableData: DataTableResponse<TData>;
    tableName: string;
    actions?: DataTableAction<TData>[];
    bulkActions?: DataTableBulkAction<TData>[];
    renderCell?: (columnId: string, value: unknown, row: TData) => React.ReactNode | undefined;
    renderHeader?: Record<string, React.ReactNode>;
    renderFooterCell?: (columnId: string, value: unknown) => React.ReactNode | undefined;
    rowClassName?: (row: TData) => string;
    groupClassName?: Record<string, string>;
    options?: Partial<DataTableOptions>;
}
