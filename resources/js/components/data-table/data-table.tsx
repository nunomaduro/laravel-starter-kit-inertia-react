import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import {
    type Column,
    type ColumnDef,
    type ColumnOrderState,
    type Table as TanStackTable,
    type VisibilityState,
    flexRender,
} from '@tanstack/react-table';
import {
    Calendar,
    Check,
    ChevronDown,
    ChevronRight,
    CircleDot,
    Copy,
    Download,
    EllipsisVertical,
    FileSpreadsheet,
    FileText,
    GripVertical,
    Hash,
    List,
    Search,
    SlidersHorizontal,
    ToggleLeft,
    Type,
    Upload,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Filters } from '../filters/filters';
import type { FilterColumn } from '../filters/types';
import { DataTableColumnHeader } from './data-table-column-header';
import { DataTablePagination } from './data-table-pagination';
import { DataTableQuickViews } from './data-table-quick-views';
import { DataTableRowActions } from './data-table-row-actions';
import type {
    DataTableBulkAction,
    DataTableColumnDef,
    DataTableConfirmOptions,
    DataTableOptions,
    DataTableProps,
    DataTableTranslations,
} from './types';
import { useDataTable } from './use-data-table';

interface ColumnMeta {
    type?: string;
    group?: string | null;
}

function getColumnMeta(columnDef: { meta?: unknown }): ColumnMeta {
    return (columnDef.meta as ColumnMeta) ?? {};
}

function getBulkConfirmOptions(
    action: DataTableBulkAction<unknown>,
    fallbackDescription: string,
): DataTableConfirmOptions {
    if (typeof action.confirm === 'object' && action.confirm !== null) {
        return action.confirm;
    }
    return {
        title: 'Confirm',
        description: fallbackDescription,
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        variant: 'default',
    };
}

function buildExportUrl(
    baseUrl: string,
    format: string,
    visibleColumns?: string[],
): string {
    const currentParams = new URL(window.location.href).searchParams;
    const exportUrl = new URL(baseUrl, window.location.origin);
    for (const [key, value] of currentParams.entries()) {
        exportUrl.searchParams.set(key, value);
    }
    exportUrl.searchParams.set('format', format);
    if (visibleColumns?.length) {
        exportUrl.searchParams.set('columns', visibleColumns.join(','));
    }
    return exportUrl.toString();
}

function CopyableCell({
    valueToCopy,
    children,
}: {
    valueToCopy: string;
    children: React.ReactNode;
}) {
    return (
        <span className="group/cell relative inline-flex items-center gap-1">
            {children}
            <Button
                variant="ghost"
                size="icon"
                className="h-6 w-6 shrink-0 opacity-0 group-hover/cell:opacity-100"
                onClick={(e) => {
                    e.stopPropagation();
                    void navigator.clipboard.writeText(valueToCopy);
                }}
                aria-label="Copy"
            >
                <Copy className="h-3 w-3" />
            </Button>
        </span>
    );
}

function getColumnPinningProps<T>(column: Column<T, unknown>) {
    const isPinned = column.getIsPinned();
    if (!isPinned) return { style: {} as React.CSSProperties, className: '' };
    return {
        style: {
            position: 'sticky' as const,
            left:
                isPinned === 'left'
                    ? `${column.getStart('left')}px`
                    : undefined,
            right:
                isPinned === 'right'
                    ? `${column.getAfter('right')}px`
                    : undefined,
            zIndex: 1,
        } as React.CSSProperties,
        className: cn(
            isPinned === 'left' &&
                column.getIsLastColumn('left') &&
                'shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]',
            isPinned === 'right' &&
                column.getIsFirstColumn('right') &&
                'shadow-[-2px_0_4px_-2px_rgba(0,0,0,0.08)]',
        ),
    };
}

/** Opaque background for pinned cells in data rows */
function getPinnedCellBg(
    isPinned: string | false,
    isSelected: boolean,
): React.CSSProperties {
    if (!isPinned) return {};
    if (isSelected) {
        return {
            backgroundImage:
                'linear-gradient(oklch(from var(--color-primary) l c h / 0.05), oklch(from var(--color-primary) l c h / 0.05))',
        };
    }
    return {};
}

function DataTableToolbar<TData>({
    tableData,
    table,
    tableName,
    columnVisibility,
    columnOrder,
    applyColumns,
    onReorderColumns,
    handleApplyQuickView,
    handleApplyCustomSearch,
    resolvedOptions,
    headerActions,
    importUrl,
    partialReloadKey,
    importInputRef,
    density,
    onDensityChange,
    densityLabel = 'Density',
}: {
    tableData: {
        quickViews: import('./types').DataTableQuickView[];
        exportUrl?: string | null;
        columns: DataTableColumnDef[];
    };
    table: TanStackTable<TData>;
    tableName: string;
    columnVisibility: VisibilityState;
    columnOrder: ColumnOrderState;
    applyColumns: (columnIds: string[]) => void;
    onReorderColumns: (order: ColumnOrderState) => void;
    handleApplyQuickView: (params: Record<string, unknown>) => void;
    handleApplyCustomSearch: (search: string) => void;
    resolvedOptions: DataTableOptions;
    headerActions?: import('./types').DataTableHeaderAction[];
    importUrl?: string | null;
    partialReloadKey?: string;
    importInputRef?: React.RefObject<HTMLInputElement | null>;
    density?: 'compact' | 'comfortable' | 'spacious';
    onDensityChange?: (d: 'compact' | 'comfortable' | 'spacious') => void;
    densityLabel?: string;
}) {
    return (
                <div className="flex flex-wrap items-center gap-2 px-4">
                    {headerActions?.map((action, i) => {
                        const Icon = action.icon;
                        return (
                            <Button
                                key={i}
                                variant={action.variant ?? 'default'}
                                size="sm"
                                className="h-8"
                                onClick={action.onClick}
                            >
                                {Icon && <Icon className="mr-1.5 h-4 w-4" />}
                                {action.label}
                            </Button>
                        );
                    })}
                    {importUrl && importInputRef && (
                        <>
                            <input
                                ref={importInputRef}
                                type="file"
                                accept=".csv,.xlsx,.xls"
                                className="hidden"
                                onChange={async (e) => {
                                    const file = e.target.files?.[0];
                                    if (!file) return;
                                    const form = new FormData();
                                    form.append('file', file);
                                    try {
                                        const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
                                        await fetch(importUrl, {
                                            method: 'POST',
                                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                            body: form,
                                        });
                                        router.reload(partialReloadKey ? { only: [partialReloadKey] } : undefined);
                                    } finally {
                                        e.target.value = '';
                                    }
                                }}
                            />
                            <Button
                                variant="outline"
                                size="sm"
                                className="h-8"
                                onClick={() => importInputRef.current?.click()}
                            >
                                <Upload className="mr-1.5 h-4 w-4" />
                                Import
                            </Button>
                        </>
                    )}
                    {(resolvedOptions.quickViews ||
                        resolvedOptions.customQuickViews) && (
                <DataTableQuickViews
                    quickViews={
                        resolvedOptions.quickViews ? tableData.quickViews : []
                    }
                    tableName={tableName}
                    columnVisibility={columnVisibility}
                    columnOrder={columnOrder}
                    allColumns={tableData.columns}
                    onSelect={handleApplyQuickView}
                    onApplyCustom={handleApplyCustomSearch}
                    onApplyColumns={applyColumns}
                    onApplyColumnOrder={onReorderColumns}
                    enableCustom={resolvedOptions.customQuickViews}
                />
            )}
                            {onDensityChange && (
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="outline" size="sm" className="h-8">
                                            <List className="mr-1.5 h-4 w-4" />
                                            {densityLabel}
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem onClick={() => onDensityChange('compact')}>
                                            {density === 'compact' && <Check className="mr-2 h-4 w-4" />}
                                            Compact
                                        </DropdownMenuItem>
                                        <DropdownMenuItem onClick={() => onDensityChange('comfortable')}>
                                            {density === 'comfortable' && <Check className="mr-2 h-4 w-4" />}
                                            Comfortable
                                        </DropdownMenuItem>
                                        <DropdownMenuItem onClick={() => onDensityChange('spacious')}>
                                            {density === 'spacious' && <Check className="mr-2 h-4 w-4" />}
                                            Spacious
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            )}
                            {resolvedOptions.exports && tableData.exportUrl && (
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="outline" size="sm" className="h-8">
                                            <Download className="h-4 w-4" />
                                            Export
                                        </Button>
                                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuLabel>Export format</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <a
                                href={buildExportUrl(
                                    tableData.exportUrl,
                                    'xlsx',
                                    table
                                        .getVisibleLeafColumns()
                                        .filter((c) => c.getCanHide())
                                        .map((c) => c.id),
                                )}
                            >
                                <FileSpreadsheet className="mr-2 h-4 w-4" />
                                Excel (.xlsx)
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a
                                href={buildExportUrl(
                                    tableData.exportUrl,
                                    'csv',
                                    table
                                        .getVisibleLeafColumns()
                                        .filter((c) => c.getCanHide())
                                        .map((c) => c.id),
                                )}
                            >
                                <FileText className="mr-2 h-4 w-4" />
                                CSV (.csv)
                            </a>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
            {(resolvedOptions.columnVisibility ||
                resolvedOptions.columnOrdering) && (
                <ColumnsDropdown
                    table={table}
                    tableColumns={tableData.columns}
                    columnOrder={columnOrder}
                    onReorder={onReorderColumns}
                    showVisibility={resolvedOptions.columnVisibility}
                    showOrdering={resolvedOptions.columnOrdering}
                />
            )}
        </div>
    );
}

function ColumnsDropdown<TData>({
    table,
    tableColumns,
    columnOrder,
    onReorder,
    showVisibility,
    showOrdering,
}: {
    table: TanStackTable<TData>;
    tableColumns: DataTableColumnDef[];
    columnOrder: ColumnOrderState;
    onReorder: (order: ColumnOrderState) => void;
    showVisibility: boolean;
    showOrdering: boolean;
}) {
    const dragItemRef = useRef<string | null>(null);
    const dragOverRef = useRef<string | null>(null);
    const [dragging, setDragging] = useState<string | null>(null);
    const [dragOverId, setDragOverId] = useState<string | null>(null);
    const [reordering, setReordering] = useState(false);

    const isReorderActive = reordering && showOrdering;

    const handleDragStart = useCallback((columnId: string) => {
        dragItemRef.current = columnId;
        setDragging(columnId);
    }, []);

    const handleDragEnd = useCallback(() => {
        const from = dragItemRef.current;
        const to = dragOverRef.current;
        if (from && to && from !== to) {
            const newOrder = [...columnOrder];
            const fromIdx = newOrder.indexOf(from);
            newOrder.splice(fromIdx, 1);
            const toIdx = newOrder.indexOf(to);
            if (toIdx !== -1) {
                newOrder.splice(toIdx, 0, from);
                onReorder(newOrder);
            }
        }
        dragItemRef.current = null;
        dragOverRef.current = null;
        setDragging(null);
        setDragOverId(null);
    }, [columnOrder, onReorder]);

    const hideable = table.getAllLeafColumns().filter((c) => c.getCanHide());
    const colDefMap = new Map(tableColumns.map((c) => [c.id, c]));

    const orderedHideable = useMemo(() => {
        if (!showOrdering) return hideable;
        return [...hideable].sort((a, b) => {
            const ai = columnOrder.indexOf(a.id);
            const bi = columnOrder.indexOf(b.id);
            return (ai === -1 ? 999 : ai) - (bi === -1 ? 999 : bi);
        });
    }, [hideable, columnOrder, showOrdering]);

    const ungrouped = orderedHideable.filter(
        (c) => !colDefMap.get(c.id)?.group,
    );
    const groups = new Map<string, typeof hideable>();
    for (const col of orderedHideable) {
        const g = colDefMap.get(col.id)?.group;
        if (g) {
            if (!groups.has(g)) groups.set(g, []);
            groups.get(g)!.push(col);
        }
    }

    function renderItem(
        column: ReturnType<TanStackTable<TData>['getAllLeafColumns']>[number],
    ) {
        const isOver = dragOverId === column.id && dragging !== column.id;
        return (
            <div
                key={column.id}
                className={cn(
                    'flex items-center gap-1 rounded-sm px-2 py-1.5 text-sm',
                    dragging === column.id && 'opacity-40',
                    isOver && 'border-t-2 border-t-primary',
                )}
                draggable={isReorderActive}
                onDragStart={() => handleDragStart(column.id)}
                onDragOver={(e) => {
                    e.preventDefault();
                    dragOverRef.current = column.id;
                    setDragOverId(column.id);
                }}
                onDragEnd={handleDragEnd}
            >
                {isReorderActive && (
                    <GripVertical className="h-3.5 w-3.5 shrink-0 cursor-grab text-muted-foreground/50" />
                )}
                {showVisibility ? (
                    <label className="flex flex-1 cursor-pointer items-center gap-2">
                        <Checkbox
                            checked={column.getIsVisible()}
                            onCheckedChange={(value) =>
                                column.toggleVisibility(!!value)
                            }
                        />
                        <span className="select-none">
                            {column.columnDef.header as string}
                        </span>
                    </label>
                ) : (
                    <span className="flex-1 select-none">
                        {column.columnDef.header as string}
                    </span>
                )}
            </div>
        );
    }

    return (
        <DropdownMenu
            onOpenChange={(open) => {
                if (!open) {
                    setReordering(false);
                    setDragging(null);
                    setDragOverId(null);
                }
            }}
        >
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="h-8">
                    <SlidersHorizontal className="h-4 w-4" />
                    Columns
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                align="end"
                className="max-h-[400px] w-60 overflow-y-auto"
            >
                <div className="flex items-center justify-between px-2 py-1.5">
                    <span className="text-sm font-semibold">Columns</span>
                    {showOrdering && (
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-6 px-2 text-xs"
                            onPointerDown={(e) => e.stopPropagation()}
                            onClick={(e) => {
                                e.preventDefault();
                                setReordering((r) => !r);
                            }}
                        >
                            {reordering ? 'Done' : 'Reorder'}
                        </Button>
                    )}
                </div>
                <DropdownMenuSeparator />
                {ungrouped.map((column) => renderItem(column))}
                {[...groups.entries()].map(([group, cols]) => (
                    <DropdownMenuSub key={group}>
                        <DropdownMenuSubTrigger
                            className={
                                'flex-row-reverse justify-end gap-2 [&_svg]:ml-0 [&_svg]:rotate-180'
                            }
                        >
                            {group}
                        </DropdownMenuSubTrigger>
                        <DropdownMenuSubContent>
                            {cols.map((column) => renderItem(column))}
                        </DropdownMenuSubContent>
                    </DropdownMenuSub>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

const TYPE_ICON_MAP: Record<string, FilterColumn['icon']> = {
    text: Type,
    number: Hash,
    date: Calendar,
    option: CircleDot,
    multiOption: List,
    boolean: ToggleLeft,
};

function buildFilterColumns(columns: DataTableColumnDef[]): FilterColumn[] {
    return columns
        .filter((col) => col.filterable)
        .map((col) => {
            const type =
                col.type === 'multiOption'
                    ? ('option' as const)
                    : (col.type as FilterColumn['type']);
            return {
                id: col.id,
                label: col.label,
                type,
                icon: TYPE_ICON_MAP[col.type],
                ...(col.options ? { options: col.options } : {}),
                ...(col.searchThreshold != null
                    ? { searchThreshold: col.searchThreshold }
                    : {}),
            };
        });
}

const DEFAULT_TRANSLATIONS: DataTableTranslations = {
    noData: 'No data',
    loading: 'Loading…',
    search: 'Search',
    export: 'Export',
    import: 'Import',
    selectAll: 'Select all',
    selectAllMatching: (count: number) => `Select all ${count} matching`,
    clearFilters: 'Clear filters',
    density: 'Density',
    keyboardShortcuts: 'Keyboard shortcuts',
};

export function DataTable<TData extends object>({
    className,
    tableData,
    tableName,
    searchableColumns,
    debounceMs = 300,
    emptyState,
    rowLink,
    prefix,
    partialReloadKey,
    actions,
    bulkActions,
    headerActions,
    renderCell,
    renderHeader,
    renderFooterCell,
    renderDetailRow,
    rowClassName,
    groupClassName,
    options: optionsOverride,
    onInlineEdit,
    onReorder,
    onStateChange,
    slots,
    mobileBreakpoint = 0,
    translations: translationsOverride,
}: DataTableProps<TData>) {
    const t = useMemo(
        () => ({ ...DEFAULT_TRANSLATIONS, ...translationsOverride }),
        [translationsOverride],
    );
    const resolvedOptions = useMemo<DataTableOptions>(
        () => ({
            quickViews: true,
            customQuickViews: true,
            exports: true,
            filters: true,
            columnVisibility: true,
            columnOrdering: true,
            stickyHeader: false,
            globalSearch: true,
            ...optionsOverride,
        }),
        [optionsOverride],
    );

    const showGlobalSearch =
        (searchableColumns?.length ?? 0) > 0 &&
        (resolvedOptions.globalSearch ?? true);

    const hasBulkActions = bulkActions && bulkActions.length > 0;
    const detailRowEnabled =
        tableData.config?.detailRowEnabled === true && renderDetailRow != null;
    const [expandedRowId, setExpandedRowId] = useState<unknown>(null);
    const [detailCache, setDetailCache] = useState<Record<string, Record<string, unknown>>>({});
    const [detailOverlayRow, setDetailOverlayRow] = useState<{ id: unknown; data: TData } | null>(null);
    const [confirmingBulkAction, setConfirmingBulkAction] = useState<
        DataTableBulkAction<TData> | null
    >(null);
    const importInputRef = useRef<HTMLInputElement>(null);
    const detailDisplay = tableData.config?.detailDisplay ?? 'inline';

    const columnDefs = useMemo<ColumnDef<TData>[]>(() => {
        const toggleUrl = tableData.toggleUrl ?? null;
        const copyCell = optionsOverride?.copyCell === true;
        function makeLeafCol(col: DataTableColumnDef): ColumnDef<TData> {
            return {
                id: col.id,
                ...(col.rowIndex
                    ? { accessorFn: (_row: TData, index: number) => index + 1 }
                    : { accessorKey: col.id }),
                header: col.label,
                enableHiding: true,
                meta: { type: col.type, group: col.group ?? null },
                cell: ({ row }) => {
                    const value = row.getValue(col.id);
                    let cellContent: React.ReactNode;
                    if (renderCell) {
                        const custom = renderCell(col.id, value, row.original);
                        if (custom !== undefined) {
                            cellContent = custom;
                            return copyCell ? (
                                <CopyableCell valueToCopy={value != null ? String(value) : ''}>
                                    {cellContent}
                                </CopyableCell>
                            ) : cellContent;
                        }
                    }
                    if ((value === null || value === undefined) && !col.toggleable) {
                        cellContent = <span className="text-muted-foreground">—</span>;
                        return cellContent;
                    }
                    if (col.toggleable && toggleUrl && typeof value === 'boolean') {
                        const rowId = (row.original as { id?: unknown }).id;
                        if (rowId == null) return <span className="text-muted-foreground">—</span>;
                        const url = `${toggleUrl}/${String(rowId)}`;
                        return (
                            <Switch
                                checked={value}
                                onCheckedChange={async (checked) => {
                                    try {
                                        const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
                                        await fetch(url, {
                                            method: 'PATCH',
                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                            body: JSON.stringify({ column: col.id, value: !!checked }),
                                        });
                                        router.reload(partialReloadKey ? { only: [partialReloadKey] } : undefined);
                                    } catch {
                                        // ignore
                                    }
                                }}
                                onClick={(e) => e.stopPropagation()}
                                aria-label={`Toggle ${col.label}`}
                            />
                        );
                    }
                    if (typeof value === 'boolean') {
                        cellContent = value ? (
                            <Check className="inline-flex h-4 items-center rounded-full font-medium text-green-800 shadow-green-100 dark:text-green-400 dark:shadow-green-900/30">
                                Yes
                            </Check>
                        ) : (
                            <X className="inline-flex h-4 items-center rounded-full font-medium text-red-800 shadow-red-100 dark:text-red-400 dark:shadow-red-900/30">
                                No
                            </X>
                        );
                        return copyCell ? (
                            <CopyableCell valueToCopy={value ? 'Yes' : 'No'}>{cellContent}</CopyableCell>
                        ) : cellContent;
                    }
                    if (col.type === 'image' && typeof value === 'string') {
                        return (
                            <img
                                src={value}
                                alt=""
                                className="h-8 w-8 rounded-full object-cover"
                            />
                        );
                    }
                    if (col.type === 'number' && typeof value === 'number') {
                        cellContent = (
                            <span className="tabular-nums">
                                {value.toLocaleString('fr-TN')}
                            </span>
                        );
                        return copyCell ? (
                            <CopyableCell valueToCopy={String(value)}>{cellContent}</CopyableCell>
                        ) : cellContent;
                    }
                    if (col.type === 'email' && typeof value === 'string') {
                        cellContent = (
                            <a
                                href={`mailto:${value}`}
                                className="text-primary hover:underline"
                                onClick={(e) => e.stopPropagation()}
                            >
                                {value}
                            </a>
                        );
                        return copyCell ? (
                            <CopyableCell valueToCopy={value}>{cellContent}</CopyableCell>
                        ) : cellContent;
                    }
                    if (col.type === 'badge' && (typeof value === 'string' || typeof value === 'number')) {
                        const opt = col.options?.find(
                            (o) => String(o.value) === String(value),
                        );
                        const variant = (opt?.variant as 'default' | 'secondary' | 'destructive' | 'outline') ?? 'secondary';
                        cellContent = (
                            <Badge variant={variant} className="capitalize">
                                {opt?.label ?? String(value)}
                            </Badge>
                        );
                        return copyCell ? (
                            <CopyableCell valueToCopy={String(value)}>{cellContent}</CopyableCell>
                        ) : cellContent;
                    }
                    cellContent = String(value);
                    return copyCell ? (
                        <CopyableCell valueToCopy={String(value ?? '')}>{cellContent}</CopyableCell>
                    ) : cellContent;
                },
            };
        }

        const result: ColumnDef<TData>[] = [];

        if (hasBulkActions) {
            result.push({
                id: '_select',
                header: ({ table: t }) => (
                    <Checkbox
                        checked={
                            t.getIsAllPageRowsSelected() ||
                            (t.getIsSomePageRowsSelected() && 'indeterminate')
                        }
                        onCheckedChange={(value) =>
                            t.toggleAllPageRowsSelected(!!value)
                        }
                        aria-label="Select all"
                    />
                ),
                cell: ({ row }) => (
                    <Checkbox
                        checked={row.getIsSelected()}
                        onCheckedChange={(value) => row.toggleSelected(!!value)}
                        aria-label="Select row"
                    />
                ),
                enableHiding: false,
            });
        }

        if (detailRowEnabled) {
            const useOverlay = detailDisplay === 'drawer' || detailDisplay === 'modal';

            async function fetchDetail(rowId: unknown) {
                if (rowId == null || detailCache[String(rowId)]) return;
                try {
                    const url = `${window.location.origin}/data-table/detail/${tableName}/${rowId}`;
                    const res = await fetch(url);
                    const json = await res.json();
                    setDetailCache((prev) => ({
                        ...prev,
                        [String(rowId)]: json.detail ?? {},
                    }));
                } catch {
                    setDetailCache((prev) => ({
                        ...prev,
                        [String(rowId)]: {},
                    }));
                }
            }

            result.push({
                id: '_expand',
                header: '',
                cell: ({ row }) => {
                    const rowId = (row.original as { id?: unknown }).id;
                    const isExpanded =
                        !useOverlay &&
                        expandedRowId != null &&
                        String(expandedRowId) === String(rowId);
                    const isOverlayOpen =
                        useOverlay &&
                        detailOverlayRow !== null &&
                        String(detailOverlayRow.id) === String(rowId);

                    function getAriaLabel(): string {
                        if (useOverlay) {
                            return isOverlayOpen ? 'Close' : 'View details';
                        }
                        return isExpanded ? 'Collapse' : 'Expand';
                    }

                    return (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-7 w-7"
                            onClick={async (e) => {
                                e.stopPropagation();
                                if (useOverlay) {
                                    if (isOverlayOpen) {
                                        setDetailOverlayRow(null);
                                        return;
                                    }
                                    setDetailOverlayRow({ id: rowId, data: row.original });
                                    await fetchDetail(rowId);
                                    return;
                                }
                                if (isExpanded) {
                                    setExpandedRowId(null);
                                    return;
                                }
                                setExpandedRowId(rowId);
                                await fetchDetail(rowId);
                            }}
                            aria-label={getAriaLabel()}
                        >
                            {!useOverlay && isExpanded ? (
                                <ChevronDown className="h-4 w-4" />
                            ) : (
                                <ChevronRight className="h-4 w-4" />
                            )}
                        </Button>
                    );
                },
                enableHiding: false,
            });
        }

        const processedGroups = new Set<string>();

        for (const col of tableData.columns) {
            if (!col.group) {
                result.push(makeLeafCol(col));
            } else if (!processedGroups.has(col.group)) {
                processedGroups.add(col.group);
                const groupCols = tableData.columns.filter(
                    (c) => c.group === col.group,
                );
                result.push({
                    id: `_group_${col.group}`,
                    header: col.group,
                    columns: groupCols.map(makeLeafCol),
                });
            }
        }

        if (actions && actions.length > 0) {
            result.push({
                id: '_actions',
                header: '',
                enableHiding: false,
                cell: ({ row }) => (
                    <DataTableRowActions row={row.original} actions={actions} />
                ),
            });
        }

        return result;
    }, [tableData.columns, tableData.toggleUrl, tableName, actions, hasBulkActions, renderCell, detailRowEnabled, expandedRowId, detailCache, detailOverlayRow, detailDisplay, partialReloadKey, optionsOverride?.copyCell]);

    const {
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
    } = useDataTable<TData>({
        tableData,
        tableName,
        columnDefs,
        prefix,
    });

    const pollingInterval = tableData.config?.pollingInterval ?? 0;
    useEffect(() => {
        if (pollingInterval <= 0 || !partialReloadKey) return;
        const id = setInterval(() => {
            router.reload({ only: [partialReloadKey], preserveState: true });
        }, pollingInterval * 1000);
        return () => clearInterval(id);
    }, [pollingInterval, partialReloadKey]);

    const [searchInputValue, setSearchInputValue] = useState(currentSearch);
    useEffect(() => {
        setSearchInputValue(currentSearch);
    }, [currentSearch]);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    useEffect(() => {
        if (!showGlobalSearch) return;
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            debounceRef.current = null;
            if (searchInputValue !== currentSearch) {
                handleGlobalSearch(searchInputValue);
            }
        }, debounceMs);
        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current);
        };
    }, [
        searchInputValue,
        currentSearch,
        debounceMs,
        showGlobalSearch,
        handleGlobalSearch,
    ]);

    const filterColumns = useMemo(
        () => buildFilterColumns(tableData.columns),
        [tableData.columns],
    );

    const selectedRows = useMemo(
        () => table.getFilteredSelectedRowModel().rows.map((r) => r.original),
        [rowSelection, tableData.data],
    );

    const DENSITY_VALUES = ['compact', 'comfortable', 'spacious'] as const;
    type Density = (typeof DENSITY_VALUES)[number];
    const [density, setDensity] = useState<Density>(() => {
        const d = optionsOverride?.density;
        return typeof d === 'string' && DENSITY_VALUES.includes(d as Density) ? (d as Density) : 'comfortable';
    });

    const densityRowClass = {
        compact: 'py-1',
        comfortable: 'py-2',
        spacious: 'py-3',
    }[density];

    const paginationKeys = useMemo(
        () => new Set(['page', 'per_page', 'sort', prefix ? `${prefix}_search` : 'search']),
        [prefix],
    );

    const hasActiveFilters = useMemo(() => {
        if (typeof window === 'undefined') return false;
        const params = new URLSearchParams(window.location.search);
        for (const k of paginationKeys) params.delete(k);
        return params.toString().length > 0;
    }, [paginationKeys, meta.filters]);

    const clearAllFilters = useCallback(() => {
        const url = new URL(window.location.href);
        for (const k of [...url.searchParams.keys()]) {
            if (!paginationKeys.has(k)) url.searchParams.delete(k);
        }
        const qs = url.searchParams.toString();
        router.get(url.pathname + (qs ? `?${qs}` : ''), {}, { preserveScroll: true });
    }, [paginationKeys]);

    const [selectingAll, setSelectingAll] = useState(false);
    const handleSelectAllMatching = useCallback(async () => {
        const url = tableData.selectAllUrl;
        if (!url) return;
        setSelectingAll(true);
        try {
            const current = new URL(window.location.href);
            const target = new URL(url, window.location.origin);
            current.searchParams.forEach((v, k) => target.searchParams.set(k, v));
            const res = await fetch(target.toString());
            const json = (await res.json()) as { ids?: unknown[] };
            const ids = json?.ids ?? [];
            const next: Record<string, boolean> = {};
            ids.forEach((id) => { next[String(id)] = true; });
            setRowSelection(next);
        } finally {
            setSelectingAll(false);
        }
    }, [tableData.selectAllUrl, setRowSelection]);

    const showSelectAllMatching =
        tableData.selectAllUrl &&
        meta.total > (meta.perPage ?? 10) &&
        hasBulkActions;

    return (
        <div className="space-y-2">
            {slots?.toolbar}
            <div className="flex flex-wrap items-center justify-between gap-2 py-1">
                <div className="flex flex-1 flex-wrap items-center gap-2 pl-6">
                    {showGlobalSearch && (
                        <div className="relative w-full max-w-sm min-w-[200px]">
                            <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder={typeof t.search === 'string' ? t.search : 'Search…'}
                                value={searchInputValue}
                                onChange={(e) =>
                                    setSearchInputValue(e.target.value)
                                }
                                className="h-8 pl-8"
                            />
                        </div>
                    )}
                    {resolvedOptions.filters && (
                        <Filters
                            columns={filterColumns}
                            serverFilters={
                                meta.filters as Record<string, unknown>
                            }
                        />
                    )}
                    {showSelectAllMatching && (
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-8"
                            disabled={selectingAll}
                            onClick={handleSelectAllMatching}
                        >
                            {selectingAll
                                ? t.loading
                                : typeof t.selectAllMatching === 'function'
                                  ? t.selectAllMatching(meta.total)
                                  : `Select all ${meta.total} matching`}
                        </Button>
                    )}
                </div>
                <Popover>
                    <PopoverTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0 md:hidden"
                        >
                            <EllipsisVertical className="h-4 w-4" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent
                        align="end"
                        className="flex w-auto flex-col gap-2 p-2"
                    >
                        <DataTableToolbar
                            tableData={tableData}
                            table={table}
                            tableName={tableName}
                            columnVisibility={columnVisibility}
                            columnOrder={columnOrder}
                            applyColumns={applyColumns}
                            onReorderColumns={setColumnOrder}
                            handleApplyQuickView={handleApplyQuickView}
                            handleApplyCustomSearch={handleApplyCustomSearch}
                            resolvedOptions={resolvedOptions}
                            headerActions={headerActions}
                            importUrl={tableData.importUrl}
                            partialReloadKey={partialReloadKey}
                            importInputRef={importInputRef}
                            density={density}
                            onDensityChange={setDensity}
                            densityLabel={t.density}
                        />
                    </PopoverContent>
                </Popover>
                <div className="hidden items-center gap-2 md:flex">
                    <DataTableToolbar
                        tableData={tableData}
                        table={table}
                        tableName={tableName}
                        columnVisibility={columnVisibility}
                        columnOrder={columnOrder}
                        applyColumns={applyColumns}
                        onReorderColumns={setColumnOrder}
                        handleApplyQuickView={handleApplyQuickView}
                        handleApplyCustomSearch={handleApplyCustomSearch}
                        resolvedOptions={resolvedOptions}
                        headerActions={headerActions}
                        importUrl={tableData.importUrl}
                        partialReloadKey={partialReloadKey}
                        importInputRef={importInputRef}
                        density={density}
                        onDensityChange={setDensity}
                        densityLabel={t.density}
                    />
                </div>
            </div>
            {hasActiveFilters && (
                <div className="flex flex-wrap items-center gap-2 px-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-7 text-xs"
                        onClick={clearAllFilters}
                    >
                        <X className="mr-1 h-3 w-3" />
                        {t.clearFilters}
                    </Button>
                </div>
            )}
            {hasBulkActions && selectedRows.length > 0 && (
                <div className="flex items-center gap-2 rounded-lg border bg-muted/50 px-3 py-2">
                    <span className="text-sm font-medium tabular-nums">
                        {selectedRows.length} selected
                    </span>
                    <div className="flex items-center gap-1">
                        {bulkActions.map((action) => {
                            const Icon = action.icon;
                            const isDisabled =
                                action.disabled?.(selectedRows) ?? false;
                            const hasConfirm = action.confirm === true || (typeof action.confirm === 'object' && action.confirm !== null);
                            return (
                                <Button
                                    key={action.id}
                                    variant={
                                        action.variant === 'destructive'
                                            ? 'destructive'
                                            : 'outline'
                                    }
                                    size="sm"
                                    className="h-7 text-xs"
                                    disabled={isDisabled}
                                    onClick={() =>
                                        hasConfirm
                                            ? setConfirmingBulkAction(action)
                                            : action.onClick(selectedRows)
                                    }
                                >
                                    {Icon && (
                                        <Icon className="mr-1 h-3.5 w-3.5" />
                                    )}
                                    {action.label}
                                </Button>
                            );
                        })}
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="ml-auto h-7 w-7"
                        onClick={() => setRowSelection({})}
                    >
                        <X className="h-3.5 w-3.5" />
                    </Button>
                </div>
            )}
            <div
                className={cn(
                    'overflow-x-auto rounded-md border border-x-0',
                    className,
                )}
            >
                {slots?.beforeTable}
                <Table>
                    <TableHeader
                        className={cn(
                            resolvedOptions.stickyHeader &&
                                'sticky top-0 z-10 bg-background shadow-sm',
                        )}
                    >
                        {table
                            .getHeaderGroups()
                            .map((headerGroup, groupIdx) => {
                                const isGroupRow =
                                    groupIdx <
                                    table.getHeaderGroups().length - 1;
                                return (
                                    <TableRow key={headerGroup.id}>
                                        {headerGroup.headers.map((header) => {
                                            if (isGroupRow) {
                                                const pin =
                                                    getColumnPinningProps(
                                                        header.column,
                                                    );
                                                return (
                                                    <TableHead
                                                        key={header.id}
                                                        colSpan={header.colSpan}
                                                        style={pin.style}
                                                        className={cn(
                                                            'h-8',
                                                            !header.isPlaceholder &&
                                                                header.colSpan >
                                                                    1 &&
                                                                'border-b text-center text-xs font-semibold text-muted-foreground',
                                                            !header.isPlaceholder &&
                                                                header.colSpan >
                                                                    1 &&
                                                                groupClassName?.[
                                                                    header
                                                                        .column
                                                                        .columnDef
                                                                        .header as string
                                                                ],
                                                            pin.className,
                                                        )}
                                                    >
                                                        {header.isPlaceholder
                                                            ? null
                                                            : flexRender(
                                                                  header.column
                                                                      .columnDef
                                                                      .header,
                                                                  header.getContext(),
                                                              )}
                                                    </TableHead>
                                                );
                                            }

                                            const colDef =
                                                tableData.columns.find(
                                                    (c) =>
                                                        c.id ===
                                                        header.column.id,
                                                );
                                            const isNumber =
                                                colDef?.type === 'number';
                                            const leafGroup = colDef?.group;
                                            const pin = getColumnPinningProps(
                                                header.column,
                                            );
                                            return (
                                                <TableHead
                                                    key={header.id}
                                                    colSpan={header.colSpan}
                                                    style={pin.style}
                                                    className={cn(
                                                        isNumber &&
                                                            'text-right',
                                                        leafGroup &&
                                                            groupClassName?.[
                                                                leafGroup
                                                            ],
                                                        pin.className,
                                                    )}
                                                >
                                                    {header.isPlaceholder ? null : colDef?.sortable ? (
                                                        <DataTableColumnHeader
                                                            label={colDef.label}
                                                            sortable={
                                                                colDef.sortable
                                                            }
                                                            sorts={meta.sorts}
                                                            columnId={colDef.id}
                                                            onSort={handleSort}
                                                            align={
                                                                isNumber
                                                                    ? 'right'
                                                                    : 'left'
                                                            }
                                                        >
                                                            {
                                                                renderHeader?.[
                                                                    colDef.id
                                                                ]
                                                            }
                                                        </DataTableColumnHeader>
                                                    ) : (
                                                        (renderHeader?.[
                                                            header.column.id
                                                        ] ??
                                                        flexRender(
                                                            header.column
                                                                .columnDef
                                                                .header,
                                                            header.getContext(),
                                                        ))
                                                    )}
                                                </TableHead>
                                            );
                                        })}
                                    </TableRow>
                                );
                            })}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows.length > 0 ? (
                            table.getRowModel().rows.flatMap((row, index) => {
                                const rowId = (row.original as { id?: unknown }).id;
                                const isExpanded = detailRowEnabled && expandedRowId != null && String(expandedRowId) === String(rowId);
                                const detailContent = isExpanded && renderDetailRow
                                    ? renderDetailRow(row.original, detailCache[String(rowId)] ?? {})
                                    : null;
                                const cols = table.getVisibleLeafColumns().length;
                                return [
                                    <TableRow
                                        key={row.id}
                                        data-state={
                                            row.getIsSelected()
                                                ? 'selected'
                                                : undefined
                                        }
                                        className={cn(
                                            densityRowClass,
                                            index % 2 === 1 && 'bg-muted/40',
                                            row.getIsSelected() && 'bg-primary/5',
                                            rowLink && 'cursor-pointer',
                                            rowClassName?.(row.original),
                                        )}
                                        onClick={
                                            rowLink
                                                ? (e) => {
                                                      const href = rowLink(
                                                          row.original,
                                                      );
                                                      if (e.metaKey || e.ctrlKey) {
                                                          window.open(href);
                                                      } else {
                                                          router.visit(href);
                                                      }
                                                  }
                                                : undefined
                                        }
                                        role={rowLink ? 'link' : undefined}
                                    >
                                        {row.getVisibleCells().map((cell) => {
                                            const pin = getColumnPinningProps(
                                                cell.column,
                                            );
                                            const pinnedBg = getPinnedCellBg(
                                                cell.column.getIsPinned(),
                                                row.getIsSelected(),
                                            );
                                            return (
                                                <TableCell
                                                    key={cell.id}
                                                    style={{
                                                        ...pin.style,
                                                        ...pinnedBg,
                                                    }}
                                                    className={cn(
                                                        index % 2 === 1 &&
                                                            'bg-muted/40',
                                                        'py-2 whitespace-nowrap',
                                                        getColumnMeta(cell.column.columnDef).type === 'number' &&
                                                            'text-right',
                                                        getColumnMeta(cell.column.columnDef).group &&
                                                            groupClassName?.[getColumnMeta(cell.column.columnDef).group!],
                                                        pin.className,
                                                    )}
                                                >
                                                    {flexRender(
                                                        cell.column.columnDef.cell,
                                                        cell.getContext(),
                                                    )}
                                                </TableCell>
                                            );
                                        })}
                                    </TableRow>,
                                    ...(detailContent
                                        ? [
                                            <TableRow key={`${row.id}-detail`} className="bg-muted/20">
                                                <TableCell colSpan={cols} className="p-0">
                                                    {detailContent}
                                                </TableCell>
                                            </TableRow>,
                                        ]
                                        : []),
                                ];
                            })
                        ) : (
                            <TableRow>
                                <TableCell
                                    colSpan={
                                        table.getVisibleLeafColumns().length
                                    }
                                    className="h-24 text-center"
                                >
                                    {emptyState ?? (
                                        <span className="text-muted-foreground">
                                            No results.
                                        </span>
                                    )}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                    {tableData.footer && (
                        <TableFooter>
                            <TableRow>
                                {[
                                    ...table.getLeftVisibleLeafColumns(),
                                    ...table.getCenterVisibleLeafColumns(),
                                    ...table.getRightVisibleLeafColumns(),
                                ].map((col) => {
                                    const footerValue =
                                        tableData.footer?.[col.id];
                                    const meta = getColumnMeta(col.columnDef);
                                    const isNumber = meta.type === 'number';
                                    const pin = getColumnPinningProps(col);
                                    let content: React.ReactNode = null;
                                    if (footerValue != null) {
                                        const custom = renderFooterCell?.(col.id, footerValue);
                                        if (custom !== undefined) {
                                            content = custom;
                                        } else {
                                            content = isNumber && typeof footerValue === 'number'
                                                ? footerValue.toLocaleString('fr-TN')
                                                : String(footerValue);
                                        }
                                    }
                                    const footerPinnedBg = col.getIsPinned()
                                        ? { backgroundColor: 'var(--color-background)' }
                                        : {};
                                    return (
                                        <TableCell
                                            key={col.id}
                                            style={{
                                                ...pin.style,
                                                ...footerPinnedBg,
                                            }}
                                            className={cn(
                                                'py-2 font-medium whitespace-nowrap',
                                                isNumber &&
                                                    'text-right tabular-nums',
                                                meta.group &&
                                                    groupClassName?.[meta.group],
                                                pin.className,
                                            )}
                                        >
                                            {content}
                                        </TableCell>
                                    );
                                })}
                            </TableRow>
                        </TableFooter>
                    )}
                    {tableData.summary && Object.keys(tableData.summary).length > 0 && (
                        <TableFooter>
                            <TableRow className="bg-muted/30 font-medium">
                                {[
                                    ...table.getLeftVisibleLeafColumns(),
                                    ...table.getCenterVisibleLeafColumns(),
                                    ...table.getRightVisibleLeafColumns(),
                                ].map((col) => {
                                    const summaryValue = tableData.summary?.[col.id];
                                    const isNumber = getColumnMeta(col.columnDef).type === 'number';
                                    const pin = getColumnPinningProps(col);
                                    const content = summaryValue != null
                                        ? (typeof summaryValue === 'number' && isNumber
                                            ? summaryValue.toLocaleString('fr-TN')
                                            : String(summaryValue))
                                        : null;
                                    return (
                                        <TableCell
                                            key={col.id}
                                            style={col.getIsPinned() ? { backgroundColor: 'var(--color-background)' } : {}}
                                            className={cn(
                                                'py-2 whitespace-nowrap text-muted-foreground',
                                                isNumber && 'text-right tabular-nums',
                                                pin.className,
                                            )}
                                        >
                                            {content}
                                        </TableCell>
                                    );
                                })}
                            </TableRow>
                        </TableFooter>
                    )}
                </Table>
                {slots?.afterTable}
            </div>
            <DataTablePagination
                meta={meta}
                onPageChange={handlePageChange}
                onPerPageChange={handlePerPageChange}
            />
            {detailRowEnabled && detailOverlayRow && detailDisplay === 'drawer' && (
                <Sheet
                    open
                    onOpenChange={(open) => !open && setDetailOverlayRow(null)}
                >
                    <SheetContent className="overflow-y-auto sm:max-w-lg">
                        <SheetHeader>
                            <SheetTitle>Details</SheetTitle>
                        </SheetHeader>
                        <div className="mt-4">
                            {renderDetailRow?.(
                                detailOverlayRow.data,
                                detailCache[String(detailOverlayRow.id)] ?? {},
                            )}
                        </div>
                    </SheetContent>
                </Sheet>
            )}
            {detailRowEnabled && detailOverlayRow && detailDisplay === 'modal' && (
                <Dialog
                    open
                    onOpenChange={(open) => !open && setDetailOverlayRow(null)}
                >
                    <DialogContent className="max-w-lg">
                        <DialogHeader>
                            <DialogTitle>Details</DialogTitle>
                        </DialogHeader>
                        <div className="mt-4">
                            {renderDetailRow?.(
                                detailOverlayRow.data,
                                detailCache[String(detailOverlayRow.id)] ?? {},
                            )}
                        </div>
                    </DialogContent>
                </Dialog>
            )}
            {confirmingBulkAction && (() => {
                const opts = getBulkConfirmOptions(
                    confirmingBulkAction as DataTableBulkAction<unknown>,
                    `Run "${confirmingBulkAction.label}" on ${selectedRows.length} selected item(s)?`,
                );
                return (
                    <Dialog
                        open
                        onOpenChange={(open) => !open && setConfirmingBulkAction(null)}
                    >
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{opts.title}</DialogTitle>
                            </DialogHeader>
                            {opts.description && (
                                <p className="text-sm text-muted-foreground">
                                    {opts.description}
                                </p>
                            )}
                            <div className="mt-4 flex justify-end gap-2">
                                <Button
                                    variant="outline"
                                    onClick={() => setConfirmingBulkAction(null)}
                                >
                                    {opts.cancelLabel ?? 'Cancel'}
                                </Button>
                                <Button
                                    variant={opts.variant === 'destructive' ? 'destructive' : 'default'}
                                    onClick={() => {
                                        confirmingBulkAction.onClick(selectedRows);
                                        setConfirmingBulkAction(null);
                                    }}
                                >
                                    {opts.confirmLabel ?? 'Confirm'}
                                </Button>
                            </div>
                        </DialogContent>
                    </Dialog>
                );
            })()}
        </div>
    );
}
