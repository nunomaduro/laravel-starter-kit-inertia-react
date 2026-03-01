import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ColumnOrderState, VisibilityState } from '@tanstack/react-table';
import type { LucideIcon } from 'lucide-react';
import {
    Bookmark,
    Calendar,
    CheckCircle,
    Eye,
    Gauge,
    ImageOff,
    List,
    Pencil,
    Save,
    Trash2,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import type { DataTableColumnDef, DataTableQuickView } from './types';

const ICON_MAP: Record<string, LucideIcon> = {
    list: List,
    'check-circle': CheckCircle,
    calendar: Calendar,
    'image-off': ImageOff,
    gauge: Gauge,
};

const CUSTOM_QV_PREFIX = 'dt-quickviews-';

function SavePreview({
    currentSearch,
    visibleColumnCount,
    totalColumnCount,
}: {
    currentSearch: string;
    visibleColumnCount: number;
    totalColumnCount: number;
}) {
    const decoded = decodeURIComponent(currentSearch);
    const params = new URLSearchParams(decoded);
    const filters: string[] = [];
    const sortParam = params.get('sort');

    for (const [key, val] of params.entries()) {
        const match = key.match(/^filter\[(.+)]$/);
        if (match) {
            filters.push(`${match[1]} = ${val}`);
        }
    }

    return (
        <div className="space-y-1 rounded border bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
            <div>
                <span className="font-medium text-foreground">Filters:</span>{' '}
                {filters.length > 0 ? filters.join(' · ') : 'None'}
            </div>
            {sortParam && (
                <div>
                    <span className="font-medium text-foreground">Sort:</span>{' '}
                    {sortParam
                        .split(',')
                        .map((s) =>
                            s.startsWith('-') ? `${s.slice(1)} \u2193` : `${s} \u2191`,
                        )
                        .join(', ')}
                </div>
            )}
            <div>
                <span className="font-medium text-foreground">Columns:</span>{' '}
                {visibleColumnCount}/{totalColumnCount} visible
            </div>
        </div>
    );
}

interface SavedQuickView {
    id: string;
    label: string;
    search: string;
    columns?: string[] | null;
    columnOrder?: ColumnOrderState | null;
}

function loadSavedViews(tableName: string): SavedQuickView[] {
    try {
        const raw = localStorage.getItem(CUSTOM_QV_PREFIX + tableName);
        return raw ? (JSON.parse(raw) as SavedQuickView[]) : [];
    } catch {
        return [];
    }
}

function persistSavedViews(tableName: string, views: SavedQuickView[]) {
    localStorage.setItem(CUSTOM_QV_PREFIX + tableName, JSON.stringify(views));
}

interface DataTableQuickViewsProps {
    quickViews: DataTableQuickView[];
    tableName: string;
    columnVisibility: VisibilityState;
    columnOrder: ColumnOrderState;
    allColumns: DataTableColumnDef[];
    onSelect: (params: Record<string, unknown>) => void;
    onApplyCustom: (search: string) => void;
    onApplyColumns: (columnIds: string[]) => void;
    onApplyColumnOrder: (order: ColumnOrderState) => void;
    enableCustom?: boolean;
}

export function DataTableQuickViews({
    quickViews,
    tableName,
    columnVisibility,
    columnOrder,
    allColumns,
    onSelect,
    onApplyCustom,
    onApplyColumns,
    onApplyColumnOrder,
    enableCustom = true,
}: DataTableQuickViewsProps) {
    const [savedViews, setSavedViews] = useState<SavedQuickView[]>(() =>
        loadSavedViews(tableName),
    );
    const [editing, setEditing] = useState(false);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [newName, setNewName] = useState('');
    const [activeCustomId, setActiveCustomId] = useState<string | null>(null);

    useEffect(() => {
        persistSavedViews(tableName, savedViews);
    }, [tableName, savedViews]);

    const active = quickViews.find((qv) => qv.active);

    const currentSearch =
        typeof window !== 'undefined'
            ? new URL(window.location.href).search
            : '';

    const hasFilters = decodeURIComponent(currentSearch).includes('filter[');

    const getVisibleColumnIds = useCallback((): string[] => {
        return allColumns
            .filter((col) => columnVisibility[col.id] !== false)
            .map((col) => col.id);
    }, [allColumns, columnVisibility]);

    const handleSave = useCallback(() => {
        if (!newName.trim()) return;
        const id = `custom_${Date.now()}`;
        const columns = getVisibleColumnIds();
        setSavedViews((prev) => [
            ...prev,
            {
                id,
                label: newName.trim(),
                search: currentSearch,
                columns,
                columnOrder,
            },
        ]);
        setNewName('');
        setDialogOpen(false);
    }, [newName, currentSearch, getVisibleColumnIds, columnOrder]);

    const handleDeleteCustom = useCallback(
        (id: string) => {
            setSavedViews((prev) => prev.filter((v) => v.id !== id));
            if (activeCustomId === id) setActiveCustomId(null);
        },
        [activeCustomId],
    );

    const handleSelectCustom = useCallback(
        (view: SavedQuickView) => {
            setActiveCustomId(view.id);
            if (view.columns) {
                onApplyColumns(view.columns);
            }
            if (view.columnOrder) {
                onApplyColumnOrder(view.columnOrder);
            }
            onApplyCustom(view.search);
        },
        [onApplyCustom, onApplyColumns, onApplyColumnOrder],
    );

    const handleSelectServer = useCallback(
        (qv: DataTableQuickView) => {
            setActiveCustomId(null);
            if (qv.columns) {
                onApplyColumns(qv.columns);
            }
            onSelect(qv.params);
        },
        [onSelect, onApplyColumns],
    );

    const activeLabel = activeCustomId
        ? savedViews.find((v) => v.id === activeCustomId)?.label
        : active?.label;

    if (quickViews.length === 0 && savedViews.length === 0 && !enableCustom)
        return null;

    return (
        <>
            <DropdownMenu
                onOpenChange={(open) => {
                    if (!open) setEditing(false);
                }}
            >
                <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm" className="h-8">
                        <Eye className="h-4 w-4" />
                        {activeLabel ?? 'View'}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>Quick views</DropdownMenuLabel>
                    <DropdownMenuSeparator />

                    {!editing ? (
                        <>
                            {quickViews.map((qv) => {
                                const Icon = qv.icon
                                    ? ICON_MAP[qv.icon]
                                    : undefined;
                                const isActive = !activeCustomId && qv.active;
                                return (
                                    <DropdownMenuItem
                                        key={qv.id}
                                        className="gap-2"
                                        onSelect={() => handleSelectServer(qv)}
                                    >
                                        {Icon && (
                                            <Icon className="h-4 w-4 text-muted-foreground" />
                                        )}
                                        <span
                                            className={
                                                isActive ? 'font-semibold' : ''
                                            }
                                        >
                                            {qv.label}
                                        </span>
                                    </DropdownMenuItem>
                                );
                            })}

                            {savedViews.length > 0 && (
                                <>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuLabel className="text-xs font-normal text-muted-foreground">
                                        Saved views
                                    </DropdownMenuLabel>
                                    {savedViews.map((sv) => (
                                        <DropdownMenuItem
                                            key={sv.id}
                                            className="gap-2"
                                            onSelect={() =>
                                                handleSelectCustom(sv)
                                            }
                                        >
                                            <Bookmark className="h-4 w-4 text-muted-foreground" />
                                            <span
                                                className={
                                                    activeCustomId === sv.id
                                                        ? 'font-semibold'
                                                        : ''
                                                }
                                            >
                                                {sv.label}
                                            </span>
                                        </DropdownMenuItem>
                                    ))}
                                </>
                            )}

                            <DropdownMenuSeparator />

                            {enableCustom && (
                                <DropdownMenuItem
                                    className="gap-2"
                                    disabled={!hasFilters}
                                    onSelect={() => {
                                        setTimeout(
                                            () => setDialogOpen(true),
                                            0,
                                        );
                                    }}
                                >
                                    <Save className="h-4 w-4" />
                                    Save filters
                                </DropdownMenuItem>
                            )}

                            {enableCustom && savedViews.length > 0 && (
                                <DropdownMenuItem
                                    className="gap-2"
                                    onSelect={(e) => {
                                        e.preventDefault();
                                        setEditing(true);
                                    }}
                                >
                                    <Pencil className="h-4 w-4" />
                                    Manage views
                                </DropdownMenuItem>
                            )}
                        </>
                    ) : (
                        <>
                            {quickViews.map((qv) => {
                                const Icon = qv.icon
                                    ? ICON_MAP[qv.icon]
                                    : undefined;
                                return (
                                    <div
                                        key={qv.id}
                                        className="flex items-center gap-2 px-2 py-1.5 text-sm text-muted-foreground"
                                    >
                                        {Icon && <Icon className="h-4 w-4" />}
                                        <span className="flex-1">
                                            {qv.label}
                                        </span>
                                    </div>
                                );
                            })}
                            {savedViews.length > 0 && <DropdownMenuSeparator />}
                            {savedViews.map((sv) => (
                                <div
                                    key={sv.id}
                                    className="flex items-center gap-2 px-2 py-1.5 text-sm"
                                >
                                    <Bookmark className="h-4 w-4 text-muted-foreground" />
                                    <span className="flex-1">{sv.label}</span>
                                    <button
                                        type="button"
                                        className="rounded p-0.5 text-destructive hover:bg-destructive/10"
                                        onClick={() =>
                                            handleDeleteCustom(sv.id)
                                        }
                                    >
                                        <Trash2 className="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            ))}
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                className="gap-2"
                                onSelect={(e) => {
                                    e.preventDefault();
                                    setEditing(false);
                                }}
                            >
                                <X className="h-4 w-4" />
                                Done
                            </DropdownMenuItem>
                        </>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>

            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Save filters</DialogTitle>
                        <DialogDescription>
                            Active filters will be saved locally.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-3 py-2">
                        <div className="grid gap-2">
                            <Label htmlFor="qv-name">View name</Label>
                            <Input
                                id="qv-name"
                                value={newName}
                                onChange={(e) => setNewName(e.target.value)}
                                placeholder="e.g. Recent items"
                                onKeyDown={(e) =>
                                    e.key === 'Enter' && handleSave()
                                }
                                autoFocus
                            />
                        </div>
                        <SavePreview
                            currentSearch={currentSearch}
                            visibleColumnCount={getVisibleColumnIds().length}
                            totalColumnCount={allColumns.length}
                        />
                    </div>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDialogOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button onClick={handleSave} disabled={!newName.trim()}>
                            <Save className="mr-2 h-4 w-4" />
                            Save
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
