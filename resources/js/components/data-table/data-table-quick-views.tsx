import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";
import type { DataTableTranslations } from "./i18n";
import type { LucideIcon } from "lucide-react";
import {
    Bookmark,
    Calendar,
    CheckCircle,
    Eye,
    Gauge,
    Globe,
    ImageOff,
    List,
    Pencil,
    Save,
    Shield,
    Trash2,
    Users,
    X,
} from "lucide-react";
import type { ColumnOrderState, VisibilityState } from "@tanstack/react-table";
import { useCallback, useEffect, useState } from "react";
import type { DataTableColumnDef, DataTableQuickView } from "./types";

const ICON_MAP: Record<string, LucideIcon> = {
    list: List,
    "check-circle": CheckCircle,
    calendar: Calendar,
    "image-off": ImageOff,
    gauge: Gauge,
};

interface ApiSavedView {
    id: number;
    name: string;
    table_name: string;
    filters: Record<string, unknown> | null;
    sort: string | null;
    columns: string[] | null;
    column_order: string[] | null;
    is_shared: boolean;
    is_system: boolean;
    created_by: number | null;
}

interface GroupedViews {
    my_views: ApiSavedView[];
    team_views: ApiSavedView[];
    system_views: ApiSavedView[];
}

async function fetchGroupedViews(tableName: string): Promise<GroupedViews> {
    const response = await fetch(`/api/data-table-saved-views?table_name=${encodeURIComponent(tableName)}`, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
    });
    if (!response.ok) return { my_views: [], team_views: [], system_views: [] };
    return response.json() as Promise<GroupedViews>;
}

async function createSavedView(payload: Record<string, unknown>): Promise<ApiSavedView | null> {
    const response = await fetch("/api/data-table-saved-views", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-XSRF-TOKEN": decodeURIComponent(
                document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? "",
            ),
        },
        credentials: "same-origin",
        body: JSON.stringify(payload),
    });
    if (!response.ok) return null;
    const json = await response.json();
    return json.data as ApiSavedView;
}

async function deleteSavedView(id: number): Promise<boolean> {
    const response = await fetch(`/api/data-table-saved-views/${id}`, {
        method: "DELETE",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-XSRF-TOKEN": decodeURIComponent(
                document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? "",
            ),
        },
        credentials: "same-origin",
    });
    return response.ok || response.status === 204;
}

function buildSearchFromFilters(filters: Record<string, unknown> | null, sort: string | null): string {
    if (!filters && !sort) return "";
    const params = new URLSearchParams();
    if (filters) {
        for (const [key, value] of Object.entries(filters)) {
            params.set(`filter[${key}]`, String(value));
        }
    }
    if (sort) {
        params.set("sort", sort);
    }
    return `?${params.toString()}`;
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
    t: DataTableTranslations;
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
    t,
}: DataTableQuickViewsProps) {
    const [groupedViews, setGroupedViews] = useState<GroupedViews>({
        my_views: [],
        team_views: [],
        system_views: [],
    });
    const [editing, setEditing] = useState(false);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [newName, setNewName] = useState("");
    const [isShared, setIsShared] = useState(false);
    const [activeViewId, setActiveViewId] = useState<number | null>(null);

    const loadViews = useCallback(async () => {
        const views = await fetchGroupedViews(tableName);
        setGroupedViews(views);
    }, [tableName]);

    useEffect(() => {
        void loadViews();
    }, [loadViews]);

    const allSavedViews = [
        ...groupedViews.my_views,
        ...groupedViews.team_views,
        ...groupedViews.system_views,
    ];

    const active = quickViews.find((qv) => qv.active);

    const currentSearch = typeof window !== "undefined"
        ? new URL(window.location.href).search
        : "";

    const hasFilters = decodeURIComponent(currentSearch).includes("filter[");

    const getVisibleColumnIds = useCallback((): string[] => {
        return allColumns
            .filter((col) => columnVisibility[col.id] !== false)
            .map((col) => col.id);
    }, [allColumns, columnVisibility]);

    const handleSave = useCallback(async () => {
        if (!newName.trim()) return;
        const columns = getVisibleColumnIds();
        const decoded = decodeURIComponent(currentSearch);
        const params = new URLSearchParams(decoded);
        const filters: Record<string, string> = {};
        for (const [key, val] of params.entries()) {
            const match = key.match(/^filter\[(.+)]$/);
            if (match) {
                filters[match[1]] = val;
            }
        }
        const sortParam = params.get("sort") ?? undefined;

        const created = await createSavedView({
            table_name: tableName,
            name: newName.trim(),
            filters: Object.keys(filters).length > 0 ? filters : null,
            sort: sortParam ?? null,
            columns,
            column_order: columnOrder,
            is_shared: isShared,
        });

        if (created) {
            setNewName("");
            setIsShared(false);
            setDialogOpen(false);
            void loadViews();
        }
    }, [newName, currentSearch, getVisibleColumnIds, columnOrder, tableName, isShared, loadViews]);

    const handleDeleteView = useCallback(async (id: number) => {
        const success = await deleteSavedView(id);
        if (success) {
            if (activeViewId === id) setActiveViewId(null);
            void loadViews();
        }
    }, [activeViewId, loadViews]);

    const handleSelectSaved = useCallback((view: ApiSavedView) => {
        setActiveViewId(view.id);
        if (view.columns) {
            onApplyColumns(view.columns);
        }
        if (view.column_order) {
            onApplyColumnOrder(view.column_order);
        }
        const search = buildSearchFromFilters(view.filters, view.sort);
        onApplyCustom(search);
    }, [onApplyCustom, onApplyColumns, onApplyColumnOrder]);

    const handleSelectServer = useCallback((qv: DataTableQuickView) => {
        setActiveViewId(null);
        if (qv.columns) {
            onApplyColumns(qv.columns);
        }
        onSelect(qv.params);
    }, [onSelect, onApplyColumns]);

    const activeLabel = activeViewId
        ? allSavedViews.find((v) => v.id === activeViewId)?.name
        : active?.label;

    if (quickViews.length === 0 && allSavedViews.length === 0 && !enableCustom) return null;

    const renderViewSection = (
        views: ApiSavedView[],
        label: string,
        icon: LucideIcon,
    ) => {
        if (views.length === 0) return null;
        const Icon = icon;
        return (
            <>
                <DropdownMenuSeparator />
                <DropdownMenuLabel className="text-xs text-muted-foreground font-normal flex items-center gap-1.5">
                    <Icon className="h-3 w-3" />
                    {label}
                </DropdownMenuLabel>
                {views.map((sv) => (
                    <DropdownMenuItem
                        key={sv.id}
                        className="gap-2"
                        onSelect={() => handleSelectSaved(sv)}
                    >
                        <Bookmark className="h-4 w-4 text-muted-foreground" />
                        <span className={activeViewId === sv.id ? "font-semibold flex-1" : "flex-1"}>
                            {sv.name}
                        </span>
                        {sv.is_shared && (
                            <Badge variant="secondary" className="text-[10px] px-1 py-0">
                                {t.sharedBadge}
                            </Badge>
                        )}
                        {sv.is_system && (
                            <Badge variant="outline" className="text-[10px] px-1 py-0">
                                {t.systemBadge}
                            </Badge>
                        )}
                    </DropdownMenuItem>
                ))}
            </>
        );
    };

    const renderEditSection = (views: ApiSavedView[], label: string, icon: LucideIcon) => {
        if (views.length === 0) return null;
        const Icon = icon;
        return (
            <>
                <DropdownMenuSeparator />
                <DropdownMenuLabel className="text-xs text-muted-foreground font-normal flex items-center gap-1.5">
                    <Icon className="h-3 w-3" />
                    {label}
                </DropdownMenuLabel>
                {views.map((sv) => (
                    <div key={sv.id} className="flex items-center gap-2 px-2 py-1.5 text-sm">
                        <Bookmark className="h-4 w-4 text-muted-foreground" />
                        <span className="flex-1">{sv.name}</span>
                        <button
                            type="button"
                            className="rounded p-0.5 text-destructive hover:bg-destructive/10"
                            onClick={() => void handleDeleteView(sv.id)}
                        >
                            <Trash2 className="h-3.5 w-3.5" />
                        </button>
                    </div>
                ))}
            </>
        );
    };

    return (
        <>
            <DropdownMenu onOpenChange={(open) => { if (!open) setEditing(false); }}>
                <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm" className="h-8">
                        <Eye className="h-4 w-4" />
                        {activeLabel ?? t.view}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>{t.quickViews}</DropdownMenuLabel>
                    <DropdownMenuSeparator />

                    {!editing ? (
                        <>
                            {quickViews.map((qv) => {
                                const Icon = qv.icon ? ICON_MAP[qv.icon] : undefined;
                                const isActive = !activeViewId && qv.active;
                                return (
                                    <DropdownMenuItem
                                        key={qv.id}
                                        className="gap-2"
                                        onSelect={() => handleSelectServer(qv)}
                                    >
                                        {Icon && <Icon className="h-4 w-4 text-muted-foreground" />}
                                        <span className={isActive ? "font-semibold" : ""}>
                                            {qv.label}
                                        </span>
                                    </DropdownMenuItem>
                                );
                            })}

                            {renderViewSection(groupedViews.my_views, t.myViews, Bookmark)}
                            {renderViewSection(groupedViews.team_views, t.teamViews, Users)}
                            {renderViewSection(groupedViews.system_views, t.systemViews, Shield)}

                            <DropdownMenuSeparator />

                            {enableCustom && (
                                <DropdownMenuItem
                                    className="gap-2"
                                    disabled={!hasFilters}
                                    onSelect={() => {
                                        setTimeout(() => setDialogOpen(true), 0);
                                    }}
                                >
                                    <Save className="h-4 w-4" />
                                    {t.saveFilters}
                                </DropdownMenuItem>
                            )}

                            {enableCustom && allSavedViews.length > 0 && (
                                <DropdownMenuItem
                                    className="gap-2"
                                    onSelect={(e) => {
                                        e.preventDefault();
                                        setEditing(true);
                                    }}
                                >
                                    <Pencil className="h-4 w-4" />
                                    {t.manageViews}
                                </DropdownMenuItem>
                            )}
                        </>
                    ) : (
                        <>
                            {quickViews.map((qv) => {
                                const Icon = qv.icon ? ICON_MAP[qv.icon] : undefined;
                                return (
                                    <div key={qv.id} className="flex items-center gap-2 px-2 py-1.5 text-sm text-muted-foreground">
                                        {Icon && <Icon className="h-4 w-4" />}
                                        <span className="flex-1">{qv.label}</span>
                                    </div>
                                );
                            })}

                            {renderEditSection(groupedViews.my_views, t.myViews, Bookmark)}
                            {renderEditSection(groupedViews.team_views, t.teamViews, Users)}
                            {renderEditSection(groupedViews.system_views, t.systemViews, Shield)}

                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                className="gap-2"
                                onSelect={(e) => {
                                    e.preventDefault();
                                    setEditing(false);
                                }}
                            >
                                <X className="h-4 w-4" />
                                {t.done}
                            </DropdownMenuItem>
                        </>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>

            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t.saveFilters}</DialogTitle>
                        <DialogDescription>
                            {t.filtersWillBeSavedLocally}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-3 py-2">
                        <div className="grid gap-2">
                            <Label htmlFor="qv-name">{t.viewName}</Label>
                            <Input
                                id="qv-name"
                                value={newName}
                                onChange={(e) => setNewName(e.target.value)}
                                placeholder={t.viewNamePlaceholder}
                                onKeyDown={(e) => e.key === "Enter" && void handleSave()}
                                autoFocus
                            />
                        </div>
                        <div className="flex items-center gap-2">
                            <Switch
                                id="qv-shared"
                                checked={isShared}
                                onCheckedChange={setIsShared}
                            />
                            <Label htmlFor="qv-shared" className="flex items-center gap-1.5 text-sm cursor-pointer">
                                <Globe className="h-3.5 w-3.5 text-muted-foreground" />
                                {t.shareWithTeam}
                            </Label>
                        </div>
                        {(() => {
                            const decoded = decodeURIComponent(currentSearch);
                            const params = new URLSearchParams(decoded);
                            const filters: string[] = [];
                            const sortParam = params.get("sort");

                            for (const [key, val] of params.entries()) {
                                const match = key.match(/^filter\[(.+)]$/);
                                if (match) {
                                    filters.push(`${match[1]} = ${val}`);
                                }
                            }

                            const visibleCount = getVisibleColumnIds().length;
                            const totalCount = allColumns.length;

                            return (
                                <div className="space-y-1 rounded border bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                                    <div>
                                        <span className="font-medium text-foreground">{t.filtersLabel}</span>{" "}
                                        {filters.length > 0 ? filters.join(" · ") : t.none}
                                    </div>
                                    {sortParam && (
                                        <div>
                                            <span className="font-medium text-foreground">{t.sortLabel}</span>{" "}
                                            {sortParam.split(",").map((s) =>
                                                s.startsWith("-") ? `${s.slice(1)} ↓` : `${s} ↑`
                                            ).join(", ")}
                                        </div>
                                    )}
                                    <div>
                                        <span className="font-medium text-foreground">{t.columns}:</span>{" "}
                                        {t.columnsCount(visibleCount, totalCount)}
                                    </div>
                                </div>
                            );
                        })()}
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDialogOpen(false)}>
                            {t.cancel}
                        </Button>
                        <Button onClick={() => void handleSave()} disabled={!newName.trim()}>
                            <Save className="mr-2 h-4 w-4" />
                            {t.save}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
