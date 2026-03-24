import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter as DialogFoot,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
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
} from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from "@/components/ui/sheet";
import { Filters } from "../filters/filters";
import type { FilterColumn } from "../filters/types";
import { Checkbox } from "@/components/ui/checkbox";
import { cn } from "@/lib/utils";
import { type Column, type ColumnDef, type ColumnOrderState, type Table as TanStackTable, type VisibilityState, flexRender } from "@tanstack/react-table";
import {
    AlertTriangle,
    AlignJustify,
    ArrowDown,
    ArrowUp,
    BarChart3,
    Calendar,
    Check,
    ChevronDown,
    ChevronRight,
    CircleDot,
    Clipboard,
    DollarSign,
    Download,
    EllipsisVertical,
    Expand,
    ExternalLink,
    EyeOff,
    FileDown,
    FileSpreadsheet,
    FileText,
    Grid3X3,
    GripVertical,
    Hash,
    HelpCircle,
    Image as ImageIcon,
    Kanban,
    Keyboard,
    LayoutGrid,
    LayoutList,
    Link as LinkIcon,
    List,
    Lightbulb,
    Loader2,
    Mail,
    MessageSquare,
    Paintbrush,
    PanelRight,
    Pencil,
    Percent,
    Phone,
    Pin,
    PinOff,
    Plus,
    Printer,
    Redo2,
    RefreshCw,
    Rows3,
    Search,
    Sparkles,
    SlidersHorizontal,
    Tag,
    TrendingUp,
    ToggleLeft,
    Trash2,
    Type,
    Undo2,
    Upload,
    Users,
    X,
} from "lucide-react";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";
import { Component, useCallback, useEffect, useMemo, useRef, useState, type ReactNode } from "react";
import { router } from "@inertiajs/react";
import { DataTableColumnHeader } from "./data-table-column-header";
import { defaultTranslations, type DataTableTranslations } from "./i18n";
import { DataTablePagination } from "./data-table-pagination";
import { DataTableRowActions } from "./data-table-row-actions";
import { DataTableQuickViews } from "./data-table-quick-views";
import type { DataTableAnalytic, DataTableColumnDef, DataTableColumnStats, DataTableConditionalFormatRule, DataTableConfirmOptions, DataTableDensity, DataTableFormField, DataTableHeaderAction, DataTableLayoutMode, DataTableOptions, DataTablePresenceUser, DataTableProps, DataTableRule } from "./types";
import { useDataTable } from "./use-data-table";
import { DataTableColumn, extractColumnConfigs } from "./data-table-column";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Label } from "@/components/ui/label";

// ─── HTML sanitization ──────────────────────────────────────────────────────

/** Strip dangerous HTML tags/attributes. Uses DOMPurify if available, falls back to tag stripping. */
function sanitizeHtml(html: string): string {
    // Use DOMPurify if available (recommended: npm install dompurify)
    if (typeof window !== "undefined" && (window as unknown as Record<string, unknown>).DOMPurify) {
        return ((window as unknown as Record<string, { sanitize: (html: string) => string }>).DOMPurify).sanitize(html);
    }
    // Fallback: strip <script>, <iframe>, <object>, <embed>, on* attributes
    return html
        .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, "")
        .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, "")
        .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, "")
        .replace(/<embed\b[^>]*\/?>/gi, "")
        .replace(/\bon\w+\s*=\s*"[^"]*"/gi, "")
        .replace(/\bon\w+\s*=\s*'[^']*'/gi, "")
        .replace(/javascript\s*:/gi, "");
}

// ─── Toast notification helper ──────────────────────────────────────────────

let toastContainer: HTMLDivElement | null = null;

function showToast(message: string, variant: "success" | "error" | "info" = "info") {
    if (typeof document === "undefined") return;
    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.className = "fixed bottom-4 right-4 z-[9999] flex flex-col gap-2";
        toastContainer.setAttribute("aria-live", "polite");
        toastContainer.setAttribute("role", "status");
        document.body.appendChild(toastContainer);
    }
    const toast = document.createElement("div");
    const bgClass = variant === "error" ? "bg-destructive text-destructive-foreground" : variant === "success" ? "bg-emerald-600 text-white" : "bg-primary text-primary-foreground";
    toast.className = `${bgClass} px-4 py-2 rounded-lg shadow-lg text-sm animate-in slide-in-from-bottom-2 max-w-sm`;
    toast.textContent = message;
    toastContainer.appendChild(toast);
    setTimeout(() => { toast.classList.add("opacity-0", "transition-opacity"); setTimeout(() => toast.remove(), 300); }, 3000);
}

// ─── Lightweight row virtualization ──────────────────────────────────────────

function useVirtualRows(enabled: boolean, containerRef: React.RefObject<HTMLElement | null>, rowCount: number, estimateRowHeight = 40, directionalOverscan = false) {
    const [scrollTop, setScrollTop] = useState(0);
    const [containerHeight, setContainerHeight] = useState(600);
    const lastScrollTopRef = useRef(0);
    const scrollDirectionRef = useRef<"down" | "up">("down");

    useEffect(() => {
        if (!enabled || !containerRef.current) return;
        const el = containerRef.current;
        setContainerHeight(el.clientHeight);
        const handleScroll = () => {
            const newScrollTop = el.scrollTop;
            scrollDirectionRef.current = newScrollTop > lastScrollTopRef.current ? "down" : "up";
            lastScrollTopRef.current = newScrollTop;
            setScrollTop(newScrollTop);
        };
        const handleResize = () => setContainerHeight(el.clientHeight);
        el.addEventListener("scroll", handleScroll, { passive: true });
        const ro = typeof ResizeObserver !== "undefined" ? new ResizeObserver(handleResize) : null;
        ro?.observe(el);
        return () => { el.removeEventListener("scroll", handleScroll); ro?.disconnect(); };
    }, [enabled, containerRef]);

    if (!enabled) return { virtualRows: null, totalHeight: 0, offsetTop: 0, isScrolling: false, scrollToIndex: (_i: number) => {} };

    // Directional overscan: more rows in scroll direction
    const overscanForward = directionalOverscan ? 10 : 5;
    const overscanBackward = directionalOverscan ? 2 : 5;
    const overscanBefore = scrollDirectionRef.current === "down" ? overscanBackward : overscanForward;
    const overscanAfter = scrollDirectionRef.current === "down" ? overscanForward : overscanBackward;
    const totalHeight = rowCount * estimateRowHeight;
    const startIndex = Math.max(0, Math.floor(scrollTop / estimateRowHeight) - overscanBefore);
    const endIndex = Math.min(rowCount, Math.ceil((scrollTop + containerHeight) / estimateRowHeight) + overscanAfter);
    const virtualRows = { startIndex, endIndex };
    const offsetTop = startIndex * estimateRowHeight;

    const scrollToIndex = (index: number) => {
        if (containerRef.current) {
            containerRef.current.scrollTop = index * estimateRowHeight;
        }
    };

    return { virtualRows, totalHeight, offsetTop, isScrolling: false, scrollToIndex };
}

// ─── AutoSizer hook ──────────────────────────────────────────────────────────

function useAutoSizer(enabled: boolean, containerRef: React.RefObject<HTMLElement | null>) {
    const [dimensions, setDimensions] = useState<{ width: number; height: number } | null>(null);

    useEffect(() => {
        if (!enabled || !containerRef.current) return;
        const el = containerRef.current;
        const parent = el.parentElement;
        const updateDimensions = () => {
            // Use parent's width to avoid exceeding overflow-hidden container bounds
            const width = parent ? parent.clientWidth : el.clientWidth;
            setDimensions({ width, height: el.clientHeight });
        };
        updateDimensions();
        const ro = typeof ResizeObserver !== "undefined" ? new ResizeObserver(updateDimensions) : null;
        // Observe the parent so we resize when the container changes, not when the scroll area changes
        if (parent) ro?.observe(parent); else ro?.observe(el);
        return () => { ro?.disconnect(); };
    }, [enabled, containerRef]);

    return dimensions;
}

// ─── Scroll-aware rendering hook ─────────────────────────────────────────────

function useScrollAwareRendering(enabled: boolean, containerRef: React.RefObject<HTMLElement | null>, resetDelay = 150) {
    const [isScrolling, setIsScrolling] = useState(false);
    const timerRef = useRef<ReturnType<typeof setTimeout>>();
    const lastScrollTop = useRef(0);
    const lastScrollTime = useRef(0);

    useEffect(() => {
        if (!enabled || !containerRef.current) return;
        const el = containerRef.current;
        const VELOCITY_THRESHOLD = 2; // px/ms — only activate for fast scroll
        const handleScroll = () => {
            const now = Date.now();
            const dt = now - lastScrollTime.current;
            const dy = Math.abs(el.scrollTop - lastScrollTop.current);
            lastScrollTop.current = el.scrollTop;
            lastScrollTime.current = now;
            if (dt > 0 && dy / dt > VELOCITY_THRESHOLD) {
                setIsScrolling(true);
                if (timerRef.current) clearTimeout(timerRef.current);
                timerRef.current = setTimeout(() => setIsScrolling(false), resetDelay);
            }
        };
        el.addEventListener("scroll", handleScroll, { passive: true });
        return () => { el.removeEventListener("scroll", handleScroll); if (timerRef.current) clearTimeout(timerRef.current); };
    }, [enabled, containerRef, resetDelay]);

    return isScrolling;
}

// ─── CellMeasurer hook ───────────────────────────────────────────────────────

function useCellMeasurer(enabled: boolean) {
    const cache = useRef<Map<string, number>>(new Map());

    const measureCell = useCallback((key: string, element: HTMLElement | null) => {
        if (!enabled || !element) return;
        const height = element.offsetHeight;
        if (height > 0) cache.current.set(key, height);
    }, [enabled]);

    const getCellHeight = useCallback((key: string, defaultHeight: number) => {
        return cache.current.get(key) ?? defaultHeight;
    }, []);

    const clearCache = useCallback(() => {
        cache.current.clear();
    }, []);

    return { measureCell, getCellHeight, clearCache };
}

// ─── Window scroller hook ────────────────────────────────────────────────────

function useWindowScroller(enabled: boolean) {
    const [windowScrollTop, setWindowScrollTop] = useState(0);
    const [windowHeight, setWindowHeight] = useState(typeof window !== "undefined" ? window.innerHeight : 800);

    useEffect(() => {
        if (!enabled || typeof window === "undefined") return;
        const handleScroll = () => setWindowScrollTop(window.scrollY);
        const handleResize = () => setWindowHeight(window.innerHeight);
        window.addEventListener("scroll", handleScroll, { passive: true });
        window.addEventListener("resize", handleResize);
        return () => { window.removeEventListener("scroll", handleScroll); window.removeEventListener("resize", handleResize); };
    }, [enabled]);

    return { windowScrollTop, windowHeight };
}

// ─── Column auto-sizing ──────────────────────────────────────────────────────

function autosizeColumn(tableRef: React.RefObject<HTMLElement | null>, columnId: string): number | null {
    if (!tableRef.current) return null;
    const cells = tableRef.current.querySelectorAll(`[data-column-id="${columnId}"]`);
    let maxWidth = 60; // minimum
    cells.forEach((cell) => {
        const scrollWidth = (cell as HTMLElement).scrollWidth;
        if (scrollWidth > maxWidth) maxWidth = scrollWidth;
    });
    return maxWidth + 16; // padding
}

function autosizeAllColumns(tableRef: React.RefObject<HTMLElement | null>, columnIds: string[]): Record<string, number> {
    const sizes: Record<string, number> = {};
    for (const id of columnIds) {
        const width = autosizeColumn(tableRef, id);
        if (width) sizes[id] = width;
    }
    return sizes;
}

// ─── Column virtualization hook ──────────────────────────────────────────────

function useColumnVirtualization(enabled: boolean, containerRef: React.RefObject<HTMLElement | null>, columnCount: number, estimateColumnWidth = 150) {
    const [scrollLeft, setScrollLeft] = useState(0);
    const [containerWidth, setContainerWidth] = useState(1200);

    useEffect(() => {
        if (!enabled || !containerRef.current) return;
        const el = containerRef.current;
        setContainerWidth(el.clientWidth);
        const handleScroll = () => setScrollLeft(el.scrollLeft);
        const handleResize = () => setContainerWidth(el.clientWidth);
        el.addEventListener("scroll", handleScroll, { passive: true });
        const ro = typeof ResizeObserver !== "undefined" ? new ResizeObserver(handleResize) : null;
        ro?.observe(el);
        return () => { el.removeEventListener("scroll", handleScroll); ro?.disconnect(); };
    }, [enabled, containerRef]);

    if (!enabled) return { visibleColumnRange: null };

    const overscan = 2;
    const startIndex = Math.max(0, Math.floor(scrollLeft / estimateColumnWidth) - overscan);
    const endIndex = Math.min(columnCount, Math.ceil((scrollLeft + containerWidth) / estimateColumnWidth) + overscan);
    return { visibleColumnRange: { startIndex, endIndex } };
}

// ─── Cell range selection hook ───────────────────────────────────────────────

function useCellRangeSelection(enabled: boolean) {
    const [rangeStart, setRangeStart] = useState<{ row: number; col: string } | null>(null);
    const [rangeEnd, setRangeEnd] = useState<{ row: number; col: string } | null>(null);
    const [isSelecting, setIsSelecting] = useState(false);

    const startSelection = useCallback((row: number, col: string) => {
        if (!enabled) return;
        setRangeStart({ row, col });
        setRangeEnd({ row, col });
        setIsSelecting(true);
    }, [enabled]);

    const updateSelection = useCallback((row: number, col: string) => {
        if (!enabled || !isSelecting) return;
        setRangeEnd({ row, col });
    }, [enabled, isSelecting]);

    const endSelection = useCallback(() => {
        setIsSelecting(false);
    }, []);

    const clearSelection = useCallback(() => {
        setRangeStart(null);
        setRangeEnd(null);
        setIsSelecting(false);
    }, []);

    const isCellInRange = useCallback((row: number, col: string, allColumns: string[]) => {
        if (!rangeStart || !rangeEnd) return false;
        const minRow = Math.min(rangeStart.row, rangeEnd.row);
        const maxRow = Math.max(rangeStart.row, rangeEnd.row);
        if (row < minRow || row > maxRow) return false;
        const startColIdx = allColumns.indexOf(rangeStart.col);
        const endColIdx = allColumns.indexOf(rangeEnd.col);
        const colIdx = allColumns.indexOf(col);
        const minCol = Math.min(startColIdx, endColIdx);
        const maxCol = Math.max(startColIdx, endColIdx);
        return colIdx >= minCol && colIdx <= maxCol;
    }, [rangeStart, rangeEnd]);

    const selectedCellCount = useMemo(() => {
        if (!rangeStart || !rangeEnd) return 0;
        const rowSpan = Math.abs(rangeEnd.row - rangeStart.row) + 1;
        return rowSpan; // simplified count
    }, [rangeStart, rangeEnd]);

    return { startSelection, updateSelection, endSelection, clearSelection, isCellInRange, selectedCellCount, rangeStart, rangeEnd };
}

// ─── Sparkline mini-chart component ──────────────────────────────────────────

function SparklineChart({ data, type = "line", width = 80, height = 20 }: { data: number[]; type?: "line" | "bar"; width?: number; height?: number }) {
    if (!data || data.length === 0) return null;
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;

    if (type === "bar") {
        const barWidth = width / data.length;
        return (
            <svg width={width} height={height} className="inline-block align-middle">
                {data.map((val, i) => {
                    const barHeight = ((val - min) / range) * height;
                    return <rect key={i} x={i * barWidth} y={height - barHeight} width={barWidth - 1} height={barHeight} className="fill-primary/60" />;
                })}
            </svg>
        );
    }

    // Line chart
    const points = data.map((val, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - ((val - min) / range) * height;
        return `${x},${y}`;
    }).join(" ");

    return (
        <svg width={width} height={height} className="inline-block align-middle">
            <polyline points={points} fill="none" className="stroke-primary" strokeWidth="1.5" />
        </svg>
    );
}

// ─── Analytics KPI Card (zero dependencies) ─────────────────────────────────

function AnalyticsCard({ card, t }: { card: DataTableAnalytic; t: DataTableTranslations }) {
    const formattedValue = useMemo(() => {
        const v = card.value;
        if (typeof v === "string") return v;
        const num = typeof v === "number" ? v : parseFloat(String(v));
        if (isNaN(num)) return String(v);

        switch (card.format) {
            case "currency":
                return `${card.prefix ?? "$"}${num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            case "percentage":
                return `${num.toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 })}%`;
            default:
                return num.toLocaleString();
        }
    }, [card.value, card.format, card.prefix]);

    const changeColor = card.change != null
        ? card.change > 0 ? "text-emerald-600 dark:text-emerald-400"
        : card.change < 0 ? "text-red-600 dark:text-red-400"
        : "text-muted-foreground"
        : null;

    const changeArrow = card.change != null
        ? card.change > 0 ? "\u2191" : card.change < 0 ? "\u2193" : ""
        : null;

    return (
        <div className="flex flex-col gap-1 rounded-md border border-border/50 p-3">
            <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">{card.label}</span>
                {card.icon && <span className="text-lg text-muted-foreground/60">{card.icon}</span>}
            </div>
            <div className="flex items-baseline gap-2">
                <span className={cn("text-xl font-semibold tracking-tight", card.color)}>
                    {card.format !== "currency" && card.prefix}{formattedValue}{card.suffix}
                </span>
                {card.change != null && (
                    <span className={cn("text-sm font-medium", changeColor)}>
                        {changeArrow} {Math.abs(card.change).toFixed(1)}%
                    </span>
                )}
            </div>
            {card.description && (
                <span className="text-xs text-muted-foreground">{card.description}</span>
            )}
        </div>
    );
}

function AnalyticsSection<TData extends object>({
    analytics,
    slot,
    data,
    columns,
    t,
}: {
    analytics: DataTableAnalytic[];
    slot?: React.ReactNode | ((props: { data: TData[]; columns: DataTableColumnDef[]; analytics: DataTableAnalytic[] }) => React.ReactNode);
    data: TData[];
    columns: DataTableColumnDef[];
    t: DataTableTranslations;
}) {
    // Custom slot — either a render function or static ReactNode
    if (slot) {
        if (typeof slot === "function") {
            return <>{slot({ data, columns, analytics })}</>;
        }
        return <>{slot}</>;
    }

    // Default: built-in KPI cards grid
    if (analytics.length === 0) return null;

    return (
        <div className={cn(
            "grid gap-4 print:hidden",
            analytics.length === 1 && "grid-cols-1",
            analytics.length === 2 && "grid-cols-1 sm:grid-cols-2",
            analytics.length === 3 && "grid-cols-1 sm:grid-cols-3",
            analytics.length >= 4 && "grid-cols-2 sm:grid-cols-4",
        )}>
            {analytics.map((card, i) => (
                <AnalyticsCard key={`${card.label}-${i}`} card={card} t={t} />
            ))}
        </div>
    );
}

// ─── Safe localStorage helpers ──────────────────────────────────────────────

function safeGetItem(key: string): string | null {
    try { return localStorage.getItem(key); }
    catch { return null; }
}

function safeSetItem(key: string, value: string): void {
    try { localStorage.setItem(key, value); }
    catch { /* storage full or unavailable */ }
}

// ─── Cell flashing hook ──────────────────────────────────────────────────────

/** Track previous cell values and return a set of "rowId:colId" keys that just changed. */
function useCellFlashing(enabled: boolean, data: unknown[], columns: { id: string }[]) {
    const prevDataRef = useRef<Map<string, unknown>>(new Map());
    const [flashingCells, setFlashingCells] = useState<Set<string>>(new Set());

    useEffect(() => {
        if (!enabled || data.length === 0) return;
        const prev = prevDataRef.current;
        const changed = new Set<string>();
        const next = new Map<string, unknown>();

        for (let i = 0; i < data.length; i++) {
            const row = data[i] as Record<string, unknown>;
            const rowId = String(row.id ?? i);
            for (const col of columns) {
                const key = `${rowId}:${col.id}`;
                const val = row[col.id];
                next.set(key, val);
                if (prev.has(key) && prev.get(key) !== val) {
                    changed.add(key);
                }
            }
        }

        prevDataRef.current = next;
        if (changed.size > 0) {
            setFlashingCells(changed);
            const timer = setTimeout(() => setFlashingCells(new Set()), 1500);
            return () => clearTimeout(timer);
        }
    }, [enabled, data, columns]);

    return flashingCells;
}

// ─── Status bar aggregation ──────────────────────────────────────────────────

function computeStatusBarAggregates(
    selectedRows: Record<string, unknown>[],
    columns: { id: string; type: string }[],
): { sum: number; avg: number; count: number; min: number; max: number } | null {
    const numericValues: number[] = [];
    for (const row of selectedRows) {
        for (const col of columns) {
            if (col.type === "number" || col.type === "currency" || col.type === "percentage") {
                const val = row[col.id];
                if (typeof val === "number") numericValues.push(val);
                else if (typeof val === "string") { const n = parseFloat(val); if (!isNaN(n)) numericValues.push(n); }
            }
        }
    }
    if (numericValues.length === 0) return null;
    const sum = numericValues.reduce((a, b) => a + b, 0);
    return {
        sum,
        avg: sum / numericValues.length,
        count: numericValues.length,
        min: Math.min(...numericValues),
        max: Math.max(...numericValues),
    };
}

// ─── Column meta type ───────────────────────────────────────────────────────

interface ColumnMeta {
    type?: string;
    group?: string | null;
    editable?: boolean;
    currency?: string;
    currencyColumn?: string | null;
    locale?: string;
    toggleable?: boolean;
    prefix?: string | null;
    suffix?: string | null;
    tooltip?: string | null;
    description?: string | null;
    lineClamp?: number | null;
    iconMap?: Record<string, string> | null;
    colorMap?: Record<string, string> | null;
    selectOptions?: { label: string; value: string }[] | null;
    html?: boolean;
    markdown?: boolean;
    bulleted?: boolean;
    stacked?: string[] | null;
    rowIndex?: boolean;
    avatarColumn?: string | null;
    hasDynamicSuffix?: boolean;
    computedFrom?: string[] | null;
    colSpan?: number | null;
    autoHeight?: boolean;
    valueGetter?: string | null;
    valueFormatter?: string | null;
    headerFilter?: boolean;
    sparkline?: string | null;
    treeParent?: string | null;
}

// ─── Error Boundary ─────────────────────────────────────────────────────────

interface ErrorBoundaryProps {
    children: React.ReactNode;
    fallback?: React.ReactNode;
}

interface ErrorBoundaryState {
    hasError: boolean;
    error?: Error;
}

class DataTableErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
    constructor(props: ErrorBoundaryProps) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error: Error): ErrorBoundaryState {
        return { hasError: true, error };
    }

    render() {
        if (this.state.hasError) {
            return this.props.fallback ?? (
                <div className="flex flex-col items-center justify-center gap-3 rounded-xl border border-destructive/20 bg-destructive/5 p-8 text-center shadow-sm">
                    <p className="text-sm font-medium text-destructive">Something went wrong rendering the table.</p>
                    <p className="text-xs text-muted-foreground">{this.state.error?.message}</p>
                    <Button variant="outline" size="sm" onClick={() => this.setState({ hasError: false })}>
                        Try again
                    </Button>
                </div>
            );
        }
        return this.props.children;
    }
}

// ─── Utility functions ──────────────────────────────────────────────────────

function buildExportUrl(baseUrl: string, format: string, visibleColumns?: string[]): string {
    const currentParams = new URL(window.location.href).searchParams;
    const exportUrl = new URL(baseUrl, window.location.origin);
    for (const [key, value] of currentParams.entries()) {
        exportUrl.searchParams.set(key, value);
    }
    exportUrl.searchParams.set("format", format);
    if (visibleColumns?.length) {
        exportUrl.searchParams.set("columns", visibleColumns.join(","));
    }
    return exportUrl.toString();
}

function getColumnPinningProps<T>(column: Column<T, unknown>) {
    const isPinned = column.getIsPinned();
    if (!isPinned) return { style: {} as React.CSSProperties, className: "" };
    return {
        style: {
            position: "sticky" as const,
            left: isPinned === "left" ? `${column.getStart("left")}px` : undefined,
            right: isPinned === "right" ? `${column.getAfter("right")}px` : undefined,
            zIndex: 1,
        } as React.CSSProperties,
        className: cn(
            "bg-background",
            isPinned === "left" && column.getIsLastColumn("left") && "shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]",
            isPinned === "right" && column.getIsFirstColumn("right") && "shadow-[-2px_0_4px_-2px_rgba(0,0,0,0.06)]",
        ),
    };
}

const BADGE_VARIANTS: Record<string, string> = {
    default: "bg-primary/10 text-primary ring-1 ring-inset ring-primary/20 dark:bg-primary/20",
    success: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20",
    warning: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20",
    danger: "bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20",
    info: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20",
    secondary: "bg-muted text-muted-foreground ring-1 ring-inset ring-border",
};

// ─── Density configuration ──────────────────────────────────────────────────

const DENSITY_CLASSES: Record<DataTableDensity, { cell: string; row: string }> = {
    compact: { cell: "py-1 text-xs", row: "h-8" },
    comfortable: { cell: "py-2", row: "" },
    spacious: { cell: "py-3", row: "h-14" },
};

function loadDensity(tableName: string): DataTableDensity {
    const stored = safeGetItem(`dt-density-${tableName}`);
    if (stored === "compact" || stored === "comfortable" || stored === "spacious") return stored;
    return "comfortable";
}

function saveDensity(tableName: string, density: DataTableDensity) {
    safeSetItem(`dt-density-${tableName}`, density);
}

// ─── Search highlighting ────────────────────────────────────────────────────

function highlightText(text: string, query: string): React.ReactNode {
    if (!query || !text) return text;
    const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const parts = text.split(new RegExp(`(${escaped})`, "gi"));
    if (parts.length === 1) return text;
    return parts.map((part, i) =>
        part.toLowerCase() === query.toLowerCase()
            ? <mark key={i} className="bg-yellow-200 dark:bg-yellow-800 rounded-sm px-0.5">{part}</mark>
            : part
    );
}

// ─── Cell copy to clipboard ─────────────────────────────────────────────────

function CopyableCell({ value, children, enabled, t }: { value: unknown; children: React.ReactNode; enabled: boolean; t: DataTableTranslations }) {
    const [copied, setCopied] = useState(false);

    const handleCopy = useCallback(async (e: React.MouseEvent) => {
        e.stopPropagation();
        try {
            await navigator.clipboard.writeText(String(value ?? ""));
            setCopied(true);
            setTimeout(() => setCopied(false), 1500);
        } catch { /* clipboard API may not be available */ }
    }, [value]);

    if (!enabled) return <>{children}</>;

    return (
        <div className="group/copy relative inline-flex items-center gap-1">
            {children}
            <button type="button" onClick={handleCopy}
                className="opacity-0 group-hover/copy:opacity-100 transition-opacity p-0.5 rounded hover:bg-muted"
                title={t.copyToClipboard}>
                {copied ? <Check className="h-3 w-3 text-emerald-600" /> : <Clipboard className="h-3 w-3 text-muted-foreground" />}
            </button>
        </div>
    );
}

// ─── Column header context menu ─────────────────────────────────────────────

function ColumnContextMenu({ columnId, sortable, isPinned, showPinning, onSort, onHide, onPin, t, children }: {
    columnId: string; sortable: boolean; isPinned: false | "left" | "right"; showPinning: boolean;
    onSort: (columnId: string, multi: boolean) => void;
    onHide: (columnId: string) => void;
    onPin: (columnId: string, direction: false | "left" | "right") => void;
    t: DataTableTranslations; children: React.ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const [pos, setPos] = useState({ x: 0, y: 0 });

    const handleContextMenu = useCallback((e: React.MouseEvent) => {
        e.preventDefault();
        setPos({ x: e.clientX, y: e.clientY });
        setOpen(true);
    }, []);

    return (
        <div onContextMenu={handleContextMenu}>
            {children}
            {open && (
                <>
                    <div className="fixed inset-0 z-50" onClick={() => setOpen(false)} />
                    <div className="fixed z-50 min-w-[160px] rounded-lg border bg-popover p-1 shadow-lg animate-in fade-in-0 zoom-in-95"
                        style={{ left: pos.x, top: pos.y }}>
                        {sortable && (
                            <>
                                <button type="button" className="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                                    onClick={() => { onSort(columnId, false); setOpen(false); }}>
                                    <ArrowUp className="h-3.5 w-3.5" />{t.sortAscending}
                                </button>
                                <button type="button" className="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                                    onClick={() => { onSort(columnId, false); onSort(columnId, false); setOpen(false); }}>
                                    <ArrowDown className="h-3.5 w-3.5" />{t.sortDescending}
                                </button>
                                <div className="my-1 h-px bg-border" />
                            </>
                        )}
                        <button type="button" className="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                            onClick={() => { onHide(columnId); setOpen(false); }}>
                            <EyeOff className="h-3.5 w-3.5" />{t.hideColumn}
                        </button>
                        {showPinning && (
                            <ColumnPinMenu columnId={columnId} isPinned={isPinned}
                                onPin={(id, dir) => { onPin(id, dir); setOpen(false); }} t={t} />
                        )}
                    </div>
                </>
            )}
        </div>
    );
}

// ─── Batch edit dialog ──────────────────────────────────────────────────────

function BatchEditDialog<TData>({ open, onOpenChange, selectedRows, editableColumns, onApply, t }: {
    open: boolean; onOpenChange: (open: boolean) => void;
    selectedRows: TData[]; editableColumns: DataTableColumnDef[];
    onApply: (columnId: string, value: unknown) => void; t: DataTableTranslations;
}) {
    const [selectedColumn, setSelectedColumn] = useState(editableColumns[0]?.id ?? "");
    const [editValue, setEditValue] = useState("");

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{t.batchEdit}</DialogTitle>
                    <DialogDescription>{t.selected(selectedRows.length)}</DialogDescription>
                </DialogHeader>
                <div className="grid gap-3 py-2">
                    <div className="grid gap-2">
                        <Label>{t.batchEditColumn}</Label>
                        <Select value={selectedColumn} onValueChange={setSelectedColumn}>
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                {editableColumns.map((col) => (
                                    <SelectItem key={col.id} value={col.id}>{col.label}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="grid gap-2">
                        <Label>{t.batchEditValue}</Label>
                        <Input value={editValue} onChange={(e) => setEditValue(e.target.value)}
                            onKeyDown={(e) => { if (e.key === "Enter") { onApply(selectedColumn, editValue); onOpenChange(false); } }} />
                    </div>
                </div>
                <DialogFoot>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>{t.cancel}</Button>
                    <Button onClick={() => { onApply(selectedColumn, editValue); onOpenChange(false); }}>{t.batchEditApply}</Button>
                </DialogFoot>
            </DialogContent>
        </Dialog>
    );
}

// ─── Row drag handle for reorder ────────────────────────────────────────────

function DragHandleCell({ rowIndex, onDragStart, onDragOver, onDragEnd }: {
    rowIndex: number;
    onDragStart: (index: number) => void;
    onDragOver: (e: React.DragEvent, index: number) => void;
    onDragEnd: () => void;
}) {
    return (
        <div className="cursor-grab active:cursor-grabbing"
            draggable
            onDragStart={(e) => { e.dataTransfer.effectAllowed = "move"; onDragStart(rowIndex); }}
            onDragOver={(e) => { e.preventDefault(); onDragOver(e, rowIndex); }}
            onDragEnd={onDragEnd}>
            <GripVertical className="h-4 w-4 text-muted-foreground/50" />
        </div>
    );
}

// ─── Import dialog ──────────────────────────────────────────────────────────

function ImportDialog({ open, onOpenChange, importUrl, t }: {
    open: boolean; onOpenChange: (open: boolean) => void;
    importUrl: string; t: DataTableTranslations;
}) {
    const [uploading, setUploading] = useState(false);
    const [result, setResult] = useState<{ success: boolean; message: string } | null>(null);
    const fileRef = useRef<HTMLInputElement>(null);

    const handleUpload = useCallback(() => {
        const file = fileRef.current?.files?.[0];
        if (!file) return;
        setUploading(true);
        setResult(null);
        router.post(importUrl, { file } as Record<string, unknown>, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setResult({ success: true, message: t.importSuccess });
                setTimeout(() => { onOpenChange(false); router.reload(); }, 1000);
            },
            onError: (errors) => {
                const msg = typeof errors === "object" && errors !== null
                    ? Object.values(errors).flat().join(", ") : t.importError;
                setResult({ success: false, message: msg || t.importError });
            },
            onFinish: () => setUploading(false),
        });
    }, [importUrl, t, onOpenChange]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{t.importData}</DialogTitle>
                </DialogHeader>
                <div className="grid gap-3 py-2">
                    <input ref={fileRef} type="file" accept=".csv,.xlsx,.xls" className="text-sm" />
                    {result && (
                        <p className={cn("text-sm", result.success ? "text-emerald-600" : "text-destructive")}>{result.message}</p>
                    )}
                </div>
                <DialogFoot>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>{t.cancel}</Button>
                    <Button onClick={handleUpload} disabled={uploading}>
                        {uploading ? <><Loader2 className="mr-2 h-4 w-4 animate-spin" />{t.importUploading}</> : t.importData}
                    </Button>
                </DialogFoot>
            </DialogContent>
        </Dialog>
    );
}

// ─── Undo/Redo stack for inline edits ───────────────────────────────────────

interface EditAction {
    rowId: unknown;
    columnId: string;
    oldValue: unknown;
    newValue: unknown;
    timestamp: number;
}

function useUndoRedo(enabled: boolean) {
    const [undoStack, setUndoStack] = useState<EditAction[]>([]);
    const [redoStack, setRedoStack] = useState<EditAction[]>([]);

    const pushEdit = useCallback((action: Omit<EditAction, "timestamp">) => {
        if (!enabled) return;
        setUndoStack((prev) => [...prev.slice(-49), { ...action, timestamp: Date.now() }]);
        setRedoStack([]);
    }, [enabled]);

    const undo = useCallback((): EditAction | null => {
        if (undoStack.length === 0) return null;
        const action = undoStack[undoStack.length - 1];
        setUndoStack((prev) => prev.slice(0, -1));
        setRedoStack((prev) => [...prev, action]);
        return action;
    }, [undoStack]);

    const redo = useCallback((): EditAction | null => {
        if (redoStack.length === 0) return null;
        const action = redoStack[redoStack.length - 1];
        setRedoStack((prev) => prev.slice(0, -1));
        setUndoStack((prev) => [...prev, action]);
        return action;
    }, [redoStack]);

    // Cleanup on unmount to prevent memory leaks
    useEffect(() => {
        return () => { setUndoStack([]); setRedoStack([]); };
    }, []);

    return { pushEdit, undo, redo, canUndo: undoStack.length > 0, canRedo: redoStack.length > 0 };
}

// ─── Keyboard shortcuts overlay ─────────────────────────────────────────────

function KeyboardShortcutsDialog({ open, onOpenChange, t }: {
    open: boolean; onOpenChange: (open: boolean) => void; t: DataTableTranslations;
}) {
    const shortcuts = [
        { keys: ["↑", "↓"], description: t.shortcutNavigation },
        { keys: ["Space"], description: t.shortcutSelect },
        { keys: ["Enter"], description: t.shortcutExpand },
        { keys: ["Escape"], description: t.shortcutEscape },
        { keys: ["/"], description: t.shortcutSearch },
        { keys: ["?"], description: t.shortcutHelp },
    ];

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Keyboard className="h-5 w-5" />{t.keyboardShortcuts}
                    </DialogTitle>
                </DialogHeader>
                <div className="grid gap-2 py-2">
                    {shortcuts.map(({ keys, description }) => (
                        <div key={description} className="flex items-center justify-between py-1.5">
                            <span className="text-sm text-muted-foreground">{description}</span>
                            <div className="flex items-center gap-1">
                                {keys.map((key) => (
                                    <kbd key={key} className="inline-flex h-6 min-w-[24px] items-center justify-center rounded border bg-muted px-1.5 font-mono text-xs font-medium">
                                        {key}
                                    </kbd>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
                <DialogFoot>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>{t.close}</Button>
                </DialogFoot>
            </DialogContent>
        </Dialog>
    );
}

// ─── Export with progress ────────────────────────────────────────────────────

function ExportWithProgress({ exportUrl, table, t }: {
    exportUrl: string; table: TanStackTable<unknown>; t: DataTableTranslations;
}) {
    const [exporting, setExporting] = useState<string | null>(null);
    const [downloadUrl, setDownloadUrl] = useState<string | null>(null);

    const handleExport = useCallback(async (format: string) => {
        setExporting(format);
        setDownloadUrl(null);
        const url = buildExportUrl(exportUrl, format, table.getVisibleLeafColumns().filter((c) => c.getCanHide()).map((c) => c.id));
        try {
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            if (response.ok) {
                const blob = await response.blob();
                const blobUrl = URL.createObjectURL(blob);
                setDownloadUrl(blobUrl);
                // Auto-download
                const a = document.createElement("a");
                a.href = blobUrl;
                a.download = `export.${format}`;
                a.click();
            }
        } catch { /* fallback to direct download */ }
        finally { setExporting(null); }
    }, [exportUrl, table]);

    const formats = [
        { id: "xlsx", label: "Excel (.xlsx)", icon: FileSpreadsheet },
        { id: "csv", label: "CSV (.csv)", icon: FileText },
        { id: "pdf", label: "PDF (.pdf)", icon: FileDown },
    ];

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="h-8 gap-1.5">
                    {exporting ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Download className="h-3.5 w-3.5" />}
                    <span className="hidden sm:inline">{exporting ? t.exporting : t.export}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>{t.exportFormat}</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {formats.map(({ id, label, icon: Icon }) => (
                    <DropdownMenuItem key={id} onClick={() => handleExport(id)} disabled={!!exporting}>
                        <Icon className="mr-2 h-4 w-4" />{label}
                        {exporting === id && <Loader2 className="ml-auto h-3.5 w-3.5 animate-spin" />}
                    </DropdownMenuItem>
                ))}
                {downloadUrl && (
                    <>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <a href={downloadUrl} download>
                                <Check className="mr-2 h-4 w-4 text-emerald-600" />{t.exportDownload}
                            </a>
                        </DropdownMenuItem>
                    </>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

// ─── Empty state illustration ───────────────────────────────────────────────

function DefaultEmptyStateIllustration() {
    return (
        <svg className="h-24 w-24 text-muted-foreground/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <path d="M3 9h18" />
            <path d="M3 15h18" />
            <path d="M9 3v18" />
            <path d="M15 3v18" />
        </svg>
    );
}

function EmptyState({ customEmpty, illustration, showIllustration, t }: {
    customEmpty?: React.ReactNode; illustration?: React.ReactNode;
    showIllustration: boolean; t: DataTableTranslations;
}) {
    if (customEmpty) return <>{customEmpty}</>;
    if (showIllustration) {
        return (
            <div className="flex flex-col items-center gap-3">
                {illustration ?? <DefaultEmptyStateIllustration />}
                <div className="text-center">
                    <p className="font-medium text-muted-foreground">{t.emptyTitle}</p>
                    <p className="text-xs text-muted-foreground/70 max-w-xs">{t.emptyDescription}</p>
                </div>
            </div>
        );
    }
    return <span className="text-muted-foreground">{t.noData}</span>;
}

// ─── Selection persistence across pages ─────────────────────────────────────

function usePersistedSelection<TData>(tableName: string, enabled: boolean) {
    const storageKey = `dt-selection-${tableName}`;

    const [persistedIds, setPersistedIds] = useState<Set<unknown>>(() => {
        if (!enabled) return new Set();
        try {
            const raw = safeGetItem(storageKey);
            return raw ? new Set(JSON.parse(raw) as unknown[]) : new Set();
        } catch { return new Set(); }
    });

    const addIds = useCallback((ids: unknown[]) => {
        setPersistedIds((prev) => {
            const next = new Set(prev);
            for (const id of ids) next.add(id);
            return next;
        });
    }, []);

    const removeIds = useCallback((ids: unknown[]) => {
        setPersistedIds((prev) => {
            const next = new Set(prev);
            for (const id of ids) next.delete(id);
            return next;
        });
    }, []);

    const clearAll = useCallback(() => setPersistedIds(new Set()), []);

    const isSelected = useCallback((id: unknown) => persistedIds.has(id), [persistedIds]);

    // Persist to localStorage
    useEffect(() => {
        if (!enabled) return;
        safeSetItem(storageKey, JSON.stringify([...persistedIds]));
    }, [enabled, storageKey, persistedIds]);

    return { persistedIds, addIds, removeIds, clearAll, isSelected, count: persistedIds.size };
}

// ─── Column pinning UI in context menu ──────────────────────────────────────

function ColumnPinMenu({ columnId, isPinned, onPin, t }: {
    columnId: string; isPinned: false | "left" | "right";
    onPin: (columnId: string, direction: false | "left" | "right") => void;
    t: DataTableTranslations;
}) {
    return (
        <>
            <div className="my-1 h-px bg-border" />
            {!isPinned ? (
                <>
                    <button type="button" className="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                        onClick={() => onPin(columnId, "left")}>
                        <Pin className="h-3.5 w-3.5" />{t.pinLeft}
                    </button>
                    <button type="button" className="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                        onClick={() => onPin(columnId, "right")}>
                        <Pin className="h-3.5 w-3.5 rotate-90" />{t.pinRight}
                    </button>
                </>
            ) : (
                <button type="button" className="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                    onClick={() => onPin(columnId, false)}>
                    <PinOff className="h-3.5 w-3.5" />{t.unpin}
                </button>
            )}
        </>
    );
}

/** Evaluate a conditional rule against a row value */
function evaluateRule(rule: DataTableRule, rowValue: unknown): boolean {
    const v = rowValue;
    const target = rule.value;
    switch (rule.operator) {
        case "eq": return v === target || String(v) === String(target);
        case "neq": return v !== target && String(v) !== String(target);
        case "gt": return Number(v) > Number(target);
        case "gte": return Number(v) >= Number(target);
        case "lt": return Number(v) < Number(target);
        case "lte": return Number(v) <= Number(target);
        case "contains": return String(v).toLowerCase().includes(String(target).toLowerCase());
        case "starts_with": return String(v).toLowerCase().startsWith(String(target).toLowerCase());
        case "ends_with": return String(v).toLowerCase().endsWith(String(target).toLowerCase());
        case "is_null": return v === null || v === undefined;
        case "is_not_null": return v !== null && v !== undefined;
        case "is_empty": return v === "" || v === null || v === undefined;
        case "is_true": return v === true || v === 1 || v === "1";
        case "is_false": return v === false || v === 0 || v === "0";
        default: return false;
    }
}

// ─── Sub-components ─────────────────────────────────────────────────────────

/** Inline editable cell component */
function InlineEditCell({ value: initialValue, columnId, columnType, onSave, t }: {
    value: unknown; columnId: string; columnType: string;
    onSave: (value: unknown) => Promise<void> | void; t: DataTableTranslations;
}) {
    const [editing, setEditing] = useState(false);
    const [editValue, setEditValue] = useState(String(initialValue ?? ""));
    const [saving, setSaving] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => { if (editing) inputRef.current?.focus(); }, [editing]);

    const handleSave = useCallback(async () => {
        setSaving(true);
        try {
            const parsed = columnType === "number" || columnType === "currency" || columnType === "percentage"
                ? Number(editValue) : editValue;
            await onSave(parsed);
            setEditing(false);
            showToast("Saved", "success");
        } catch (e) {
            showToast(e instanceof Error ? e.message : "Save failed", "error");
        } finally { setSaving(false); }
    }, [editValue, columnType, onSave]);

    if (!editing) {
        return (
            <span className="cursor-pointer rounded px-1 -mx-1 hover:bg-muted/80 transition-colors"
                onDoubleClick={() => { setEditValue(String(initialValue ?? "")); setEditing(true); }}
                title="Double-click to edit">
                {initialValue === null || initialValue === undefined
                    ? <span className="text-muted-foreground/50 text-xs">&mdash;</span> : String(initialValue)}
            </span>
        );
    }

    return (
        <div className="flex items-center gap-1">
            <Input ref={inputRef}
                type={columnType === "number" || columnType === "currency" || columnType === "percentage" ? "number" : "text"}
                value={editValue} onChange={(e) => setEditValue(e.target.value)}
                onKeyDown={(e) => { if (e.key === "Enter") handleSave(); if (e.key === "Escape") setEditing(false); }}
                className="h-7 w-auto min-w-[80px] text-sm" disabled={saving} />
            {saving && <Loader2 className="h-3.5 w-3.5 animate-spin text-muted-foreground" />}
        </div>
    );
}

/** Boolean toggle switch cell — uses Inertia router.patch for proper session/CSRF handling */
function ToggleCell({ value, row, columnId, toggleUrl }: {
    value: boolean; row: Record<string, unknown>; columnId: string; toggleUrl: string;
}) {
    const [checked, setChecked] = useState(!!value);
    const [saving, setSaving] = useState(false);

    const handleToggle = useCallback((newValue: boolean) => {
        setSaving(true);
        setChecked(newValue);
        const rowId = row.id ?? "";
        const url = `${toggleUrl}/${rowId}`;
        router.patch(url, { column: columnId, value: newValue } as Record<string, unknown>, {
            preserveScroll: true,
            preserveState: true,
            onError: (errors) => {
                setChecked(!newValue);
                const msg = typeof errors === "object" && errors !== null ? Object.values(errors)[0] : "Toggle failed";
                showToast(String(msg), "error");
            },
            onFinish: () => setSaving(false),
        });
    }, [row.id, toggleUrl, columnId]);

    return <Checkbox checked={checked} onCheckedChange={handleToggle} disabled={saving}
        className="data-[state=checked]:bg-emerald-500 data-[state=checked]:border-emerald-500"
        aria-label={`Toggle ${columnId}`} />;
}

function DensityToggle({ density, onChange, t }: {
    density: DataTableDensity; onChange: (density: DataTableDensity) => void; t: DataTableTranslations;
}) {
    const densities: { value: DataTableDensity; label: string }[] = [
        { value: "compact", label: t.densityCompact },
        { value: "comfortable", label: t.densityComfortable },
        { value: "spacious", label: t.densitySpacious },
    ];
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="h-8 gap-1.5">
                    <Rows3 className="h-3.5 w-3.5" /><span className="hidden sm:inline">{t.density}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {densities.map((d) => (
                    <DropdownMenuItem key={d.value} onClick={() => onChange(d.value)}
                        className={cn(density === d.value && "font-semibold")}>
                        {density === d.value && <Check className="mr-2 h-3.5 w-3.5" />}
                        <span className={density !== d.value ? "ml-6" : ""}>{d.label}</span>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function DataTableToolbar<TData>({ tableData, table, tableName, columnVisibility, columnOrder, applyColumns, onReorderColumns, handleApplyQuickView, handleApplyCustomSearch, resolvedOptions, t, density, onDensityChange, onImportClick, onShowShortcuts, canUndo, canRedo, onUndo, onRedo }: {
    tableData: { quickViews: import("./types").DataTableQuickView[]; exportUrl?: string | null; importUrl?: string | null; columns: DataTableColumnDef[] };
    table: TanStackTable<TData>; tableName: string;
    columnVisibility: VisibilityState; columnOrder: ColumnOrderState;
    applyColumns: (columnIds: string[]) => void; onReorderColumns: (order: ColumnOrderState) => void;
    handleApplyQuickView: (params: Record<string, unknown>) => void;
    handleApplyCustomSearch: (search: string) => void;
    resolvedOptions: DataTableOptions; t: DataTableTranslations;
    density: DataTableDensity; onDensityChange: (density: DataTableDensity) => void;
    onImportClick?: () => void; onShowShortcuts?: () => void;
    canUndo?: boolean; canRedo?: boolean; onUndo?: () => void; onRedo?: () => void;
}) {
    return (
        <div className="flex items-center gap-2">
            {(resolvedOptions.quickViews || resolvedOptions.customQuickViews) && (
                <DataTableQuickViews quickViews={resolvedOptions.quickViews ? tableData.quickViews : []}
                    tableName={tableName} columnVisibility={columnVisibility} columnOrder={columnOrder}
                    allColumns={tableData.columns} onSelect={handleApplyQuickView}
                    onApplyCustom={handleApplyCustomSearch} onApplyColumns={applyColumns}
                    onApplyColumnOrder={onReorderColumns} enableCustom={resolvedOptions.customQuickViews} t={t} />
            )}
            {resolvedOptions.exports && tableData.exportUrl && (
                resolvedOptions.exportProgress ? (
                    <ExportWithProgress exportUrl={tableData.exportUrl} table={table as TanStackTable<unknown>} t={t} />
                ) : (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" size="sm" className="h-8 gap-1.5"><Download className="h-3.5 w-3.5" /><span className="hidden sm:inline">{t.export}</span></Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>{t.exportFormat}</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <a href={buildExportUrl(tableData.exportUrl, "xlsx", table.getVisibleLeafColumns().filter((c) => c.getCanHide()).map((c) => c.id))}>
                                    <FileSpreadsheet className="mr-2 h-4 w-4" />Excel (.xlsx)</a>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                                <a href={buildExportUrl(tableData.exportUrl, "csv", table.getVisibleLeafColumns().filter((c) => c.getCanHide()).map((c) => c.id))}>
                                    <FileText className="mr-2 h-4 w-4" />CSV (.csv)</a>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                                <a href={buildExportUrl(tableData.exportUrl, "pdf", table.getVisibleLeafColumns().filter((c) => c.getCanHide()).map((c) => c.id))}>
                                    <FileDown className="mr-2 h-4 w-4" />PDF (.pdf)</a>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                )
            )}
            {tableData.importUrl && onImportClick && (
                <Button variant="outline" size="sm" className="h-8 gap-1.5" onClick={onImportClick}>
                    <Upload className="h-3.5 w-3.5" /><span className="hidden sm:inline">{t.importData}</span>
                </Button>
            )}
            {resolvedOptions.undoRedo && (canUndo || canRedo) && (
                <div className="flex items-center">
                    <Button variant="ghost" size="icon" className="h-8 w-8" disabled={!canUndo} onClick={onUndo} title={t.undo} aria-label={t.undo}>
                        <Undo2 className="h-3.5 w-3.5" />
                    </Button>
                    <Button variant="ghost" size="icon" className="h-8 w-8" disabled={!canRedo} onClick={onRedo} title={t.redo} aria-label={t.redo}>
                        <Redo2 className="h-3.5 w-3.5" />
                    </Button>
                </div>
            )}
            {resolvedOptions.printable && (
                <Button variant="outline" size="sm" className="h-8 gap-1.5" onClick={() => window.print()}>
                    <Printer className="h-3.5 w-3.5" /><span className="hidden sm:inline">{t.print}</span>
                </Button>
            )}
            {resolvedOptions.density && (
                <DensityToggle density={density} onChange={onDensityChange} t={t} />
            )}
            {(resolvedOptions.columnVisibility || resolvedOptions.columnOrdering) && (
                <ColumnsDropdown table={table} tableColumns={tableData.columns} columnOrder={columnOrder}
                    onReorder={onReorderColumns} showVisibility={resolvedOptions.columnVisibility}
                    showOrdering={resolvedOptions.columnOrdering} t={t} />
            )}
            {resolvedOptions.shortcutsOverlay && onShowShortcuts && (
                <Button variant="ghost" size="icon" className="h-8 w-8" onClick={onShowShortcuts} title={t.keyboardShortcuts} aria-label={t.keyboardShortcuts}>
                    <HelpCircle className="h-3.5 w-3.5" />
                </Button>
            )}
        </div>
    );
}

function ColumnsDropdown<TData>({ table, tableColumns, columnOrder, onReorder, showVisibility, showOrdering, t }: {
    table: TanStackTable<TData>; tableColumns: DataTableColumnDef[];
    columnOrder: ColumnOrderState; onReorder: (order: ColumnOrderState) => void;
    showVisibility: boolean; showOrdering: boolean; t: DataTableTranslations;
}) {
    const dragItem = useRef<string | null>(null);
    const dragOverRef = useRef<string | null>(null);
    const [dragging, setDragging] = useState<string | null>(null);
    const [dragOverId, setDragOverId] = useState<string | null>(null);
    const [reordering, setReordering] = useState(false);
    const isReorderActive = reordering && showOrdering;

    const handleDragStart = useCallback((columnId: string) => { dragItem.current = columnId; setDragging(columnId); }, []);
    const handleDragEnd = useCallback(() => {
        const from = dragItem.current; const to = dragOverRef.current;
        if (from && to && from !== to) {
            const newOrder = [...columnOrder]; const fromIdx = newOrder.indexOf(from);
            newOrder.splice(fromIdx, 1); const toIdx = newOrder.indexOf(to);
            if (toIdx !== -1) { newOrder.splice(toIdx, 0, from); onReorder(newOrder); }
        }
        dragItem.current = null; dragOverRef.current = null; setDragging(null); setDragOverId(null);
    }, [columnOrder, onReorder]);

    const hideable = table.getAllLeafColumns().filter((c) => c.getCanHide());
    const colDefMap = new Map(tableColumns.map((c) => [c.id, c]));
    const orderedHideable = useMemo(() => {
        if (!showOrdering) return hideable;
        return [...hideable].sort((a, b) => {
            const ai = columnOrder.indexOf(a.id); const bi = columnOrder.indexOf(b.id);
            return (ai === -1 ? 999 : ai) - (bi === -1 ? 999 : bi);
        });
    }, [hideable, columnOrder, showOrdering]);

    const ungrouped = orderedHideable.filter((c) => !colDefMap.get(c.id)?.group);
    const groups = new Map<string, typeof hideable>();
    for (const col of orderedHideable) {
        const g = colDefMap.get(col.id)?.group;
        if (g) { if (!groups.has(g)) groups.set(g, []); groups.get(g)!.push(col); }
    }

    function renderItem(column: ReturnType<TanStackTable<TData>["getAllLeafColumns"]>[number]) {
        const isOver = dragOverId === column.id && dragging !== column.id;
        return (
            <div key={column.id} className={cn("flex items-center gap-1 rounded-sm px-2 py-1.5 text-sm",
                dragging === column.id && "opacity-40", isOver && "border-t-2 border-t-primary")}
                draggable={isReorderActive}
                onDragStart={() => handleDragStart(column.id)}
                onDragOver={(e) => { e.preventDefault(); dragOverRef.current = column.id; setDragOverId(column.id); }}
                onDragEnd={handleDragEnd}>
                {isReorderActive && <GripVertical className="h-3.5 w-3.5 shrink-0 cursor-grab text-muted-foreground/50" aria-label="Drag to reorder" role="img" />}
                {showVisibility ? (
                    <label className="flex flex-1 cursor-pointer items-center gap-2">
                        <Checkbox checked={column.getIsVisible()} onCheckedChange={(value) => column.toggleVisibility(!!value)} />
                        <span className="select-none">{column.columnDef.header as string}</span>
                    </label>
                ) : <span className="flex-1 select-none">{column.columnDef.header as string}</span>}
            </div>
        );
    }

    return (
        <DropdownMenu onOpenChange={(open) => { if (!open) { setReordering(false); setDragging(null); setDragOverId(null); } }}>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="h-8 gap-1.5"><SlidersHorizontal className="h-3.5 w-3.5" /><span className="hidden sm:inline">{t.columns}</span></Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="max-h-[400px] w-60 overflow-y-auto">
                <div className="flex items-center justify-between px-2 py-1.5">
                    <span className="text-sm font-semibold">{t.columns}</span>
                    {showOrdering && (
                        <Button variant="ghost" size="sm" className="h-6 px-2 text-xs"
                            onPointerDown={(e) => e.stopPropagation()}
                            onClick={(e) => { e.preventDefault(); setReordering((r) => !r); }}>
                            {reordering ? t.done : t.reorder}
                        </Button>
                    )}
                </div>
                <DropdownMenuSeparator />
                {ungrouped.map((column) => renderItem(column))}
                {[...groups.entries()].map(([group, cols]) => (
                    <DropdownMenuSub key={group}>
                        <DropdownMenuSubTrigger className="flex-row-reverse gap-2 justify-end [&_svg]:ml-0 [&_svg]:rotate-180">{group}</DropdownMenuSubTrigger>
                        <DropdownMenuSubContent>{cols.map((column) => renderItem(column))}</DropdownMenuSubContent>
                    </DropdownMenuSub>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

const TYPE_ICON_MAP: Record<string, FilterColumn["icon"]> = {
    text: Type, number: Hash, date: Calendar, option: CircleDot, multiOption: List,
    boolean: ToggleLeft, image: ImageIcon, badge: Tag, currency: DollarSign,
    percentage: Percent, link: LinkIcon, email: Mail, phone: Phone,
};

function buildFilterColumns(columns: DataTableColumnDef[]): FilterColumn[] {
    return columns.filter((col) => col.filterable).map((col) => {
        const type = col.type === "multiOption" ? "option" as const
            : (col.type === "currency" || col.type === "percentage") ? "number" as const
            : (col.type === "link" || col.type === "email" || col.type === "phone") ? "text" as const
            : col.type as FilterColumn["type"];
        return {
            id: col.id, label: col.label, type, icon: TYPE_ICON_MAP[col.type],
            ...(col.options ? { options: col.options } : {}),
            ...(col.searchThreshold != null ? { searchThreshold: col.searchThreshold } : {}),
        };
    });
}

function SkeletonRows({ count, colCount }: { count: number; colCount: number }) {
    return (<>{Array.from({ length: count }).map((_, i) => (
        <TableRow key={`skeleton-${i}`}>{Array.from({ length: colCount }).map((_, j) => (
            <TableCell key={`skeleton-${i}-${j}`} className="py-2.5"><Skeleton className="h-4 w-full rounded" /></TableCell>
        ))}</TableRow>
    ))}</>);
}

/** Hook for responsive column collapse based on viewport width */
function useResponsiveColumns(columns: DataTableColumnDef[]): Set<string> {
    const [hiddenCols, setHiddenCols] = useState<Set<string>>(new Set());
    useEffect(() => {
        const priorityCols = columns.filter((c) => c.responsivePriority != null);
        if (priorityCols.length === 0) return;
        function update() {
            const width = window.innerWidth;
            const hidden = new Set<string>();
            for (const col of priorityCols) {
                const threshold = 640 + ((col.responsivePriority ?? 0) - 1) * 128;
                if (width < threshold) hidden.add(col.id);
            }
            setHiddenCols(hidden);
        }
        update();
        window.addEventListener("resize", update);
        return () => window.removeEventListener("resize", update);
    }, [columns]);
    return hiddenCols;
}

// ─── Mobile card layout ──────────────────────────────────────────────────────

function useMobileBreakpoint(breakpoint: number): boolean {
    const [isMobile, setIsMobile] = useState(false);
    useEffect(() => {
        if (breakpoint <= 0) return;
        function check() { setIsMobile(window.innerWidth < breakpoint); }
        check();
        window.addEventListener("resize", check);
        return () => window.removeEventListener("resize", check);
    }, [breakpoint]);
    return isMobile;
}

function MobileCardLayout<TData>({ rows, columns, renderCell, actions, onRowClick, rowLink, t, density }: {
    rows: TData[]; columns: DataTableColumnDef[];
    renderCell?: (columnId: string, value: unknown, row: TData) => React.ReactNode | undefined;
    actions?: import("./types").DataTableAction<TData>[];
    onRowClick?: (row: TData) => void;
    rowLink?: (row: TData) => string;
    t: DataTableTranslations;
    density: DataTableDensity;
}) {
    const visibleCols = columns.filter((c) => c.visible !== false);
    return (
        <div className="grid gap-3 md:hidden">
            {rows.length === 0 ? (
                <div className="text-center py-8 text-muted-foreground">{t.noData}</div>
            ) : rows.map((row, idx) => {
                const rowData = row as Record<string, unknown>;
                const handleClick = () => {
                    if (rowLink) window.location.href = rowLink(row);
                    else if (onRowClick) onRowClick(row);
                };
                const isClickable = !!onRowClick || !!rowLink;
                return (
                    <div key={rowData.id != null ? String(rowData.id) : idx}
                        className={cn("rounded-lg border bg-card p-4 space-y-2",
                            isClickable && "cursor-pointer hover:bg-accent/50 hover:shadow-md transition-all",
                            density === "compact" && "p-2.5 space-y-1",
                            density === "spacious" && "p-5 space-y-3")}
                        onClick={isClickable ? handleClick : undefined}>
                        {visibleCols.map((col) => {
                            const value = rowData[col.id];
                            const custom = renderCell?.(col.id, value, row);
                            return (
                                <div key={col.id} className="flex items-start justify-between gap-2">
                                    <span className="text-xs font-medium text-muted-foreground shrink-0">{col.label}</span>
                                    <span className="text-sm text-right">
                                        {custom !== undefined ? custom : value === null || value === undefined ? "—" : String(value)}
                                    </span>
                                </div>
                            );
                        })}
                        {actions && actions.length > 0 && (
                            <div className="flex justify-end gap-1 pt-1 border-t">
                                <DataTableRowActions row={row} actions={actions} t={t} />
                            </div>
                        )}
                    </div>
                );
            })}
        </div>
    );
}

// ─── Layout Switcher ─────────────────────────────────────────────────────

function LayoutSwitcher({ layout, onLayoutChange, showKanban, t }: {
    layout: DataTableLayoutMode; onLayoutChange: (mode: DataTableLayoutMode) => void;
    showKanban: boolean; t: DataTableTranslations;
}) {
    const modes: { mode: DataTableLayoutMode; icon: React.ReactNode; label: string }[] = [
        { mode: "table", icon: <LayoutList className="h-3.5 w-3.5" />, label: t.layoutTable },
        { mode: "grid", icon: <LayoutGrid className="h-3.5 w-3.5" />, label: t.layoutGrid },
        { mode: "cards", icon: <Rows3 className="h-3.5 w-3.5" />, label: t.layoutCards },
        ...(showKanban ? [{ mode: "kanban" as const, icon: <Kanban className="h-3.5 w-3.5" />, label: t.layoutKanban }] : []),
    ];
    return (
        <div className="inline-flex items-center rounded-md border bg-muted/30 p-0.5">
            {modes.map(({ mode, icon, label }) => (
                <button key={mode} type="button" title={label}
                    className={cn("inline-flex items-center rounded-md px-1.5 py-1 text-xs font-medium transition-all",
                        layout === mode ? "bg-background text-foreground shadow-sm" : "text-muted-foreground hover:text-foreground")}
                    onClick={() => onLayoutChange(mode)}>
                    {icon}
                </button>
            ))}
        </div>
    );
}

// ─── Grid Layout ─────────────────────────────────────────────────────────

function GridLayout<TData>({ rows, columns, renderCell, actions, onRowClick, rowLink, t, density,
    imageColumn, titleColumn, subtitleColumn }: {
    rows: TData[]; columns: DataTableColumnDef[];
    renderCell?: (columnId: string, value: unknown, row: TData) => React.ReactNode | undefined;
    actions?: import("./types").DataTableAction<TData>[];
    onRowClick?: (row: TData) => void; rowLink?: (row: TData) => string;
    t: DataTableTranslations; density: DataTableDensity;
    imageColumn?: string; titleColumn?: string; subtitleColumn?: string;
}) {
    return (
        <div className={cn("grid gap-4",
            "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4")}>
            {rows.length === 0 ? (
                <div className="col-span-full text-center py-12 text-muted-foreground">{t.noData}</div>
            ) : rows.map((row, idx) => {
                const rowData = row as Record<string, unknown>;
                const handleClick = () => {
                    if (rowLink) window.location.href = rowLink(row);
                    else if (onRowClick) onRowClick(row);
                };
                const isClickable = !!onRowClick || !!rowLink;
                const imgSrc = imageColumn ? rowData[imageColumn] as string | undefined : undefined;
                const title = titleColumn ? String(rowData[titleColumn] ?? "") : null;
                const subtitle = subtitleColumn ? String(rowData[subtitleColumn] ?? "") : null;
                const initials = title ? title.split(" ").map(w => w[0]).join("").slice(0, 2).toUpperCase() : "?";
                const visibleCols = columns.filter(c => c.visible !== false && c.id !== imageColumn && c.id !== titleColumn && c.id !== subtitleColumn);

                return (
                    <div key={rowData.id != null ? String(rowData.id) : idx}
                        className={cn("group rounded-lg border bg-card overflow-hidden transition-colors",
                            isClickable && "cursor-pointer hover:bg-accent/5 hover:border-primary/30",
                            density === "compact" && "text-xs")}
                        onClick={isClickable ? handleClick : undefined}>
                        <div className={cn("p-4 space-y-3", density === "compact" && "p-2.5 space-y-1.5", density === "spacious" && "p-5 space-y-3")}>
                            {/* Header with avatar/image + name */}
                            <div className="flex items-center gap-3">
                                {imgSrc ? (
                                    <img src={imgSrc} alt={title ?? ""} className="h-10 w-10 rounded-full object-cover shrink-0 ring-1 ring-border" />
                                ) : title ? (
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                        {initials}
                                    </div>
                                ) : null}
                                <div className="min-w-0 flex-1">
                                    {title && <h3 className="font-semibold text-sm truncate">{title}</h3>}
                                    {subtitle && <p className="text-xs text-muted-foreground truncate">{subtitle}</p>}
                                </div>
                            </div>
                            {/* Fields */}
                            <div className="space-y-1.5 pt-2 border-t border-border/40">
                                {visibleCols.slice(0, 4).map(col => {
                                    const value = rowData[col.id];
                                    const custom = renderCell?.(col.id, value, row);
                                    return (
                                        <div key={col.id} className="flex items-center justify-between gap-2">
                                            <span className="text-[11px] font-medium text-muted-foreground uppercase tracking-wider shrink-0">{col.label}</span>
                                            <span className="text-xs text-right truncate">
                                                {custom !== undefined ? custom
                                                    : col.type === "badge" && value != null ? (
                                                        <span className={cn("inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 ring-inset",
                                                            String(value).toLowerCase() === "active" ? "bg-emerald-500/10 text-emerald-500 ring-emerald-500/20"
                                                            : String(value).toLowerCase() === "pending" ? "bg-amber-500/10 text-amber-500 ring-amber-500/20"
                                                            : String(value).toLowerCase() === "deleted" ? "bg-red-500/10 text-red-500 ring-red-500/20"
                                                            : "bg-muted text-muted-foreground ring-border"
                                                        )}>{String(value)}</span>
                                                    )
                                                    : col.type === "boolean" ? (
                                                        <Checkbox checked={!!value} disabled className="h-4 w-4 data-[state=checked]:bg-emerald-500 data-[state=checked]:border-emerald-500" />
                                                    )
                                                    : value == null ? "—" : String(value)}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                            {actions && actions.length > 0 && (
                                <div className="flex justify-end gap-1 pt-2 border-t border-border/40">
                                    <DataTableRowActions row={row} actions={actions} t={t} />
                                </div>
                            )}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

// ─── Enhanced Card Layout ────────────────────────────────────────────────

function CardLayout<TData>({ rows, columns, renderCell, actions, onRowClick, rowLink, t, density,
    titleColumn, subtitleColumn, imageColumn }: {
    rows: TData[]; columns: DataTableColumnDef[];
    renderCell?: (columnId: string, value: unknown, row: TData) => React.ReactNode | undefined;
    actions?: import("./types").DataTableAction<TData>[];
    onRowClick?: (row: TData) => void; rowLink?: (row: TData) => string;
    t: DataTableTranslations; density: DataTableDensity;
    titleColumn?: string; subtitleColumn?: string; imageColumn?: string;
}) {
    const visibleCols = columns.filter(c => c.visible !== false && c.id !== titleColumn && c.id !== subtitleColumn && c.id !== imageColumn);
    return (
        <div className="space-y-2">
            {rows.length === 0 ? (
                <div className="text-center py-12 text-muted-foreground">{t.noData}</div>
            ) : rows.map((row, idx) => {
                const rowData = row as Record<string, unknown>;
                const handleClick = () => {
                    if (rowLink) window.location.href = rowLink(row);
                    else if (onRowClick) onRowClick(row);
                };
                const isClickable = !!onRowClick || !!rowLink;
                const title = titleColumn ? String(rowData[titleColumn] ?? "") : null;
                const subtitle = subtitleColumn ? String(rowData[subtitleColumn] ?? "") : null;
                const imgSrc = imageColumn ? rowData[imageColumn] as string | undefined : undefined;
                const initials = title ? title.split(" ").map(w => w[0]).join("").slice(0, 2).toUpperCase() : "?";

                return (
                    <div key={rowData.id != null ? String(rowData.id) : idx}
                        className={cn("rounded-lg border bg-card transition-colors",
                            isClickable && "cursor-pointer hover:bg-accent/5 hover:border-primary/30",
                            density === "compact" && "text-xs")}
                        onClick={isClickable ? handleClick : undefined}>
                        <div className={cn("p-4", density === "compact" && "p-3", density === "spacious" && "p-6")}>
                            {/* Header row with avatar + title + actions */}
                            <div className="flex items-center gap-3 mb-3 pb-3 border-b border-border/40">
                                {imgSrc ? (
                                    <img src={imgSrc} alt={title ?? ""} className="h-10 w-10 rounded-full object-cover shrink-0 ring-1 ring-border" />
                                ) : title ? (
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                        {initials}
                                    </div>
                                ) : null}
                                <div className="min-w-0 flex-1">
                                    {title && <h3 className="font-semibold text-sm">{title}</h3>}
                                    {subtitle && <p className="text-xs text-muted-foreground mt-0.5">{subtitle}</p>}
                                </div>
                                {actions && actions.length > 0 && (
                                    <div className="shrink-0">
                                        <DataTableRowActions row={row} actions={actions} t={t} />
                                    </div>
                                )}
                            </div>
                            {/* Fields in a responsive grid */}
                            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-2.5">
                                {visibleCols.map(col => {
                                    const value = rowData[col.id];
                                    const custom = renderCell?.(col.id, value, row);
                                    return (
                                        <div key={col.id} className="space-y-0.5">
                                            <span className="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">{col.label}</span>
                                            <div className="text-sm">
                                                {custom !== undefined ? custom
                                                    : col.type === "badge" && value != null ? (
                                                        <span className={cn("inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 ring-inset",
                                                            String(value).toLowerCase() === "active" ? "bg-emerald-500/10 text-emerald-500 ring-emerald-500/20"
                                                            : String(value).toLowerCase() === "pending" ? "bg-amber-500/10 text-amber-500 ring-amber-500/20"
                                                            : String(value).toLowerCase() === "deleted" ? "bg-red-500/10 text-red-500 ring-red-500/20"
                                                            : "bg-muted text-muted-foreground ring-border"
                                                        )}>{String(value)}</span>
                                                    )
                                                    : col.type === "boolean" ? (
                                                        <Checkbox checked={!!value} disabled className="h-4 w-4 data-[state=checked]:bg-emerald-500 data-[state=checked]:border-emerald-500" />
                                                    )
                                                    : value == null ? "—" : String(value)}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

// ─── Column Statistics Popover ───────────────────────────────────────────

function computeColumnStats(data: Record<string, unknown>[], columnId: string, columnType: string): DataTableColumnStats {
    const values = data.map(row => row[columnId]);
    const count = values.length;
    const nullCount = values.filter(v => v === null || v === undefined || v === "").length;
    const nonNull = values.filter(v => v !== null && v !== undefined && v !== "");
    const uniqueCount = new Set(nonNull.map(v => String(v))).size;

    const stats: DataTableColumnStats = { count, nullCount, uniqueCount };

    // Numeric stats
    if (columnType === "number" || columnType === "currency" || columnType === "percentage") {
        const nums = nonNull.map(v => typeof v === "number" ? v : parseFloat(String(v))).filter(n => !isNaN(n));
        if (nums.length > 0) {
            const sorted = [...nums].sort((a, b) => a - b);
            stats.min = sorted[0];
            stats.max = sorted[sorted.length - 1];
            stats.sum = nums.reduce((a, b) => a + b, 0);
            stats.avg = stats.sum / nums.length;
            const mid = Math.floor(sorted.length / 2);
            stats.median = sorted.length % 2 === 0 ? (sorted[mid - 1] + sorted[mid]) / 2 : sorted[mid];

            // Distribution: 8 buckets
            const range = stats.max - stats.min;
            if (range > 0) {
                const bucketCount = Math.min(8, uniqueCount);
                const bucketSize = range / bucketCount;
                const buckets = Array.from({ length: bucketCount }, (_, i) => ({
                    bucket: `${(stats.min! + i * bucketSize).toFixed(1)}`,
                    count: 0,
                }));
                for (const n of nums) {
                    const idx = Math.min(Math.floor((n - stats.min!) / bucketSize), bucketCount - 1);
                    buckets[idx].count++;
                }
                stats.distribution = buckets;
            }
        }
    } else {
        // Categorical distribution: top 8 values by frequency
        const freq = new Map<string, number>();
        for (const v of nonNull) {
            const key = String(v);
            freq.set(key, (freq.get(key) ?? 0) + 1);
        }
        stats.distribution = [...freq.entries()]
            .sort((a, b) => b[1] - a[1])
            .slice(0, 8)
            .map(([bucket, count]) => ({ bucket, count }));
    }

    return stats;
}

function ColumnStatsPopover({ columnId, columnLabel, columnType, data, t }: {
    columnId: string; columnLabel: string; columnType: string;
    data: Record<string, unknown>[]; t: DataTableTranslations;
}) {
    const [open, setOpen] = useState(false);
    const stats = useMemo(() => open ? computeColumnStats(data, columnId, columnType) : null, [open, data, columnId, columnType]);
    const isNumeric = columnType === "number" || columnType === "currency" || columnType === "percentage";
    const maxDistCount = stats?.distribution ? Math.max(...stats.distribution.map(d => d.count)) : 0;

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <button type="button" className="p-0.5 rounded hover:bg-muted transition-colors" title={t.columnStats}>
                    <BarChart3 className="h-3 w-3 text-muted-foreground/60" />
                </button>
            </PopoverTrigger>
            <PopoverContent align="start" className="w-72 p-0">
                <div className="p-3 border-b">
                    <h4 className="font-semibold text-sm">{columnLabel}</h4>
                    <p className="text-xs text-muted-foreground">{t.columnStats}</p>
                </div>
                {stats && (
                    <div className="p-3 space-y-3">
                        {/* Basic stats */}
                        <div className="grid grid-cols-3 gap-2">
                            <div className="text-center p-1.5 rounded bg-muted/50">
                                <div className="text-xs text-muted-foreground">{t.statsCount}</div>
                                <div className="text-sm font-semibold tabular-nums">{stats.count}</div>
                            </div>
                            <div className="text-center p-1.5 rounded bg-muted/50">
                                <div className="text-xs text-muted-foreground">{t.statsNulls}</div>
                                <div className="text-sm font-semibold tabular-nums">{stats.nullCount}</div>
                            </div>
                            <div className="text-center p-1.5 rounded bg-muted/50">
                                <div className="text-xs text-muted-foreground">{t.statsUnique}</div>
                                <div className="text-sm font-semibold tabular-nums">{stats.uniqueCount}</div>
                            </div>
                        </div>

                        {/* Numeric stats */}
                        {isNumeric && stats.min !== undefined && (
                            <div className="grid grid-cols-2 gap-2">
                                {[
                                    { label: t.summaryMin, value: stats.min },
                                    { label: t.summaryMax, value: stats.max },
                                    { label: t.summaryAvg, value: stats.avg },
                                    { label: t.statsMedian, value: stats.median },
                                    { label: t.summarySum, value: stats.sum },
                                ].filter(s => s.value !== undefined).map(({ label, value }) => (
                                    <div key={label} className="flex justify-between text-xs py-0.5">
                                        <span className="text-muted-foreground">{label}</span>
                                        <span className="font-medium tabular-nums">{Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })}</span>
                                    </div>
                                ))}
                            </div>
                        )}

                        {/* Distribution chart */}
                        {stats.distribution && stats.distribution.length > 0 && (
                            <div className="space-y-1">
                                <h5 className="text-xs font-medium text-muted-foreground">{t.statsDistribution}</h5>
                                <div className="space-y-0.5">
                                    {stats.distribution.map((d, i) => (
                                        <div key={i} className="flex items-center gap-2 text-xs">
                                            <span className="w-16 truncate text-muted-foreground text-right shrink-0" title={d.bucket}>{d.bucket}</span>
                                            <div className="flex-1 h-4 bg-muted/50 rounded overflow-hidden">
                                                <div className="h-full bg-primary/40 rounded transition-all"
                                                    style={{ width: `${maxDistCount > 0 ? (d.count / maxDistCount) * 100 : 0}%` }} />
                                            </div>
                                            <span className="w-8 text-right tabular-nums text-muted-foreground">{d.count}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </PopoverContent>
        </Popover>
    );
}

// ─── Conditional Formatting Rules Builder ────────────────────────────────

const COND_FORMAT_OPERATORS = [
    { value: "gt", label: ">" },
    { value: "gte", label: ">=" },
    { value: "lt", label: "<" },
    { value: "lte", label: "<=" },
    { value: "eq", label: "=" },
    { value: "neq", label: "!=" },
    { value: "contains", label: "contains" },
    { value: "empty", label: "is empty" },
    { value: "notEmpty", label: "is not empty" },
];

const COND_FORMAT_COLORS = [
    { bg: "bg-red-100 dark:bg-red-900/30", label: "Red", value: "#fee2e2" },
    { bg: "bg-orange-100 dark:bg-orange-900/30", label: "Orange", value: "#ffedd5" },
    { bg: "bg-yellow-100 dark:bg-yellow-900/30", label: "Yellow", value: "#fef9c3" },
    { bg: "bg-green-100 dark:bg-green-900/30", label: "Green", value: "#dcfce7" },
    { bg: "bg-blue-100 dark:bg-blue-900/30", label: "Blue", value: "#dbeafe" },
    { bg: "bg-purple-100 dark:bg-purple-900/30", label: "Purple", value: "#f3e8ff" },
];

function useConditionalFormatRules(tableName: string, enabled: boolean) {
    const key = `dt-cond-format-${tableName}`;
    const [rules, setRules] = useState<DataTableConditionalFormatRule[]>(() => {
        if (!enabled) return [];
        const stored = safeGetItem(key);
        if (stored) { try { return JSON.parse(stored); } catch { return []; } }
        return [];
    });

    const saveRules = useCallback((newRules: DataTableConditionalFormatRule[]) => {
        setRules(newRules);
        safeSetItem(key, JSON.stringify(newRules));
    }, [key]);

    const addRule = useCallback(() => {
        saveRules([...rules, {
            id: `cf-${Date.now()}`,
            column: "", operator: "gt", value: "",
            style: { backgroundColor: "#dcfce7" },
        }]);
    }, [rules, saveRules]);

    const updateRule = useCallback((id: string, patch: Partial<DataTableConditionalFormatRule>) => {
        saveRules(rules.map(r => r.id === id ? { ...r, ...patch } : r));
    }, [rules, saveRules]);

    const removeRule = useCallback((id: string) => {
        saveRules(rules.filter(r => r.id !== id));
    }, [rules, saveRules]);

    return { rules, addRule, updateRule, removeRule };
}

function evaluateConditionalFormat(rule: DataTableConditionalFormatRule, cellValue: unknown): boolean {
    const val = cellValue;
    const ruleVal = rule.value;
    switch (rule.operator) {
        case "gt": return Number(val) > Number(ruleVal);
        case "gte": return Number(val) >= Number(ruleVal);
        case "lt": return Number(val) < Number(ruleVal);
        case "lte": return Number(val) <= Number(ruleVal);
        case "eq": return String(val) === String(ruleVal);
        case "neq": return String(val) !== String(ruleVal);
        case "contains": return String(val).toLowerCase().includes(String(ruleVal).toLowerCase());
        case "empty": return val === null || val === undefined || val === "";
        case "notEmpty": return val !== null && val !== undefined && val !== "";
        case "between": return Number(val) >= Number(ruleVal) && Number(val) <= Number(rule.value2);
        default: return false;
    }
}

function ConditionalFormatDialog({ open, onOpenChange, columns, rules, onAddRule, onUpdateRule, onRemoveRule, t }: {
    open: boolean; onOpenChange: (open: boolean) => void;
    columns: DataTableColumnDef[];
    rules: DataTableConditionalFormatRule[];
    onAddRule: () => void;
    onUpdateRule: (id: string, patch: Partial<DataTableConditionalFormatRule>) => void;
    onRemoveRule: (id: string) => void;
    t: DataTableTranslations;
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Paintbrush className="h-5 w-5" />{t.conditionalFormatting}
                    </DialogTitle>
                </DialogHeader>
                <div className="space-y-3 py-2">
                    {rules.length === 0 && (
                        <p className="text-sm text-muted-foreground text-center py-4">{t.noRules}</p>
                    )}
                    {rules.map((rule) => (
                        <div key={rule.id} className="flex flex-wrap items-end gap-2 rounded-lg border p-3 bg-muted/20">
                            <div className="grid gap-1 flex-1 min-w-[120px]">
                                <Label className="text-xs">{t.formatColumn}</Label>
                                <Select value={rule.column} onValueChange={(v) => onUpdateRule(rule.id, { column: v })}>
                                    <SelectTrigger className="h-8 text-xs"><SelectValue placeholder="Column..." /></SelectTrigger>
                                    <SelectContent>
                                        {columns.map(col => (
                                            <SelectItem key={col.id} value={col.id}>{col.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="grid gap-1 min-w-[100px]">
                                <Label className="text-xs">{t.formatOperator}</Label>
                                <Select value={rule.operator} onValueChange={(v) => onUpdateRule(rule.id, { operator: v as DataTableConditionalFormatRule["operator"] })}>
                                    <SelectTrigger className="h-8 text-xs"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        {COND_FORMAT_OPERATORS.map(op => (
                                            <SelectItem key={op.value} value={op.value}>{op.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            {rule.operator !== "empty" && rule.operator !== "notEmpty" && (
                                <div className="grid gap-1 min-w-[80px]">
                                    <Label className="text-xs">{t.formatValue}</Label>
                                    <Input className="h-8 text-xs w-24" value={String(rule.value ?? "")}
                                        onChange={e => onUpdateRule(rule.id, { value: e.target.value })} />
                                </div>
                            )}
                            <div className="grid gap-1">
                                <Label className="text-xs">{t.formatBackground}</Label>
                                <div className="flex gap-1">
                                    {COND_FORMAT_COLORS.map(color => (
                                        <button key={color.value} type="button"
                                            className={cn("h-6 w-6 rounded border transition-all", color.bg,
                                                rule.style.backgroundColor === color.value && "ring-2 ring-primary ring-offset-1")}
                                            onClick={() => onUpdateRule(rule.id, { style: { ...rule.style, backgroundColor: color.value } })} />
                                    ))}
                                </div>
                            </div>
                            <div className="flex items-center gap-1">
                                <label className="flex items-center gap-1 text-xs">
                                    <Checkbox checked={rule.style.fontWeight === "bold"}
                                        onCheckedChange={checked => onUpdateRule(rule.id, { style: { ...rule.style, fontWeight: checked ? "bold" : "normal" } })} />
                                    <span className="font-bold">B</span>
                                </label>
                            </div>
                            <Button variant="ghost" size="icon" className="h-8 w-8 text-destructive hover:text-destructive"
                                onClick={() => onRemoveRule(rule.id)}>
                                <Trash2 className="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    ))}
                </div>
                <DialogFoot>
                    <Button variant="outline" size="sm" className="gap-1.5" onClick={onAddRule}>
                        <Plus className="h-3.5 w-3.5" />{t.addRule}
                    </Button>
                    <Button onClick={() => onOpenChange(false)}>{t.done}</Button>
                </DialogFoot>
            </DialogContent>
        </Dialog>
    );
}

// ─── Faceted Filter ──────────────────────────────────────────────────────

function FacetedFilterSection({ columns, facetedCounts, serverFilters, prefix, partialReloadKey, t }: {
    columns: DataTableColumnDef[];
    facetedCounts: Record<string, Record<string, number>>;
    serverFilters: Record<string, unknown>;
    prefix?: string; partialReloadKey?: string;
    t: DataTableTranslations;
}) {
    const facetedColumns = columns.filter(c => c.filterable && facetedCounts[c.id]);
    if (facetedColumns.length === 0) return null;

    const handleToggle = useCallback((columnId: string, value: string) => {
        const p = prefix ? `${prefix}_` : "";
        const filterKey = `${p}filter[${columnId}]`;
        const url = new URL(window.location.href);
        const current = url.searchParams.get(filterKey) ?? "";
        const currentValues = current.startsWith("in:") ? current.slice(3).split(",").filter(Boolean) : current ? [current] : [];

        let newValues: string[];
        if (currentValues.includes(value)) {
            newValues = currentValues.filter(v => v !== value);
        } else {
            newValues = [...currentValues, value];
        }

        if (newValues.length === 0) {
            url.searchParams.delete(filterKey);
        } else {
            url.searchParams.set(filterKey, newValues.length === 1 ? `is:${newValues[0]}` : `in:${newValues.join(",")}`);
        }
        url.searchParams.set(`${p}page`, "1");
        router.visit(url.toString(), { preserveState: true, preserveScroll: true, only: partialReloadKey ? [partialReloadKey] : undefined });
    }, [prefix, partialReloadKey]);

    return (
        <div className="flex flex-wrap gap-4 print:hidden">
            {facetedColumns.map(col => {
                const counts = facetedCounts[col.id];
                const options = col.options ?? Object.keys(counts).map(k => ({ label: k, value: k }));
                const p = prefix ? `${prefix}_` : "";
                const filterKey = `${p}filter[${col.id}]`;
                const currentFilter = typeof window !== "undefined" ? new URL(window.location.href).searchParams.get(filterKey) ?? "" : "";
                const selectedValues = new Set(currentFilter.startsWith("in:") ? currentFilter.slice(3).split(",") : currentFilter.startsWith("is:") ? [currentFilter.slice(3)] : []);

                return (
                    <div key={col.id} className="space-y-1.5">
                        <h4 className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{col.label}</h4>
                        <div className="flex flex-wrap gap-1">
                            {options.map(opt => {
                                const count = counts[opt.value] ?? 0;
                                const isActive = selectedValues.has(opt.value);
                                return (
                                    <button key={opt.value} type="button"
                                        className={cn("inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition-all border",
                                            isActive
                                                ? "bg-primary text-primary-foreground border-primary"
                                                : "bg-background text-foreground border-border hover:border-primary/50 hover:bg-accent/50")}
                                        onClick={() => handleToggle(col.id, opt.value)}>
                                        <span>{opt.label}</span>
                                        <span className={cn("tabular-nums rounded-full px-1.5 py-0.5 text-[10px] min-w-[1.25rem] text-center",
                                            isActive ? "bg-primary-foreground/20" : "bg-muted")}>
                                            {count}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

// ─── Collaborative Presence Indicators ───────────────────────────────────

const PRESENCE_COLORS = ["#ef4444", "#f97316", "#eab308", "#22c55e", "#3b82f6", "#8b5cf6", "#ec4899", "#06b6d4"];

function usePresence(channel: string | undefined, currentUser: DataTablePresenceUser | undefined): DataTablePresenceUser[] {
    const [users, setUsers] = useState<DataTablePresenceUser[]>([]);

    useEffect(() => {
        if (!channel || !currentUser) return;
        const Echo = (window as unknown as { Echo?: {
            join: (name: string) => {
                here: (cb: (users: DataTablePresenceUser[]) => void) => unknown;
                joining: (cb: (user: DataTablePresenceUser) => void) => unknown;
                leaving: (cb: (user: DataTablePresenceUser) => void) => unknown;
                leave: () => void;
            }
        } }).Echo;
        if (!Echo) return;

        const presenceChannel = Echo.join(channel);
        presenceChannel.here((members: DataTablePresenceUser[]) => {
            setUsers(members.filter(m => m.id !== currentUser.id).map((m, i) => ({
                ...m, color: m.color ?? PRESENCE_COLORS[i % PRESENCE_COLORS.length]
            })));
        });
        presenceChannel.joining((user: DataTablePresenceUser) => {
            setUsers(prev => {
                if (prev.find(u => u.id === user.id)) return prev;
                return [...prev, { ...user, color: user.color ?? PRESENCE_COLORS[prev.length % PRESENCE_COLORS.length] }];
            });
        });
        presenceChannel.leaving((user: DataTablePresenceUser) => {
            setUsers(prev => prev.filter(u => u.id !== user.id));
        });

        return () => { presenceChannel.leave(); };
    }, [channel, currentUser]);

    return users;
}

function PresenceIndicator({ users, t }: { users: DataTablePresenceUser[]; t: DataTableTranslations }) {
    if (users.length === 0) return null;
    const maxShow = 5;
    const shown = users.slice(0, maxShow);
    const overflow = users.length - maxShow;

    return (
        <div className="flex items-center gap-1.5 print:hidden" title={t.presenceUsers(users.length)}>
            <Users className="h-3.5 w-3.5 text-muted-foreground" />
            <div className="flex -space-x-2">
                {shown.map(user => (
                    <div key={user.id} className="relative" title={`${user.name} — ${t.presenceViewing}`}>
                        {user.avatar ? (
                            <img src={user.avatar} alt={user.name}
                                className="h-6 w-6 rounded-full ring-2 ring-background object-cover"
                                style={{ borderColor: user.color }} />
                        ) : (
                            <div className="flex h-6 w-6 items-center justify-center rounded-full ring-2 ring-background text-[10px] font-bold text-white"
                                style={{ backgroundColor: user.color }}>
                                {user.name.charAt(0).toUpperCase()}
                            </div>
                        )}
                        <span className="absolute -bottom-0.5 -right-0.5 h-2 w-2 rounded-full bg-emerald-500 ring-1 ring-background" />
                    </div>
                ))}
            </div>
            {overflow > 0 && <span className="text-xs text-muted-foreground">+{overflow}</span>}
        </div>
    );
}

// ─── Spreadsheet Mode: Tab/Enter cell navigation ─────────────────────────

function useSpreadsheetMode(enabled: boolean, tableRef: React.RefObject<HTMLElement | null>) {
    useEffect(() => {
        if (!enabled || !tableRef.current) return;
        const table = tableRef.current;

        const getEditableCells = (): HTMLElement[] => {
            return Array.from(table.querySelectorAll("[data-editable-cell]")) as HTMLElement[];
        };

        const handleKeyDown = (e: KeyboardEvent) => {
            const target = e.target as HTMLElement;
            if (!target.closest("[data-editable-cell]")) return;

            const cells = getEditableCells();
            const currentIndex = cells.findIndex(cell => cell.contains(target));
            if (currentIndex === -1) return;

            if (e.key === "Tab") {
                e.preventDefault();
                const nextIndex = e.shiftKey ? currentIndex - 1 : currentIndex + 1;
                if (nextIndex >= 0 && nextIndex < cells.length) {
                    const nextCell = cells[nextIndex];
                    const input = nextCell.querySelector("input, [contenteditable]") as HTMLElement;
                    if (input) input.focus();
                    else nextCell.click();
                }
            } else if (e.key === "Enter" && !e.shiftKey) {
                // Move down to next row, same column
                const currentCell = cells[currentIndex];
                const colId = currentCell.dataset.columnId;
                const rowIdx = parseInt(currentCell.dataset.rowIndex ?? "0", 10);
                const nextRowCell = cells.find(c => c.dataset.columnId === colId && parseInt(c.dataset.rowIndex ?? "0", 10) === rowIdx + 1);
                if (nextRowCell) {
                    e.preventDefault();
                    const input = nextRowCell.querySelector("input, [contenteditable]") as HTMLElement;
                    if (input) input.focus();
                    else nextRowCell.click();
                }
            }
        };

        table.addEventListener("keydown", handleKeyDown);
        return () => table.removeEventListener("keydown", handleKeyDown);
    }, [enabled, tableRef]);
}

// ─── Kanban Board View ───────────────────────────────────────────────────

function KanbanLayout<TData>({ rows, columns, kanbanColumnId, renderCell, actions,
    onRowClick, rowLink, onKanbanMove, t, density, titleColumn, subtitleColumn }: {
    rows: TData[]; columns: DataTableColumnDef[];
    kanbanColumnId: string;
    renderCell?: (columnId: string, value: unknown, row: TData) => React.ReactNode | undefined;
    actions?: import("./types").DataTableAction<TData>[];
    onRowClick?: (row: TData) => void; rowLink?: (row: TData) => string;
    onKanbanMove?: (rowId: unknown, fromLane: string, toLane: string) => Promise<void> | void;
    t: DataTableTranslations; density: DataTableDensity;
    titleColumn?: string; subtitleColumn?: string;
}) {
    const kanbanCol = columns.find(c => c.id === kanbanColumnId);
    const lanes: { value: string; label: string }[] = kanbanCol?.options ?? [];

    // If no options defined, derive from data
    const derivedLanes = useMemo(() => {
        if (lanes.length > 0) return lanes;
        const uniqueValues = new Set<string>();
        for (const row of rows) {
            const val = (row as Record<string, unknown>)[kanbanColumnId];
            if (val != null) uniqueValues.add(String(val));
        }
        return [...uniqueValues].map(v => ({ value: v, label: v }));
    }, [lanes, rows, kanbanColumnId]);

    // Group rows by lane
    const laneRows = useMemo(() => {
        const map = new Map<string, TData[]>();
        for (const lane of derivedLanes) map.set(lane.value, []);
        for (const row of rows) {
            const val = String((row as Record<string, unknown>)[kanbanColumnId] ?? "");
            if (!map.has(val)) map.set(val, []);
            map.get(val)!.push(row);
        }
        return map;
    }, [rows, kanbanColumnId, derivedLanes]);

    const [draggedCard, setDraggedCard] = useState<{ rowId: unknown; fromLane: string } | null>(null);
    const [dragOverLane, setDragOverLane] = useState<string | null>(null);

    const handleDragStart = useCallback((rowId: unknown, fromLane: string) => {
        setDraggedCard({ rowId, fromLane });
    }, []);

    const handleDrop = useCallback((toLane: string) => {
        if (draggedCard && draggedCard.fromLane !== toLane && onKanbanMove) {
            onKanbanMove(draggedCard.rowId, draggedCard.fromLane, toLane);
        }
        setDraggedCard(null);
        setDragOverLane(null);
    }, [draggedCard, onKanbanMove]);

    const visibleCols = columns.filter(c => c.visible !== false && c.id !== kanbanColumnId && c.id !== titleColumn && c.id !== subtitleColumn).slice(0, 3);

    return (
        <div className="flex gap-4 overflow-x-auto pb-4 print:hidden">
            {derivedLanes.map(lane => {
                const laneItems = laneRows.get(lane.value) ?? [];
                const badge = kanbanCol?.options?.find(o => o.value === lane.value);
                return (
                    <div key={lane.value} className={cn("flex-shrink-0 w-72 rounded-xl border bg-muted/20",
                        dragOverLane === lane.value && "ring-2 ring-primary/50 bg-primary/5")}
                        onDragOver={(e) => { e.preventDefault(); setDragOverLane(lane.value); }}
                        onDragLeave={() => setDragOverLane(null)}
                        onDrop={() => handleDrop(lane.value)}>
                        {/* Lane header */}
                        <div className="flex items-center justify-between p-3 border-b">
                            <div className="flex items-center gap-2">
                                {badge && (
                                    <span className={cn("inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium",
                                        BADGE_VARIANTS[badge.variant ?? "default"] ?? BADGE_VARIANTS.default)}>
                                        {lane.label}
                                    </span>
                                )}
                                {!badge && <span className="text-sm font-semibold">{lane.label}</span>}
                            </div>
                            <span className="text-xs text-muted-foreground tabular-nums bg-muted rounded-full px-2 py-0.5">{laneItems.length}</span>
                        </div>
                        {/* Cards */}
                        <div className="p-2 space-y-2 min-h-[100px]">
                            {laneItems.length === 0 && (
                                <div className="text-center py-6 text-xs text-muted-foreground">{t.kanbanEmpty}</div>
                            )}
                            {laneItems.map((row, idx) => {
                                const rowData = row as Record<string, unknown>;
                                const rowId = rowData.id ?? idx;
                                const title = titleColumn ? String(rowData[titleColumn] ?? "") : null;
                                const subtitle = subtitleColumn ? String(rowData[subtitleColumn] ?? "") : null;
                                const isClickable = !!onRowClick || !!rowLink;
                                return (
                                    <div key={String(rowId)} draggable={!!onKanbanMove}
                                        onDragStart={() => handleDragStart(rowId, lane.value)}
                                        onDragEnd={() => { setDraggedCard(null); setDragOverLane(null); }}
                                        className={cn("rounded-lg border bg-card p-3 shadow-sm transition-all",
                                            "hover:shadow-md hover:border-primary/30",
                                            isClickable && "cursor-pointer",
                                            !!onKanbanMove && "cursor-grab active:cursor-grabbing",
                                            density === "compact" && "p-2 text-xs")}
                                        onClick={() => {
                                            if (rowLink) window.location.href = rowLink(row);
                                            else if (onRowClick) onRowClick(row);
                                        }}>
                                        {title && <div className="font-medium text-sm mb-1 truncate">{title}</div>}
                                        {subtitle && <div className="text-xs text-muted-foreground mb-2 truncate">{subtitle}</div>}
                                        {visibleCols.map(col => {
                                            const value = rowData[col.id];
                                            const custom = renderCell?.(col.id, value, row);
                                            return (
                                                <div key={col.id} className="flex justify-between text-xs py-0.5">
                                                    <span className="text-muted-foreground">{col.label}</span>
                                                    <span className="truncate ml-2">{custom !== undefined ? custom : value == null ? "—" : String(value)}</span>
                                                </div>
                                            );
                                        })}
                                        {actions && actions.length > 0 && (
                                            <div className="flex justify-end pt-2 mt-1 border-t">
                                                <DataTableRowActions row={row} actions={actions} t={t} />
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

// ─── Active filter chips ─────────────────────────────────────────────────────

function FilterChips({ filters, columns, onClear, onClearAll, t }: {
    filters: Record<string, unknown>;
    columns: DataTableColumnDef[];
    onClear: (columnId: string) => void;
    onClearAll: () => void;
    t: DataTableTranslations;
}) {
    const colMap = useMemo(() => new Map(columns.map((c) => [c.id, c])), [columns]);
    const entries = Object.entries(filters).filter(([, v]) => v !== null && v !== undefined && v !== "");
    if (entries.length === 0) return null;

    return (
        <div className="flex flex-wrap items-center gap-1.5 print:hidden">
            {entries.map(([key, value]) => {
                const col = colMap.get(key);
                const label = col?.label ?? key;
                const displayValue = String(value).replace(/^[a-z_]+:/i, "").replace(/,/g, ", ");
                return (
                    <span key={key} className="inline-flex items-center gap-1 rounded-full bg-primary/10 ring-1 ring-inset ring-primary/20 px-2.5 py-0.5 text-xs font-medium text-primary">
                        <span className="text-primary/60">{label}:</span> {displayValue}
                        <button type="button" onClick={() => onClear(key)}
                            className="ml-0.5 rounded-full p-0.5 hover:bg-primary/20 transition-colors">
                            <X className="h-3 w-3" />
                        </button>
                    </span>
                );
            })}
            {entries.length > 1 && (
                <button type="button" onClick={onClearAll}
                    className="text-xs text-muted-foreground hover:text-foreground transition-colors underline-offset-2 hover:underline">
                    {t.clearAllFilters}
                </button>
            )}
        </div>
    );
}

// ─── Inline row creation ─────────────────────────────────────────────────────

function InlineRowCreator({ columns, onRowCreate, t }: {
    columns: DataTableColumnDef[];
    onRowCreate: (data: Record<string, unknown>) => Promise<void> | void;
    t: DataTableTranslations;
}) {
    const [isCreating, setIsCreating] = useState(false);
    const [values, setValues] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);

    const editableCols = useMemo(
        () => columns.filter((c) => c.editable && c.type !== "image"),
        [columns],
    );

    const handleSave = useCallback(async () => {
        setSaving(true);
        try {
            const parsed: Record<string, unknown> = {};
            for (const col of editableCols) {
                const raw = values[col.id] ?? "";
                if (col.type === "number" || col.type === "currency" || col.type === "percentage") {
                    parsed[col.id] = raw ? Number(raw) : null;
                } else if (col.type === "boolean") {
                    parsed[col.id] = raw === "true" || raw === "1";
                } else {
                    parsed[col.id] = raw || null;
                }
            }
            await onRowCreate(parsed);
            setValues({});
            setIsCreating(false);
        } finally { setSaving(false); }
    }, [editableCols, values, onRowCreate]);

    if (!isCreating) {
        return (
            <Button variant="outline" size="sm" className="h-8 gap-1.5" onClick={() => setIsCreating(true)}>
                <span className="text-lg leading-none">+</span>
                <span>{t.addRow ?? "Add row"}</span>
            </Button>
        );
    }

    return (
        <div className="flex items-end gap-2 rounded-lg border bg-muted/20 p-3 print:hidden">
            {editableCols.map((col) => (
                <div key={col.id} className="grid gap-1">
                    <Label className="text-xs">{col.label}</Label>
                    <Input
                        type={col.type === "number" || col.type === "currency" || col.type === "percentage" ? "number" : "text"}
                        value={values[col.id] ?? ""}
                        onChange={(e) => setValues((prev) => ({ ...prev, [col.id]: e.target.value }))}
                        onKeyDown={(e) => { if (e.key === "Enter") handleSave(); if (e.key === "Escape") setIsCreating(false); }}
                        className="h-8 w-32 text-sm"
                        placeholder={col.label}
                    />
                </div>
            ))}
            <div className="flex items-center gap-1">
                <Button size="sm" className="h-8" onClick={handleSave} disabled={saving}>
                    {saving ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : t.save}
                </Button>
                <Button variant="ghost" size="sm" className="h-8" onClick={() => { setIsCreating(false); setValues({}); }}>
                    {t.cancel}
                </Button>
            </div>
        </div>
    );
}

// ─── Inline select cell ──────────────────────────────────────────────────────

function SelectCell({ value, options, onSave }: {
    value: string; options: { label: string; value: string }[];
    onSave: (value: string) => Promise<void> | void;
}) {
    const [saving, setSaving] = useState(false);
    const handleChange = useCallback(async (newVal: string) => {
        setSaving(true);
        try { await onSave(newVal); } finally { setSaving(false); }
    }, [onSave]);
    return (
        <Select value={value} onValueChange={handleChange} disabled={saving}>
            <SelectTrigger className="h-7 w-auto min-w-[100px] text-sm">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {options.map((opt) => (
                    <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

// ─── Multi-select cell for inline editing multiOption columns ─────────────────

function MultiSelectCell({ value, options, onSave }: {
    value: string[]; options: { label: string; value: string }[];
    onSave: (value: string[]) => Promise<void> | void;
}) {
    const [selected, setSelected] = useState<string[]>(value);
    const [saving, setSaving] = useState(false);
    const [open, setOpen] = useState(false);

    const toggleOption = useCallback(async (optValue: string) => {
        const next = selected.includes(optValue)
            ? selected.filter((v) => v !== optValue)
            : [...selected, optValue];
        setSelected(next);
        setSaving(true);
        try { await onSave(next); } finally { setSaving(false); }
    }, [selected, onSave]);

    const selectedLabels = selected
        .map((v) => options.find((o) => o.value === v)?.label ?? v)
        .join(", ");

    return (
        <DropdownMenu open={open} onOpenChange={setOpen}>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="h-7 w-auto min-w-[100px] text-sm font-normal justify-start" disabled={saving}>
                    {selectedLabels || <span className="text-muted-foreground">Select...</span>}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="w-48">
                {options.map((opt) => (
                    <DropdownMenuItem key={opt.value} onClick={(e) => { e.preventDefault(); toggleOption(opt.value); }}
                        className="flex items-center gap-2">
                        <span className={`h-4 w-4 border rounded flex items-center justify-center text-xs ${selected.includes(opt.value) ? "bg-primary text-primary-foreground border-primary" : "border-input"}`}>
                            {selected.includes(opt.value) ? "✓" : ""}
                        </span>
                        {opt.label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

// ─── Form action dialog ──────────────────────────────────────────────────────

function FormActionDialog<TData>({ open, onOpenChange, action, row, t }: {
    open: boolean; onOpenChange: (open: boolean) => void;
    action: { label: string; form?: DataTableFormField[]; onClick: (row: TData) => void };
    row: TData; t: DataTableTranslations;
}) {
    const fields = action.form ?? [];
    const [values, setValues] = useState<Record<string, unknown>>(() => {
        const init: Record<string, unknown> = {};
        for (const f of fields) init[f.name] = f.defaultValue ?? (f.type === "checkbox" ? false : "");
        return init;
    });
    const handleSubmit = useCallback(() => {
        // Attach form values to the row for the handler
        const enrichedRow = { ...row, _formValues: values } as TData;
        action.onClick(enrichedRow);
        onOpenChange(false);
    }, [action, row, values, onOpenChange]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{action.label}</DialogTitle>
                </DialogHeader>
                <div className="grid gap-3 py-2">
                    {fields.map((field) => (
                        <div key={field.name} className="grid gap-1.5">
                            <Label className="text-sm">{field.label}{field.required && <span className="text-destructive"> *</span>}</Label>
                            {field.type === "select" ? (
                                <Select value={String(values[field.name] ?? "")} onValueChange={(v) => setValues((p) => ({ ...p, [field.name]: v }))}>
                                    <SelectTrigger><SelectValue placeholder={field.placeholder} /></SelectTrigger>
                                    <SelectContent>
                                        {field.options?.map((opt) => <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            ) : field.type === "textarea" ? (
                                <textarea className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    value={String(values[field.name] ?? "")} placeholder={field.placeholder}
                                    onChange={(e) => setValues((p) => ({ ...p, [field.name]: e.target.value }))} />
                            ) : field.type === "checkbox" ? (
                                <div className="flex items-center gap-2">
                                    <Checkbox checked={!!values[field.name]} onCheckedChange={(v) => setValues((p) => ({ ...p, [field.name]: !!v }))} />
                                </div>
                            ) : (
                                <Input type={field.type === "number" ? "number" : "text"}
                                    value={String(values[field.name] ?? "")} placeholder={field.placeholder}
                                    onChange={(e) => setValues((p) => ({ ...p, [field.name]: field.type === "number" ? Number(e.target.value) : e.target.value }))} />
                            )}
                        </div>
                    ))}
                </div>
                <DialogFoot>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>{t.cancel}</Button>
                    <Button onClick={handleSubmit}>{t.confirmAction}</Button>
                </DialogFoot>
            </DialogContent>
        </Dialog>
    );
}

// ─── User-selectable grouping dropdown ───────────────────────────────────────

function GroupBySelector({ options, columns, currentGroupBy, onChange, t }: {
    options: string[]; columns: DataTableColumnDef[];
    currentGroupBy: string | null; onChange: (columnId: string | null) => void;
    t: DataTableTranslations;
}) {
    const colMap = new Map(columns.map((c) => [c.id, c]));
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="h-8 gap-1.5">
                    <AlignJustify className="h-3.5 w-3.5" />
                    <span className="hidden sm:inline">{t.groupBy}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem onClick={() => onChange(null)} className={cn(!currentGroupBy && "font-semibold")}>
                    {!currentGroupBy && <Check className="mr-2 h-3.5 w-3.5" />}
                    <span className={currentGroupBy ? "ml-6" : ""}>{t.none}</span>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                {options.map((colId) => (
                    <DropdownMenuItem key={colId} onClick={() => onChange(colId)} className={cn(currentGroupBy === colId && "font-semibold")}>
                        {currentGroupBy === colId && <Check className="mr-2 h-3.5 w-3.5" />}
                        <span className={currentGroupBy !== colId ? "ml-6" : ""}>{colMap.get(colId)?.label ?? colId}</span>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

// ─── Master/Detail nested sub-table ──────────────────────────────────────────

function MasterDetailRow<TData>({ row, colSpan, renderContent, t }: {
    row: TData; colSpan: number;
    renderContent: (row: TData) => React.ReactNode;
    t: DataTableTranslations;
}) {
    const [loaded, setLoaded] = useState(false);
    useEffect(() => { const timer = setTimeout(() => setLoaded(true), 0); return () => clearTimeout(timer); }, []);
    return (
        <TableRow className="bg-muted/10 hover:bg-muted/20 border-b border-border/30">
            <TableCell colSpan={colSpan} className="p-0">
                <div className="border-l-4 border-primary/30 pl-4 py-3 pr-3">
                    {loaded ? renderContent(row) : (
                        <div className="flex items-center gap-2 py-4 text-sm text-muted-foreground">
                            <Loader2 className="h-4 w-4 animate-spin" />{t.masterDetailLoading}
                        </div>
                    )}
                </div>
            </TableCell>
        </TableRow>
    );
}

// ─── Integrated Charts ───────────────────────────────────────────────────────

type ChartKind = "bar" | "line" | "pie" | "doughnut";

interface IntegratedChartState {
    columnId: string;
    chartType: ChartKind;
}

/** Minimal SVG-based chart renderer — zero external dependencies */
function IntegratedChartPanel({ data, columns, chartState, onClose, onChangeColumn, onChangeType, availableTypes, t }: {
    data: Record<string, unknown>[];
    columns: DataTableColumnDef[];
    chartState: IntegratedChartState;
    onClose: () => void;
    onChangeColumn: (colId: string) => void;
    onChangeType: (type: ChartKind) => void;
    availableTypes: ChartKind[];
    t: DataTableTranslations;
}) {
    const numericColumns = useMemo(() => columns.filter(c => c.type === "number" || c.type === "currency" || c.type === "percentage"), [columns]);
    const labelColumns = useMemo(() => columns.filter(c => c.type === "text" || c.type === "option" || c.type === "badge"), [columns]);

    const chartData = useMemo(() => {
        const values: { label: string; value: number }[] = [];
        const labelCol = labelColumns[0];
        for (const row of data) {
            const rawVal = row[chartState.columnId];
            const num = typeof rawVal === "number" ? rawVal : Number(rawVal);
            if (isNaN(num)) continue;
            const label = labelCol ? String(row[labelCol.id] ?? "") : `Row ${values.length + 1}`;
            values.push({ label, value: num });
        }
        return values.slice(0, 50); // limit for readability
    }, [data, chartState.columnId, labelColumns]);

    const maxVal = useMemo(() => Math.max(...chartData.map(d => Math.abs(d.value)), 1), [chartData]);
    const total = useMemo(() => chartData.reduce((s, d) => s + Math.abs(d.value), 0), [chartData]);

    // Color palette
    const colors = ["#3b82f6", "#ef4444", "#22c55e", "#f59e0b", "#8b5cf6", "#ec4899", "#06b6d4", "#f97316", "#6366f1", "#14b8a6",
        "#e11d48", "#84cc16", "#7c3aed", "#0ea5e9", "#d946ef", "#10b981", "#f43f5e", "#a855f7", "#0891b2", "#65a30d"];

    const chartTypeLabels: Record<ChartKind, string> = { bar: t.chartBar, line: t.chartLine, pie: t.chartPie, doughnut: t.chartDoughnut };

    const renderBarChart = () => {
        if (chartData.length === 0) return <text x="200" y="100" textAnchor="middle" className="fill-muted-foreground text-sm">{t.chartNoData}</text>;
        const barWidth = Math.max(8, Math.min(40, 380 / chartData.length - 4));
        const chartWidth = chartData.length * (barWidth + 4);
        return (
            <svg viewBox={`0 0 ${Math.max(400, chartWidth + 40)} 220`} className="w-full h-48">
                {chartData.map((d, i) => {
                    const barH = (Math.abs(d.value) / maxVal) * 180;
                    const x = 20 + i * (barWidth + 4);
                    return (
                        <g key={i}>
                            <rect x={x} y={200 - barH} width={barWidth} height={barH} fill={colors[i % colors.length]} rx="2" opacity="0.85">
                                <title>{`${d.label}: ${d.value}`}</title>
                            </rect>
                            {chartData.length <= 20 && (
                                <text x={x + barWidth / 2} y={215} textAnchor="middle" className="fill-muted-foreground" style={{ fontSize: "8px" }}>
                                    {d.label.length > 6 ? d.label.slice(0, 5) + "…" : d.label}
                                </text>
                            )}
                        </g>
                    );
                })}
            </svg>
        );
    };

    const renderLineChart = () => {
        if (chartData.length < 2) return <text x="200" y="100" textAnchor="middle" className="fill-muted-foreground text-sm">{t.chartNoData}</text>;
        const w = 400, h = 200, px = 20, py = 10;
        const points = chartData.map((d, i) => ({
            x: px + (i / (chartData.length - 1)) * (w - 2 * px),
            y: py + (1 - Math.abs(d.value) / maxVal) * (h - 2 * py),
        }));
        const pathD = points.map((p, i) => `${i === 0 ? "M" : "L"} ${p.x} ${p.y}`).join(" ");
        return (
            <svg viewBox={`0 0 ${w} ${h + 20}`} className="w-full h-48">
                <path d={pathD} fill="none" stroke="#3b82f6" strokeWidth="2" strokeLinejoin="round" />
                {points.map((p, i) => (
                    <circle key={i} cx={p.x} cy={p.y} r="3" fill="#3b82f6">
                        <title>{`${chartData[i].label}: ${chartData[i].value}`}</title>
                    </circle>
                ))}
            </svg>
        );
    };

    const renderPieChart = (isDoughnut: boolean) => {
        if (chartData.length === 0 || total === 0) return <text x="150" y="100" textAnchor="middle" className="fill-muted-foreground text-sm">{t.chartNoData}</text>;
        const cx = 150, cy = 100, r = 80, innerR = isDoughnut ? 45 : 0;
        let startAngle = -Math.PI / 2;
        const slices = chartData.slice(0, 20).map((d, i) => {
            const sliceAngle = (Math.abs(d.value) / total) * 2 * Math.PI;
            const endAngle = startAngle + sliceAngle;
            const x1 = cx + r * Math.cos(startAngle), y1 = cy + r * Math.sin(startAngle);
            const x2 = cx + r * Math.cos(endAngle), y2 = cy + r * Math.sin(endAngle);
            const ix1 = cx + innerR * Math.cos(startAngle), iy1 = cy + innerR * Math.sin(startAngle);
            const ix2 = cx + innerR * Math.cos(endAngle), iy2 = cy + innerR * Math.sin(endAngle);
            const largeArc = sliceAngle > Math.PI ? 1 : 0;
            const pathD = isDoughnut
                ? `M ${x1} ${y1} A ${r} ${r} 0 ${largeArc} 1 ${x2} ${y2} L ${ix2} ${iy2} A ${innerR} ${innerR} 0 ${largeArc} 0 ${ix1} ${iy1} Z`
                : `M ${cx} ${cy} L ${x1} ${y1} A ${r} ${r} 0 ${largeArc} 1 ${x2} ${y2} Z`;
            startAngle = endAngle;
            return <path key={i} d={pathD} fill={colors[i % colors.length]} opacity="0.85"><title>{`${d.label}: ${d.value}`}</title></path>;
        });
        return (
            <svg viewBox="0 0 300 200" className="w-full h-48">
                {slices}
            </svg>
        );
    };

    return (
        <div className="rounded-lg border bg-card shadow-sm p-4 print:hidden">
            <div className="flex items-center justify-between mb-3">
                <h3 className="text-sm font-semibold">{t.chartTitle}</h3>
                <div className="flex items-center gap-2">
                    <Select value={chartState.columnId} onValueChange={onChangeColumn}>
                        <SelectTrigger className="h-7 w-[140px] text-xs">
                            <SelectValue placeholder={t.chartColumn} />
                        </SelectTrigger>
                        <SelectContent>
                            {numericColumns.map(c => <SelectItem key={c.id} value={c.id}>{c.label}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    <Select value={chartState.chartType} onValueChange={(v) => onChangeType(v as ChartKind)}>
                        <SelectTrigger className="h-7 w-[100px] text-xs">
                            <SelectValue placeholder={t.chartType} />
                        </SelectTrigger>
                        <SelectContent>
                            {availableTypes.map(type => <SelectItem key={type} value={type}>{chartTypeLabels[type]}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    <Button variant="ghost" size="icon" className="h-7 w-7" onClick={onClose}><X className="h-3.5 w-3.5" /></Button>
                </div>
            </div>
            <div className="bg-muted/20 rounded-md p-2">
                <svg width="0" height="0"><defs /></svg>
                {chartState.chartType === "bar" && renderBarChart()}
                {chartState.chartType === "line" && renderLineChart()}
                {chartState.chartType === "pie" && renderPieChart(false)}
                {chartState.chartType === "doughnut" && renderPieChart(true)}
            </div>
            {/* Legend */}
            {(chartState.chartType === "pie" || chartState.chartType === "doughnut") && chartData.length > 0 && (
                <div className="flex flex-wrap gap-x-3 gap-y-1 mt-2">
                    {chartData.slice(0, 20).map((d, i) => (
                        <div key={i} className="flex items-center gap-1 text-[10px] text-muted-foreground">
                            <span className="inline-block h-2 w-2 rounded-full" style={{ backgroundColor: colors[i % colors.length] }} />
                            {d.label}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

// ─── Find & Replace ──────────────────────────────────────────────────────────

interface FindReplaceMatch {
    rowIndex: number;
    columnId: string;
    rowId: unknown;
    value: string;
}

function useFindReplace(enabled: boolean, data: Record<string, unknown>[], columns: DataTableColumnDef[]) {
    const [query, setQuery] = useState("");
    const [replacement, setReplacement] = useState("");
    const [caseSensitive, setCaseSensitive] = useState(false);
    const [currentIndex, setCurrentIndex] = useState(0);

    const matches = useMemo<FindReplaceMatch[]>(() => {
        if (!enabled || !query) return [];
        const q = caseSensitive ? query : query.toLowerCase();
        const result: FindReplaceMatch[] = [];
        const searchableCols = columns.filter(c => c.type === "text" || c.type === "option" || c.type === "badge" || c.type === "email" || c.type === "link" || c.type === "phone");
        for (let i = 0; i < data.length; i++) {
            const row = data[i];
            const rowId = row.id ?? i;
            for (const col of searchableCols) {
                const val = String(row[col.id] ?? "");
                const compareVal = caseSensitive ? val : val.toLowerCase();
                if (compareVal.includes(q)) {
                    result.push({ rowIndex: i, columnId: col.id, rowId, value: val });
                }
            }
        }
        return result;
    }, [enabled, query, caseSensitive, data, columns]);

    const goToNext = useCallback(() => {
        if (matches.length === 0) return;
        setCurrentIndex(prev => (prev + 1) % matches.length);
    }, [matches.length]);

    const goToPrevious = useCallback(() => {
        if (matches.length === 0) return;
        setCurrentIndex(prev => (prev - 1 + matches.length) % matches.length);
    }, [matches.length]);

    // Reset index when matches change
    useEffect(() => { setCurrentIndex(0); }, [matches.length]);

    return { query, setQuery, replacement, setReplacement, caseSensitive, setCaseSensitive, matches, currentIndex, setCurrentIndex, goToNext, goToPrevious };
}

function FindReplaceBar({ state, onReplace, onReplaceAll, onClose, t }: {
    state: ReturnType<typeof useFindReplace>;
    onReplace: (match: FindReplaceMatch, newValue: string) => void;
    onReplaceAll: (matches: FindReplaceMatch[], replacement: string) => void;
    onClose: () => void;
    t: DataTableTranslations;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    useEffect(() => { inputRef.current?.focus(); }, []);

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === "Escape") { onClose(); return; }
        if (e.key === "Enter") {
            if (e.shiftKey) state.goToPrevious();
            else state.goToNext();
        }
    };

    const currentMatch = state.matches[state.currentIndex] ?? null;

    return (
        <div className="flex flex-wrap items-center gap-2 rounded-lg border bg-card shadow-sm px-3 py-2 print:hidden">
            <Search className="h-3.5 w-3.5 text-muted-foreground shrink-0" />
            <Input ref={inputRef} value={state.query} onChange={(e) => state.setQuery(e.target.value)}
                onKeyDown={handleKeyDown}
                placeholder={t.findPlaceholder} className="h-7 w-40 text-xs" />
            <Input value={state.replacement} onChange={(e) => state.setReplacement(e.target.value)}
                onKeyDown={handleKeyDown}
                placeholder={t.replacePlaceholder} className="h-7 w-40 text-xs" />
            <div className="flex items-center gap-1">
                <Button variant="ghost" size="sm" className="h-7 px-2 text-xs" onClick={state.goToPrevious} disabled={state.matches.length === 0}>{t.findPrevious}</Button>
                <Button variant="ghost" size="sm" className="h-7 px-2 text-xs" onClick={state.goToNext} disabled={state.matches.length === 0}>{t.findNext}</Button>
            </div>
            <div className="flex items-center gap-1">
                <Button variant="outline" size="sm" className="h-7 px-2 text-xs"
                    disabled={!currentMatch || !state.replacement}
                    onClick={() => { if (currentMatch && state.replacement) onReplace(currentMatch, state.replacement); }}>
                    {t.replaceOne}
                </Button>
                <Button variant="outline" size="sm" className="h-7 px-2 text-xs"
                    disabled={state.matches.length === 0 || !state.replacement}
                    onClick={() => { if (state.matches.length > 0 && state.replacement) onReplaceAll(state.matches, state.replacement); }}>
                    {t.replaceAll}
                </Button>
            </div>
            <label className="flex items-center gap-1 text-xs text-muted-foreground cursor-pointer">
                <Checkbox checked={state.caseSensitive} onCheckedChange={(v) => state.setCaseSensitive(!!v)} className="h-3.5 w-3.5" />
                {t.findCaseSensitive}
            </label>
            <span className="text-xs text-muted-foreground tabular-nums">
                {state.query ? (state.matches.length > 0 ? t.findMatchesCount(state.currentIndex + 1, state.matches.length) : t.findNoMatches) : ""}
            </span>
            <Button variant="ghost" size="icon" className="h-7 w-7 ml-auto" onClick={onClose}><X className="h-3.5 w-3.5" /></Button>
        </div>
    );
}

// ─── AI Assistant ────────────────────────────────────────────────────────────

interface AiInsight {
    type: "anomaly" | "trend" | "pattern" | "recommendation";
    title: string;
    description: string;
    severity?: "info" | "warning" | "critical";
    column?: string;
    action?: { filters?: Record<string, unknown>; sort?: string };
}

interface AiSuggestion {
    label: string;
    description: string;
    action: { filters?: Record<string, unknown>; sort?: string };
    icon?: string;
}

interface AiColumnSummaryResult {
    summary: string;
    highlights: string[];
    suggestion?: string;
}

interface AiEnrichResult {
    column_name: string;
    enrichments: Record<string, string>;
}

function useAiAssistant(aiBaseUrl: string | undefined) {
    const [insights, setInsights] = useState<AiInsight[]>([]);
    const [suggestions, setSuggestions] = useState<AiSuggestion[]>([]);
    const [columnSummary, setColumnSummary] = useState<AiColumnSummaryResult | null>(null);
    const [enrichResult, setEnrichResult] = useState<AiEnrichResult | null>(null);
    const [visualizeHtml, setVisualizeHtml] = useState<string | null>(null);
    const [loadingInsights, setLoadingInsights] = useState(false);
    const [loadingSuggestions, setLoadingSuggestions] = useState(false);
    const [loadingColumnSummary, setLoadingColumnSummary] = useState(false);
    const [loadingEnrich, setLoadingEnrich] = useState(false);
    const [loadingVisualize, setLoadingVisualize] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const fetchJson = useCallback(async (endpoint: string, body: Record<string, unknown>) => {
        if (!aiBaseUrl) throw new Error("AI base URL not configured");
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        const res = await fetch(`${aiBaseUrl}/${endpoint}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                ...(csrfToken ? { "X-CSRF-TOKEN": csrfToken } : {}),
            },
            body: JSON.stringify(body),
        });
        if (!res.ok) {
            const data = await res.json().catch(() => null);
            throw new Error(data?.error || `Request failed (${res.status})`);
        }
        return res.json();
    }, [aiBaseUrl]);

    const queryNlp = useCallback(async (query: string) => {
        setError(null);
        return fetchJson("query", { query });
    }, [fetchJson]);

    const fetchInsights = useCallback(async () => {
        setLoadingInsights(true);
        setError(null);
        try {
            const data = await fetchJson("insights", {});
            setInsights(data.insights || []);
        } catch (e) {
            setError((e as Error).message);
        } finally {
            setLoadingInsights(false);
        }
    }, [fetchJson]);

    const fetchSuggestions = useCallback(async (currentFilters?: Record<string, unknown>) => {
        setLoadingSuggestions(true);
        setError(null);
        try {
            const data = await fetchJson("suggest", { current_filters: currentFilters || {} });
            setSuggestions(data.suggestions || []);
        } catch (e) {
            setError((e as Error).message);
        } finally {
            setLoadingSuggestions(false);
        }
    }, [fetchJson]);

    const fetchColumnSummary = useCallback(async (columnId: string) => {
        setLoadingColumnSummary(true);
        setError(null);
        setColumnSummary(null);
        try {
            const data = await fetchJson("column-summary", { column: columnId });
            setColumnSummary(data);
        } catch (e) {
            setError((e as Error).message);
        } finally {
            setLoadingColumnSummary(false);
        }
    }, [fetchJson]);

    const enrichRows = useCallback(async (prompt: string, columnName: string, rowIds: unknown[]) => {
        setLoadingEnrich(true);
        setError(null);
        setEnrichResult(null);
        try {
            const data = await fetchJson("enrich", { prompt, column_name: columnName, row_ids: rowIds });
            setEnrichResult(data);
            return data as AiEnrichResult;
        } catch (e) {
            setError((e as Error).message);
            return null;
        } finally {
            setLoadingEnrich(false);
        }
    }, [fetchJson]);

    const fetchVisualize = useCallback(async (prompt?: string) => {
        setLoadingVisualize(true);
        setError(null);
        setVisualizeHtml(null);
        try {
            const data = await fetchJson("visualize", { prompt: prompt || "" });
            setVisualizeHtml(data.html || null);
        } catch (e) {
            setError((e as Error).message);
        } finally {
            setLoadingVisualize(false);
        }
    }, [fetchJson]);

    return {
        insights, suggestions, columnSummary, enrichResult, visualizeHtml,
        loadingInsights, loadingSuggestions, loadingColumnSummary, loadingEnrich, loadingVisualize,
        error, queryNlp, fetchInsights, fetchSuggestions, fetchColumnSummary, enrichRows, fetchVisualize,
        clearError: () => setError(null),
    };
}

/** AI Assistant Panel — displays insights, suggestions, NLQ input, and enrichment */
function AiAssistantPanel({ ai, t, onApplyAction, onClose, columns, selectedRowIds, hasThesys }: {
    ai: ReturnType<typeof useAiAssistant>;
    t: DataTableTranslations;
    onApplyAction: (action: { filters?: Record<string, unknown>; sort?: string }) => void;
    onClose: () => void;
    columns: DataTableColumnDef[];
    selectedRowIds: unknown[];
    hasThesys?: boolean;
}) {
    const tabs = [
        { id: "insights" as const, label: t.aiInsights, icon: Lightbulb },
        { id: "suggestions" as const, label: t.aiSuggestions, icon: TrendingUp },
        { id: "summary" as const, label: t.aiColumnSummary, icon: BarChart3 },
        { id: "enrich" as const, label: t.aiEnrich, icon: Sparkles },
        ...(hasThesys ? [{ id: "visualize" as const, label: t.aiVisualize, icon: BarChart3 }] : []),
    ];

    type TabId = typeof tabs[number]["id"];
    const [activeTab, setActiveTab] = useState<TabId>("insights");
    const [enrichPrompt, setEnrichPrompt] = useState("");
    const [vizPrompt, setVizPrompt] = useState("");
    const [enrichColName, setEnrichColName] = useState("");
    const [summaryColumnId, setSummaryColumnId] = useState<string | null>(null);

    const insightTypeIcon = (type: string) => {
        switch (type) {
            case "anomaly": return <AlertTriangle className="h-4 w-4 text-amber-500" />;
            case "trend": return <TrendingUp className="h-4 w-4 text-blue-500" />;
            case "pattern": return <BarChart3 className="h-4 w-4 text-purple-500" />;
            case "recommendation": return <Lightbulb className="h-4 w-4 text-emerald-500" />;
            default: return <MessageSquare className="h-4 w-4 text-muted-foreground" />;
        }
    };

    const insightTypeLabel = (type: string) => {
        switch (type) {
            case "anomaly": return t.aiAnomaly;
            case "trend": return t.aiTrend;
            case "pattern": return t.aiPattern;
            case "recommendation": return t.aiRecommendation;
            default: return type;
        }
    };

    const severityColor = (severity?: string) => {
        switch (severity) {
            case "critical": return "border-destructive/50 bg-destructive/5";
            case "warning": return "border-amber-500/50 bg-amber-500/5";
            default: return "border-border bg-card";
        }
    };

    // Badge dot for tabs that have loaded data
    const hasData = (tab: TabId) => {
        switch (tab) {
            case "insights": return ai.insights.length > 0;
            case "suggestions": return ai.suggestions.length > 0;
            case "summary": return !!ai.columnSummary;
            case "enrich": return !!ai.enrichResult;
            case "visualize": return !!ai.visualizeHtml;
        }
    };

    return (
        <div className="rounded-lg border bg-card text-card-foreground shadow-sm print:hidden">
            {/* ── Header ── */}
            <div className="flex items-center gap-2 border-b px-4 py-3">
                <div className="flex items-center gap-2">
                    <Sparkles className="h-4 w-4" />
                    <span className="text-sm font-semibold">{t.aiAssistant}</span>
                </div>
                {ai.error && (
                    <div className="ml-3 flex items-center gap-1.5 rounded-md border border-destructive/30 bg-destructive/10 px-2 py-0.5">
                        <AlertTriangle className="h-3 w-3 text-destructive" />
                        <span className="text-xs text-destructive">{ai.error}</span>
                    </div>
                )}
                <Button variant="ghost" size="icon" className="ml-auto h-7 w-7" onClick={onClose}>
                    <X className="h-3.5 w-3.5" />
                </Button>
            </div>

            {/* ── Tab navigation ── */}
            <div className="border-b">
                <div className="flex gap-0 overflow-x-auto px-2">
                    {tabs.map(tab => {
                        const Icon = tab.icon;
                        const active = activeTab === tab.id;
                        return (
                            <button
                                key={tab.id}
                                className={`relative flex items-center gap-1.5 whitespace-nowrap px-3 py-2.5 text-xs font-medium transition-colors ${
                                    active
                                        ? "text-foreground"
                                        : "text-muted-foreground hover:text-foreground"
                                }`}
                                onClick={() => {
                                    setActiveTab(tab.id);
                                    if (tab.id === "insights" && ai.insights.length === 0 && !ai.loadingInsights) ai.fetchInsights();
                                    if (tab.id === "suggestions" && ai.suggestions.length === 0 && !ai.loadingSuggestions) ai.fetchSuggestions();
                                }}
                            >
                                <Icon className="h-3.5 w-3.5" />
                                {tab.label}
                                {hasData(tab.id) && (
                                    <span className="ml-0.5 h-1.5 w-1.5 rounded-full bg-primary" />
                                )}
                                {/* Active indicator bar */}
                                {active && (
                                    <span className="absolute inset-x-0 -bottom-px h-0.5 bg-primary" />
                                )}
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* ── Content area ── */}
            <div className="max-h-[28rem] overflow-y-auto p-4">

                {/* ── Insights tab ── */}
                {activeTab === "insights" && (
                    <div className="space-y-3">
                        {/* Header with refresh */}
                        <div className="flex items-center justify-between">
                            <p className="text-xs font-medium text-muted-foreground">{t.aiInsights}</p>
                            <Button
                                variant="ghost" size="sm" className="h-6 gap-1 px-2 text-xs text-muted-foreground"
                                disabled={ai.loadingInsights}
                                onClick={() => ai.fetchInsights()}
                            >
                                <RefreshCw className={`h-3 w-3 ${ai.loadingInsights ? "animate-spin" : ""}`} />
                                {t.aiRefresh}
                            </Button>
                        </div>

                        {/* Loading state */}
                        {ai.loadingInsights && ai.insights.length === 0 && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <Loader2 className="h-5 w-5 animate-spin text-muted-foreground" />
                                <p className="text-xs text-muted-foreground">{t.aiInsightsLoading}</p>
                            </div>
                        )}

                        {/* Empty state */}
                        {!ai.loadingInsights && ai.insights.length === 0 && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <Lightbulb className="h-8 w-8 text-muted-foreground/30" />
                                <p className="text-xs text-muted-foreground">{t.aiNoInsights}</p>
                                <Button variant="outline" size="sm" className="mt-1 h-7 text-xs" onClick={() => ai.fetchInsights()}>
                                    <Sparkles className="mr-1.5 h-3 w-3" />
                                    {t.aiInsights}
                                </Button>
                            </div>
                        )}

                        {/* Insight cards */}
                        {ai.insights.map((insight, i) => (
                            <div key={i} className={`rounded-lg border p-3 text-sm ${severityColor(insight.severity)}`}>
                                <div className="flex items-start gap-2.5">
                                    <div className="mt-0.5 shrink-0">{insightTypeIcon(insight.type)}</div>
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-2">
                                            <span className="rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-muted-foreground">
                                                {insightTypeLabel(insight.type)}
                                            </span>
                                        </div>
                                        <p className="mt-1.5 text-xs font-semibold">{insight.title}</p>
                                        <p className="mt-0.5 text-xs text-muted-foreground leading-relaxed">{insight.description}</p>
                                        {insight.action && (
                                            <Button variant="outline" size="sm" className="mt-2 h-6 gap-1 px-2.5 text-[11px]" onClick={() => onApplyAction(insight.action!)}>
                                                {t.aiApply}
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* ── Suggestions tab ── */}
                {activeTab === "suggestions" && (
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <p className="text-xs font-medium text-muted-foreground">{t.aiSuggestions}</p>
                            <Button
                                variant="ghost" size="sm" className="h-6 gap-1 px-2 text-xs text-muted-foreground"
                                disabled={ai.loadingSuggestions}
                                onClick={() => ai.fetchSuggestions()}
                            >
                                <RefreshCw className={`h-3 w-3 ${ai.loadingSuggestions ? "animate-spin" : ""}`} />
                                {t.aiRefresh}
                            </Button>
                        </div>

                        {ai.loadingSuggestions && ai.suggestions.length === 0 && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <Loader2 className="h-5 w-5 animate-spin text-muted-foreground" />
                                <p className="text-xs text-muted-foreground">{t.aiSuggestionsLoading}</p>
                            </div>
                        )}

                        {!ai.loadingSuggestions && ai.suggestions.length === 0 && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <TrendingUp className="h-8 w-8 text-muted-foreground/30" />
                                <p className="text-xs text-muted-foreground">{t.aiNoSuggestions}</p>
                                <Button variant="outline" size="sm" className="mt-1 h-7 text-xs" onClick={() => ai.fetchSuggestions()}>
                                    <Sparkles className="mr-1.5 h-3 w-3" />
                                    {t.aiSuggestions}
                                </Button>
                            </div>
                        )}

                        <div className="grid gap-2">
                            {ai.suggestions.map((suggestion, i) => (
                                <button
                                    key={i}
                                    className="group flex w-full items-start gap-3 rounded-lg border p-3 text-left transition-colors hover:bg-accent/50"
                                    onClick={() => onApplyAction(suggestion.action)}
                                >
                                    <TrendingUp className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground transition-colors group-hover:text-foreground" />
                                    <div className="min-w-0">
                                        <p className="text-xs font-semibold">{suggestion.label}</p>
                                        <p className="mt-0.5 text-xs text-muted-foreground leading-relaxed">{suggestion.description}</p>
                                    </div>
                                    <ChevronRight className="ml-auto mt-0.5 h-4 w-4 shrink-0 text-muted-foreground/50 transition-transform group-hover:translate-x-0.5" />
                                </button>
                            ))}
                        </div>
                    </div>
                )}

                {/* ── Column Summary tab ── */}
                {activeTab === "summary" && (
                    <div className="space-y-3">
                        <p className="text-xs font-medium text-muted-foreground">{t.aiColumnSummary}</p>
                        <select
                            className="h-8 w-full rounded-md border bg-background px-3 text-xs"
                            value={summaryColumnId ?? ""}
                            onChange={(e) => {
                                const colId = e.target.value;
                                setSummaryColumnId(colId);
                                if (colId) ai.fetchColumnSummary(colId);
                            }}
                        >
                            <option value="">{t.aiSelectColumn}</option>
                            {columns.map(col => (
                                <option key={col.id} value={col.id}>{col.label}</option>
                            ))}
                        </select>

                        {ai.loadingColumnSummary && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <Loader2 className="h-5 w-5 animate-spin text-muted-foreground" />
                                <p className="text-xs text-muted-foreground">{t.aiColumnSummaryLoading}</p>
                            </div>
                        )}

                        {!summaryColumnId && !ai.columnSummary && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <BarChart3 className="h-8 w-8 text-muted-foreground/30" />
                                <p className="text-xs text-muted-foreground">{t.aiSelectColumn}</p>
                            </div>
                        )}

                        {ai.columnSummary && (
                            <div className="space-y-3">
                                <div className="rounded-lg border p-3">
                                    <p className="text-xs leading-relaxed">{ai.columnSummary.summary}</p>
                                </div>
                                {ai.columnSummary.highlights.length > 0 && (
                                    <div className="rounded-lg border p-3">
                                        <p className="mb-2 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Highlights</p>
                                        <ul className="space-y-1.5">
                                            {ai.columnSummary.highlights.map((h, i) => (
                                                <li key={i} className="flex items-start gap-2 text-xs">
                                                    <span className="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-primary" />
                                                    <span className="leading-relaxed">{h}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                                {ai.columnSummary.suggestion && (
                                    <div className="flex items-start gap-2 rounded-lg border border-primary/20 bg-primary/5 p-3">
                                        <Lightbulb className="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary" />
                                        <p className="text-xs leading-relaxed">{ai.columnSummary.suggestion}</p>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                )}

                {/* ── Enrich tab ── */}
                {activeTab === "enrich" && (
                    <div className="space-y-3">
                        <p className="text-xs text-muted-foreground">
                            {t.aiEnrichDescription(selectedRowIds.length)}
                        </p>

                        <div className="space-y-2">
                            <label className="text-[11px] font-medium text-muted-foreground">{t.aiEnrichColumnName}</label>
                            <Input
                                placeholder={t.aiEnrichColumnName}
                                value={enrichColName}
                                onChange={(e) => setEnrichColName(e.target.value)}
                                className="h-8 text-xs"
                            />
                        </div>

                        <div className="space-y-2">
                            <label className="text-[11px] font-medium text-muted-foreground">{t.aiEnrichPrompt}</label>
                            <Input
                                placeholder={t.aiEnrichPrompt}
                                value={enrichPrompt}
                                onChange={(e) => setEnrichPrompt(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === "Enter" && enrichPrompt.trim() && enrichColName.trim()) {
                                        ai.enrichRows(enrichPrompt, enrichColName, selectedRowIds);
                                    }
                                }}
                                className="h-8 text-xs"
                            />
                        </div>

                        <Button
                            size="sm" className="h-8 text-xs"
                            disabled={!enrichPrompt.trim() || !enrichColName.trim() || ai.loadingEnrich}
                            onClick={() => ai.enrichRows(enrichPrompt, enrichColName, selectedRowIds)}
                        >
                            {ai.loadingEnrich ? (
                                <><Loader2 className="mr-1.5 h-3 w-3 animate-spin" />{t.aiEnrichLoading}</>
                            ) : (
                                <><Sparkles className="mr-1.5 h-3 w-3" />{t.aiEnrich}</>
                            )}
                        </Button>

                        {ai.enrichResult && (
                            <div className="rounded-lg border">
                                <div className="border-b px-3 py-2">
                                    <p className="text-xs font-semibold">{t.aiEnrichSuccess(Object.keys(ai.enrichResult.enrichments).length)}</p>
                                </div>
                                <div className="max-h-40 overflow-y-auto">
                                    <table className="w-full text-xs">
                                        <thead className="sticky top-0 bg-muted/80 backdrop-blur">
                                            <tr>
                                                <th className="px-3 py-1.5 text-left text-[11px] font-medium text-muted-foreground">ID</th>
                                                <th className="px-3 py-1.5 text-left text-[11px] font-medium text-muted-foreground">{ai.enrichResult.column_name}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {Object.entries(ai.enrichResult.enrichments).map(([id, val]) => (
                                                <tr key={id} className="border-t">
                                                    <td className="px-3 py-1.5 font-mono text-muted-foreground">{id}</td>
                                                    <td className="px-3 py-1.5">{val}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* ── Visualize tab (Thesys C1) ── */}
                {activeTab === "visualize" && hasThesys && (
                    <div className="space-y-3">
                        <p className="text-xs text-muted-foreground">{t.aiVisualize}</p>
                        <div className="flex gap-2">
                            <Input
                                placeholder={t.aiVisualizePrompt}
                                value={vizPrompt}
                                onChange={(e) => setVizPrompt(e.target.value)}
                                onKeyDown={(e) => { if (e.key === "Enter") ai.fetchVisualize(vizPrompt || undefined); }}
                                className="h-8 text-xs"
                            />
                            <Button
                                size="sm" className="h-8 shrink-0 text-xs"
                                disabled={ai.loadingVisualize}
                                onClick={() => ai.fetchVisualize(vizPrompt || undefined)}
                            >
                                {ai.loadingVisualize ? (
                                    <><Loader2 className="mr-1.5 h-3 w-3 animate-spin" />{t.aiVisualizeLoading}</>
                                ) : (
                                    <><BarChart3 className="mr-1.5 h-3 w-3" />{t.aiVisualizeGenerate}</>
                                )}
                            </Button>
                        </div>

                        {!ai.visualizeHtml && !ai.loadingVisualize && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <BarChart3 className="h-8 w-8 text-muted-foreground/30" />
                                <p className="text-xs text-muted-foreground">{t.aiVisualizePrompt}</p>
                            </div>
                        )}

                        {ai.loadingVisualize && !ai.visualizeHtml && (
                            <div className="flex flex-col items-center gap-2 py-8 text-center">
                                <Loader2 className="h-5 w-5 animate-spin text-muted-foreground" />
                                <p className="text-xs text-muted-foreground">{t.aiVisualizeLoading}</p>
                            </div>
                        )}

                        {ai.visualizeHtml && (
                            <div
                                className="rounded-lg border bg-background p-4"
                                dangerouslySetInnerHTML={{ __html: sanitizeHtml(ai.visualizeHtml) }}
                            />
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

// ─── Main DataTable component ───────────────────────────────────────────────

function DataTableInner<TData extends object>({
    className, tableData, tableName, prefix, actions, bulkActions,
    renderCell: renderCellProp, renderHeader: renderHeaderProp,
    renderFooterCell: renderFooterCellProp, renderFilter: renderFilterProp,
    rowClassName, rowDataAttributes, groupClassName,
    options: optionsOverride, translations: translationsOverride,
    onRowClick, rowLink, emptyState, debounceMs, partialReloadKey,
    onInlineEdit, realtimeChannel, realtimeEvent = ".updated",
    renderDetailRow, selectionMode = "checkbox", slots,
    onReorder, onBatchEdit, emptyStateIllustration,
    onStateChange, onRowCreate, mobileBreakpoint = 0, children,
    headerActions, groupByOptions, onGroupByChange,
    rowSpan, columnSpan, onClipboardPaste, onDragToFill,
    onCellRangeSelect, apiRef, onLoadMore, hasMore,
    sparklineData, onAiQuery, onPivotChange,
    kanbanColumnId, onKanbanMove, facetedCounts: facetedCountsProp,
    presenceChannel, currentUser,
    cardImageColumn, cardTitleColumn, cardSubtitleColumn,
    renderMasterDetail, onFindReplace, chartTypes, aiBaseUrl, aiThesys,
}: DataTableProps<TData>) {
    // Handle deferred/lazy loading: show skeleton until data is available
    if (!tableData) {
        return (
            <div className="space-y-3">
                <div className="flex items-center gap-2">
                    <Skeleton className="h-9 w-64" />
                    <Skeleton className="h-9 w-24 ml-auto" />
                    <Skeleton className="h-9 w-24" />
                </div>
                <div className="rounded-md border">
                    <Skeleton className="h-10 w-full rounded-b-none" />
                    {Array.from({ length: 5 }).map((_, i) => (
                        <Skeleton key={i} className="h-12 w-full rounded-none border-t" />
                    ))}
                </div>
                <div className="flex items-center justify-between">
                    <Skeleton className="h-5 w-32" />
                    <Skeleton className="h-9 w-48" />
                </div>
            </div>
        );
    }

    // Extract column configs from JSX children (<DataTable.Column>)
    const jsxColumnConfigs = useMemo(
        () => children ? extractColumnConfigs<TData>(children) : null,
        [children],
    );

    // Merge JSX column overrides with prop-based overrides (props take priority)
    const renderCell = renderCellProp ?? jsxColumnConfigs?.renderCell;
    const renderHeader = renderHeaderProp ?? jsxColumnConfigs?.renderHeader;
    const renderFooterCell = renderFooterCellProp ?? jsxColumnConfigs?.renderFooterCell;
    const renderFilter = renderFilterProp ?? jsxColumnConfigs?.renderFilter;
    const t = useMemo<DataTableTranslations>(() => ({ ...defaultTranslations, ...translationsOverride }), [translationsOverride]);

    const resolvedOptions = useMemo<DataTableOptions>(() => ({
        quickViews: true, customQuickViews: true, exports: true, filters: true,
        columnVisibility: true, columnOrdering: true, columnResizing: false,
        stickyHeader: true, globalSearch: true, loading: true,
        keyboardNavigation: false, printable: false, density: false,
        copyCell: false, contextMenu: false, virtualScrolling: false,
        rowGrouping: false, rowReorder: false, batchEdit: false,
        searchHighlight: false, undoRedo: false, columnPinning: false,
        persistSelection: false, shortcutsOverlay: false,
        exportProgress: false, emptyStateIllustration: false,
        cellFlashing: false, statusBar: false, clipboardPaste: false,
        dragToFill: false, headerFilters: false, infiniteScroll: false,
        columnAutoSize: false, columnVirtualization: false,
        cellRangeSelection: false, autoSizer: false, cellMeasurer: false,
        scrollAwareRendering: false, windowScroller: false,
        directionalOverscan: false,
        layoutSwitcher: false, columnStatistics: false,
        conditionalFormatting: false, facetedFilters: false,
        presence: false, spreadsheetMode: false, kanbanView: false,
        masterDetail: false, integratedCharts: false, findReplace: false,
        ...optionsOverride,
    }), [optionsOverride]);

    const config = tableData.config;
    const hasBulkActions = bulkActions && bulkActions.length > 0;
    const isClickable = !!onRowClick || !!rowLink;
    const hasDetailRows = !!renderDetailRow && (config?.detailRowEnabled ?? false);

    // Responsive column collapse
    const responsiveHiddenCols = useResponsiveColumns(tableData.columns);

    // Inertia loading state
    const [isNavigating, setIsNavigating] = useState(false);
    const isBackgroundReload = useRef(false);
    useEffect(() => {
        if (!resolvedOptions.loading) return;
        const removeStart = router.on("start", (event) => {
            // Don't show skeletons for background reloads (polling, real-time, prefetch)
            const visit = (event as unknown as { detail?: { visit?: { only?: string[] } } })?.detail?.visit;
            const isPartialOnly = visit?.only && visit.only.length > 0;
            if (isPartialOnly) {
                isBackgroundReload.current = true;
                return;
            }
            isBackgroundReload.current = false;
            setIsNavigating(true);
        });
        const removeFinish = router.on("finish", () => {
            isBackgroundReload.current = false;
            setIsNavigating(false);
        });
        return () => {
            removeStart();
            removeFinish();
        };
    }, [resolvedOptions.loading]);

    // Real-time updates via Laravel Echo — uses partial reload scoped to the data prop
    useEffect(() => {
        if (!realtimeChannel) return;
        const Echo = (window as unknown as { Echo?: { channel: (name: string) => { listen: (event: string, cb: (payload?: Record<string, unknown>) => void) => { stopListening: (event: string) => void } } } }).Echo;
        if (!Echo) return;
        const channel = Echo.channel(realtimeChannel);
        const handler = (_payload?: Record<string, unknown>) => {
            // Targeted partial reload — only refreshes the table data prop, not the full page
            router.reload({ only: partialReloadKey ? [partialReloadKey] : undefined });
        };
        channel.listen(realtimeEvent, handler);
        return () => { channel.stopListening(realtimeEvent); };
    }, [realtimeChannel, realtimeEvent, partialReloadKey]);

    // Auto-refresh polling
    useEffect(() => {
        const interval = config?.pollingInterval ?? 0;
        if (interval <= 0) return;
        const timer = setInterval(() => {
            router.reload({ only: partialReloadKey ? [partialReloadKey] : undefined });
        }, interval * 1000);
        return () => clearInterval(timer);
    }, [config?.pollingInterval, partialReloadKey]);

    // Deferred loading
    const [deferLoaded, setDeferLoaded] = useState(!config?.deferLoading);
    useEffect(() => {
        if (config?.deferLoading && !deferLoaded) {
            router.reload({ only: partialReloadKey ? [partialReloadKey] : undefined,
                onSuccess: () => setDeferLoaded(true) });
        }
    }, [config?.deferLoading, deferLoaded, partialReloadKey]);

    const [bulkConfirm, setBulkConfirm] = useState<{ action: (typeof bulkActions extends (infer U)[] ? U : never); opts: DataTableConfirmOptions; rows: TData[] } | null>(null);
    const [serverSelectAll, setServerSelectAll] = useState(false);
    const [serverSelectedIds, setServerSelectedIds] = useState<unknown[]>([]);
    const lastSelectedIndex = useRef<number | null>(null);
    const [focusedRowIndex, setFocusedRowIndex] = useState<number | null>(null);
    const tableBodyRef = useRef<HTMLTableSectionElement>(null);
    const virtualContainerRef = useRef<HTMLDivElement>(null);
    const tableElementRef = useRef<HTMLTableElement>(null);

    // Spreadsheet mode (Tab/Enter navigation)
    useSpreadsheetMode(resolvedOptions.spreadsheetMode, tableElementRef);

    // AutoSizer
    const autoSizerDimensions = useAutoSizer(resolvedOptions.autoSizer, virtualContainerRef);

    // Scroll-aware rendering
    const isScrollingFast = useScrollAwareRendering(resolvedOptions.scrollAwareRendering, virtualContainerRef);

    // CellMeasurer
    const { measureCell, getCellHeight, clearCache: clearMeasureCache } = useCellMeasurer(resolvedOptions.cellMeasurer);

    // Window scroller
    const { windowScrollTop, windowHeight } = useWindowScroller(resolvedOptions.windowScroller);

    // Column virtualization
    const visibleColumnIds = useMemo(() => tableData.columns.filter(c => c.visible !== false).map(c => c.id), [tableData.columns]);
    const { visibleColumnRange } = useColumnVirtualization(resolvedOptions.columnVirtualization, virtualContainerRef, visibleColumnIds.length);

    // Cell range selection
    const cellRange = useCellRangeSelection(resolvedOptions.cellRangeSelection);

    // Horizontal scroll shadow indicators (DOM-based to avoid re-renders)
    const scrollShadowLeftRef = useRef<HTMLDivElement>(null);
    const scrollShadowRightRef = useRef<HTMLDivElement>(null);
    useEffect(() => {
        const el = virtualContainerRef.current;
        if (!el) return;
        const update = () => {
            const { scrollLeft: sl, scrollWidth, clientWidth } = el;
            if (scrollShadowLeftRef.current) scrollShadowLeftRef.current.style.opacity = sl > 0 ? "1" : "0";
            if (scrollShadowRightRef.current) scrollShadowRightRef.current.style.opacity = sl + clientWidth < scrollWidth - 1 ? "1" : "0";
        };
        update();
        el.addEventListener("scroll", update, { passive: true });
        const ro = new ResizeObserver(update);
        ro.observe(el);
        return () => { el.removeEventListener("scroll", update); ro.disconnect(); };
    }, []);

    // Header filter state
    const [headerFilterValues, setHeaderFilterValues] = useState<Record<string, string>>({});
    const [headerFiltersVisible, setHeaderFiltersVisible] = useState(false);

    // Tree data state
    const [expandedTreeNodes, setExpandedTreeNodes] = useState<Set<string>>(new Set());

    // Infinite scroll state
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const infiniteScrollSentinelRef = useRef<HTMLDivElement>(null);

    // AI assistant state
    const [aiQuery, setAiQuery] = useState("");
    const [aiQuerying, setAiQuerying] = useState(false);
    const [aiPanelOpen, setAiPanelOpen] = useState(false);
    const ai = useAiAssistant(aiBaseUrl);

    // Pivot mode state
    const [pivotActive, setPivotActive] = useState(false);

    // Layout mode state (persisted to localStorage)
    const [layoutMode, setLayoutMode] = useState<DataTableLayoutMode>(() => {
        if (!resolvedOptions.layoutSwitcher) return (config?.defaultLayout as DataTableLayoutMode) ?? "table";
        const stored = safeGetItem(`dt-layout-${tableName}`);
        if (stored === "grid" || stored === "cards" || stored === "kanban" || stored === "table") return stored;
        return (config?.defaultLayout as DataTableLayoutMode) ?? "table";
    });
    const handleLayoutChange = useCallback((mode: DataTableLayoutMode) => {
        setLayoutMode(mode);
        safeSetItem(`dt-layout-${tableName}`, mode);
    }, [tableName]);

    // Conditional formatting rules
    const condFormat = useConditionalFormatRules(tableName, resolvedOptions.conditionalFormatting);
    const [condFormatOpen, setCondFormatOpen] = useState(false);

    // Presence
    const presenceUsers = usePresence(
        resolvedOptions.presence ? presenceChannel : undefined,
        resolvedOptions.presence ? currentUser : undefined,
    );

    // Master/Detail expanded rows (separate from regular detail rows)
    const [masterDetailExpanded, setMasterDetailExpanded] = useState<Set<string | number>>(new Set());
    const hasMasterDetail = resolvedOptions.masterDetail && !!renderMasterDetail;
    const toggleMasterDetail = useCallback((rowId: string | number) => {
        setMasterDetailExpanded(prev => {
            const next = new Set(prev);
            if (next.has(rowId)) next.delete(rowId); else next.add(rowId);
            return next;
        });
    }, []);

    // Integrated Charts
    const [chartState, setChartState] = useState<IntegratedChartState | null>(null);
    const resolvedChartTypes = chartTypes ?? (["bar", "line", "pie", "doughnut"] as ChartKind[]);
    // Find & Replace
    const [findReplaceOpen, setFindReplaceOpen] = useState(false);

    // Ctrl+F keyboard shortcut for Find & Replace
    useEffect(() => {
        if (!resolvedOptions.findReplace) return;
        const handler = (e: KeyboardEvent) => {
            if ((e.ctrlKey || e.metaKey) && e.key === "f") {
                e.preventDefault();
                setFindReplaceOpen(true);
            }
        };
        document.addEventListener("keydown", handler);
        return () => document.removeEventListener("keydown", handler);
    }, [resolvedOptions.findReplace]);

    // Faceted counts: merge from prop and server response
    const facetedCounts = facetedCountsProp ?? tableData.facetedCounts ?? null;

    // Header filter handler
    const handleHeaderFilterChange = useCallback((columnId: string, value: string) => {
        setHeaderFilterValues(prev => ({ ...prev, [columnId]: value }));
        // Apply filter via URL
        const url = new URL(window.location.href);
        const p = prefix ? `${prefix}_` : "";
        if (value) {
            url.searchParams.set(`${p}filter[${columnId}]`, `contains:${value}`);
        } else {
            url.searchParams.delete(`${p}filter[${columnId}]`);
        }
        url.searchParams.set(`${p}page`, "1");
        router.visit(url.toString(), { preserveState: true, preserveScroll: true, only: partialReloadKey ? [partialReloadKey] : undefined });
    }, [prefix, partialReloadKey]);

    // AI assistant handler (supports both onAiQuery prop and built-in aiBaseUrl)
    const handleAiQuery = useCallback(async () => {
        if (!aiQuery.trim()) return;
        if (!onAiQuery && !aiBaseUrl) return;
        setAiQuerying(true);
        try {
            let result: { filters?: Record<string, unknown>; sort?: string } | void;
            if (onAiQuery) {
                result = await onAiQuery(aiQuery.trim());
            } else if (aiBaseUrl) {
                result = await ai.queryNlp(aiQuery.trim());
            }
            if (result) {
                const url = new URL(window.location.href);
                const p = prefix ? `${prefix}_` : "";
                if (result.filters) {
                    Object.entries(result.filters).forEach(([key, value]) => {
                        url.searchParams.set(`${p}filter[${key}]`, String(value));
                    });
                }
                if (result.sort) {
                    url.searchParams.set(`${p}sort`, result.sort);
                }
                router.visit(url.toString(), { preserveState: true });
            }
            setAiQuery("");
        } finally {
            setAiQuerying(false);
        }
    }, [onAiQuery, aiBaseUrl, ai, aiQuery, prefix]);

    // Apply AI action (from insights/suggestions panel)
    const handleAiApplyAction = useCallback((action: { filters?: Record<string, unknown>; sort?: string }) => {
        const url = new URL(window.location.href);
        const p = prefix ? `${prefix}_` : "";
        if (action.filters) {
            Object.entries(action.filters).forEach(([key, value]) => {
                url.searchParams.set(`${p}filter[${key}]`, String(value));
            });
        }
        if (action.sort) {
            url.searchParams.set(`${p}sort`, action.sort);
        }
        url.searchParams.set(`${p}page`, "1");
        router.visit(url.toString(), { preserveState: true });
    }, [prefix]);

    // Tree data: build hierarchy from flat data
    const treeConfig = config;
    const [expandedRows, setExpandedRows] = useState<Set<string>>(new Set());
    const [showTrashed, setShowTrashed] = useState(false);

    // Detail modal/drawer state
    const detailDisplay = config?.detailDisplay ?? "inline";
    const [detailRow, setDetailRow] = useState<TData | null>(null);

    // Density toggle
    const [density, setDensity] = useState<DataTableDensity>(() => loadDensity(tableName));
    const handleDensityChange = useCallback((d: DataTableDensity) => { setDensity(d); saveDensity(tableName, d); }, [tableName]);
    const densityClasses = DENSITY_CLASSES[density];

    // Batch edit
    const [batchEditOpen, setBatchEditOpen] = useState(false);

    // Import dialog
    const [importDialogOpen, setImportDialogOpen] = useState(false);

    // Row drag reorder
    const [dragRowIndex, setDragRowIndex] = useState<number | null>(null);
    const [dragOverRowIndex, setDragOverRowIndex] = useState<number | null>(null);
    const handleRowDragStart = useCallback((index: number) => { setDragRowIndex(index); }, []);
    const handleRowDragOver = useCallback((_e: React.DragEvent, index: number) => { setDragOverRowIndex(index); }, []);
    const handleRowDragEnd = useCallback(() => {
        if (dragRowIndex !== null && dragOverRowIndex !== null && dragRowIndex !== dragOverRowIndex && onReorder) {
            const rows = tableData.data;
            const ids = rows.map((r) => (r as Record<string, unknown>).id);
            const reordered = [...ids];
            const [moved] = reordered.splice(dragRowIndex, 1);
            reordered.splice(dragOverRowIndex, 0, moved);
            onReorder(reordered, reordered.map((_, i) => i));
        }
        setDragRowIndex(null);
        setDragOverRowIndex(null);
    }, [dragRowIndex, dragOverRowIndex, tableData.data, onReorder]);

    // Collapsed row groups
    const [collapsedGroups, setCollapsedGroups] = useState<Set<string>>(new Set());

    // Search term for highlighting
    const searchKeyForHighlight = prefix ? `${prefix}_search` : "search";
    const currentSearchTerm = typeof window !== "undefined" ? new URL(window.location.href).searchParams.get(searchKeyForHighlight) ?? "" : "";

    // Undo/Redo stack for inline edits
    const { pushEdit, undo: undoEdit, redo: redoEdit, canUndo, canRedo } = useUndoRedo(resolvedOptions.undoRedo);

    // Keyboard shortcuts overlay
    const [shortcutsOpen, setShortcutsOpen] = useState(false);

    // Cell flashing — detect value changes from polling/realtime
    const flashingCells = useCellFlashing(resolvedOptions.cellFlashing, tableData.data as unknown[], tableData.columns);

    // Drag-to-fill state
    const [dragFillState, setDragFillState] = useState<{ columnId: string; startRowIndex: number; value: unknown } | null>(null);
    const [dragFillEndIndex, setDragFillEndIndex] = useState<number | null>(null);

    const handleDragFillStart = useCallback((columnId: string, rowIndex: number, value: unknown) => {
        setDragFillState({ columnId, startRowIndex: rowIndex, value });
    }, []);

    const handleDragFillOver = useCallback((_e: React.DragEvent, rowIndex: number) => {
        if (dragFillState) setDragFillEndIndex(rowIndex);
    }, [dragFillState]);

    const handleDragFillEnd = useCallback(() => {
        if (dragFillState && dragFillEndIndex !== null && onDragToFill) {
            const start = Math.min(dragFillState.startRowIndex, dragFillEndIndex);
            const end = Math.max(dragFillState.startRowIndex, dragFillEndIndex);
            const targetIds: unknown[] = [];
            for (let i = start; i <= end; i++) {
                if (i !== dragFillState.startRowIndex) {
                    const row = tableData.data[i] as Record<string, unknown> | undefined;
                    if (row) targetIds.push(row.id ?? i);
                }
            }
            if (targetIds.length > 0) onDragToFill(dragFillState.columnId, dragFillState.value, targetIds);
        }
        setDragFillState(null);
        setDragFillEndIndex(null);
    }, [dragFillState, dragFillEndIndex, onDragToFill, tableData.data]);

    // Server-driven action rules helper — matches by action.id first, then falls back to action.label
    const checkActionRule = useCallback((actionId: string | undefined, actionLabel: string, row: Record<string, unknown>): boolean => {
        const rules = tableData.actionRules;
        if (!rules) return true;
        const rule = (actionId && rules[actionId]) ? rules[actionId] : rules[actionLabel];
        if (!rule) return true;
        const cellValue = row[rule.column];
        switch (rule.operator) {
            case "eq": return cellValue === rule.value;
            case "neq": return cellValue !== rule.value;
            case "gt": return Number(cellValue) > Number(rule.value);
            case "gte": return Number(cellValue) >= Number(rule.value);
            case "lt": return Number(cellValue) < Number(rule.value);
            case "lte": return Number(cellValue) <= Number(rule.value);
            case "in": return Array.isArray(rule.value) && rule.value.includes(cellValue);
            case "notIn": return Array.isArray(rule.value) && !rule.value.includes(cellValue);
            default: return true;
        }
    }, [tableData.actionRules]);

    // User-selectable grouping
    const [userGroupBy, setUserGroupBy] = useState<string | null>(null);
    const handleGroupByChange = useCallback((colId: string | null) => {
        setUserGroupBy(colId);
        onGroupByChange?.(colId);
    }, [onGroupByChange]);

    // Form action dialog
    const [formAction, setFormAction] = useState<{ action: import("./types").DataTableAction<TData>; row: TData } | null>(null);

    // Selection persistence across pages
    const { persistedIds, addIds: addPersistedIds, removeIds: removePersistedIds, clearAll: clearPersistedSelection, count: persistedSelectionCount } = usePersistedSelection<TData>(tableName, resolvedOptions.persistSelection);

    const RESIZE_KEY = `dt-resize-${tableName}`;
    const [columnSizing, setColumnSizing] = useState<Record<string, number>>(() => {
        if (!resolvedOptions.columnResizing) return {};
        const stored = safeGetItem(RESIZE_KEY);
        if (stored) { try { return JSON.parse(stored); } catch { /* fall through */ } }
        return {};
    });

    useEffect(() => {
        if (resolvedOptions.columnResizing && Object.keys(columnSizing).length > 0)
            safeSetItem(RESIZE_KEY, JSON.stringify(columnSizing));
    }, [columnSizing, resolvedOptions.columnResizing, RESIZE_KEY]);

    // Persist state to localStorage
    const STATE_KEY = `dt-state-${tableName}`;
    useEffect(() => {
        if (!config?.persistState) return;
        safeSetItem(STATE_KEY, JSON.stringify({
            filters: tableData.meta.filters, sorts: tableData.meta.sorts, perPage: tableData.meta.perPage,
        }));
    }, [config?.persistState, tableData.meta.filters, tableData.meta.sorts, tableData.meta.perPage, STATE_KEY]);

    // Merge enum options into columns
    const mergedColumns = useMemo(() => {
        if (!tableData.enumOptions) return tableData.columns;
        return tableData.columns.map((col) => {
            const enumOpts = tableData.enumOptions?.[col.id];
            if (!enumOpts) return col;
            // Merge enum options with original options to preserve variants/extra fields
            const originalOptions = col.options ?? [];
            const merged = enumOpts.map((eo) => {
                const original = originalOptions.find((o) => o.value === eo.value);
                return original ? { ...original, ...eo } : eo;
            });
            return { ...col, options: merged };
        });
    }, [tableData.columns, tableData.enumOptions]);

    const numericCols = useMemo(() => mergedColumns.filter(c => c.type === "number" || c.type === "currency" || c.type === "percentage"), [mergedColumns]);
    const findReplace = useFindReplace(resolvedOptions.findReplace && findReplaceOpen, tableData.data as Record<string, unknown>[], mergedColumns);

    const openChart = useCallback(() => {
        const firstNumCol = numericCols[0];
        if (firstNumCol) setChartState({ columnId: firstNumCol.id, chartType: "bar" });
    }, [numericCols]);

    // Find & Replace highlight set for cell rendering
    const findReplaceHighlights = useMemo<Set<string>>(() => {
        if (!findReplaceOpen || findReplace.matches.length === 0) return new Set();
        return new Set(findReplace.matches.map(m => `${m.rowIndex}:${m.columnId}`));
    }, [findReplaceOpen, findReplace.matches]);

    const findReplaceCurrentKey = findReplace.matches[findReplace.currentIndex]
        ? `${findReplace.matches[findReplace.currentIndex].rowIndex}:${findReplace.matches[findReplace.currentIndex].columnId}`
        : null;

    const handleFindReplace = useCallback((match: FindReplaceMatch, newValue: string) => {
        if (onFindReplace) {
            const row = tableData.data[match.rowIndex] as Record<string, unknown>;
            const oldValue = row[match.columnId];
            const replaced = String(oldValue ?? "").replace(
                findReplace.caseSensitive ? match.value : new RegExp(findReplace.query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "i"),
                newValue
            );
            onFindReplace(match.rowId, match.columnId, oldValue, replaced);
            showToast(t.replaceSuccess(1), "success");
        }
    }, [onFindReplace, tableData.data, findReplace.caseSensitive, findReplace.query, t]);

    const handleFindReplaceAll = useCallback((matches: FindReplaceMatch[], replacement: string) => {
        if (onFindReplace) {
            for (const match of matches) {
                const row = tableData.data[match.rowIndex] as Record<string, unknown>;
                const oldValue = row[match.columnId];
                const replaced = String(oldValue ?? "").replace(
                    findReplace.caseSensitive
                        ? new RegExp(findReplace.query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "g")
                        : new RegExp(findReplace.query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "gi"),
                    replacement
                );
                onFindReplace(match.rowId, match.columnId, oldValue, replaced);
            }
            showToast(t.replaceSuccess(matches.length), "success");
        }
    }, [onFindReplace, tableData.data, findReplace.caseSensitive, findReplace.query, t]);

    const columnDefs = useMemo<ColumnDef<TData>[]>(() => {
        function makeLeafCol(col: DataTableColumnDef): ColumnDef<TData> {
            return {
                id: col.id, accessorKey: col.id, header: col.label, enableHiding: true,
                enableResizing: resolvedOptions.columnResizing, size: columnSizing[col.id] || undefined,
                meta: { type: col.type, group: col.group ?? null, editable: col.editable, currency: col.currency, currencyColumn: col.currencyColumn, locale: col.locale, toggleable: col.toggleable, prefix: col.prefix, suffix: col.suffix, tooltip: col.tooltip, description: col.description, lineClamp: col.lineClamp, iconMap: col.iconMap, colorMap: col.colorMap, selectOptions: col.selectOptions, html: col.html, markdown: col.markdown, bulleted: col.bulleted, stacked: col.stacked, rowIndex: col.rowIndex, avatarColumn: col.avatarColumn, hasDynamicSuffix: col.hasDynamicSuffix, computedFrom: col.computedFrom, colSpan: col.colSpan, autoHeight: col.autoHeight, valueGetter: col.valueGetter, valueFormatter: col.valueFormatter, headerFilter: col.headerFilter, sparkline: col.sparkline, treeParent: col.treeParent } satisfies ColumnMeta,
                cell: ({ row }) => {
                    // valueGetter: derive value from another column or dot-path
                    let value = row.getValue(col.id);
                    const rowData = row.original as Record<string, unknown>;
                    if (col.valueGetter) {
                        const parts = col.valueGetter.split(".");
                        let resolved: unknown = rowData;
                        for (const part of parts) {
                            if (resolved && typeof resolved === "object") resolved = (resolved as Record<string, unknown>)[part];
                            else { resolved = undefined; break; }
                        }
                        if (resolved !== undefined) value = resolved;
                    }

                    // Sparkline column — supports both array (by row index) and object (by row ID) formats
                    if (col.sparkline && sparklineData?.[col.id]) {
                        const colSparkData = sparklineData[col.id];
                        const sparkData = Array.isArray(colSparkData)
                            ? colSparkData[row.index]
                            : colSparkData[(rowData as Record<string, unknown>).id as string | number];
                        if (sparkData) return <SparklineChart data={sparkData} type={col.sparkline as "line" | "bar"} />;
                    }

                    // Row index column
                    if (col.rowIndex) {
                        const pageOffset = ((tableData.meta?.currentPage ?? 1) - 1) * (tableData.meta?.perPage ?? 0);
                        return <span className="text-muted-foreground tabular-nums">{pageOffset + row.index + 1}</span>;
                    }

                    // Stacked/composite columns
                    if (col.stacked && col.stacked.length > 0) {
                        return (
                            <div className="flex flex-col gap-0.5">
                                {col.stacked.map((stackedId) => {
                                    const stackedValue = rowData[stackedId];
                                    return <span key={stackedId} className="text-sm first:font-medium [&:not(:first-child)]:text-xs [&:not(:first-child)]:text-muted-foreground">{stackedValue != null ? String(stackedValue) : "—"}</span>;
                                })}
                            </div>
                        );
                    }

                    // Avatar composite cell: avatar image + text name
                    if (col.avatarColumn) {
                        const avatarUrl = rowData[col.avatarColumn];
                        const displayValue = value != null ? String(value) : "—";
                        return (
                            <div className="flex items-center gap-2.5">
                                {avatarUrl ? (
                                    <img src={String(avatarUrl)} alt={displayValue} className="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-border/50" />
                                ) : (
                                    <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground ring-1 ring-border/50" role="img" aria-label={displayValue}>{displayValue.charAt(0).toUpperCase()}</span>
                                )}
                                <span className="truncate font-medium">{displayValue}</span>
                            </div>
                        );
                    }

                    // Boolean toggle switch
                    if (col.toggleable && tableData.toggleUrl) {
                        return <ToggleCell value={!!value} row={rowData}
                            columnId={col.id} toggleUrl={tableData.toggleUrl} />;
                    }

                    // Inline multi-select dropdown for multiOption columns
                    if (col.type === "multiOption" && col.options && col.editable && onInlineEdit) {
                        const arrValue = Array.isArray(value) ? value.map(String) : [];
                        return <MultiSelectCell value={arrValue} options={col.options} onSave={(newVal) => {
                            pushEdit({ rowId: rowData.id, columnId: col.id, oldValue: value, newValue: newVal });
                            return onInlineEdit(row.original, col.id, newVal);
                        }} />;
                    }

                    // Inline select dropdown
                    if (col.type === "select" && col.selectOptions && onInlineEdit) {
                        return <SelectCell value={String(value ?? "")} options={col.selectOptions} onSave={(newVal) => {
                            pushEdit({ rowId: rowData.id, columnId: col.id, oldValue: value, newValue: newVal });
                            return onInlineEdit(row.original, col.id, newVal);
                        }} />;
                    }

                    // Inline editing with undo/redo support
                    if (col.editable && onInlineEdit) {
                        return <InlineEditCell value={value} columnId={col.id} columnType={col.type}
                            onSave={(newVal) => {
                                const rowId = rowData.id;
                                pushEdit({ rowId, columnId: col.id, oldValue: value, newValue: newVal });
                                return onInlineEdit(row.original, col.id, newVal);
                            }} t={t} />;
                    }

                    if (renderCell) { const custom = renderCell(col.id, value, row.original); if (custom !== undefined) return custom; }
                    if (value === null || value === undefined) return <span className="text-muted-foreground">—</span>;

                    // Wrap helper for prefix/suffix/tooltip/lineClamp/colorMap
                    const wrapCell = (content: React.ReactNode) => {
                        let wrapped = content;
                        // Prefix/suffix (supports dynamic server-resolved suffixes)
                        const resolvedSuffix = col.hasDynamicSuffix ? (rowData[`_suffix_${col.id}`] as string | null) : col.suffix;
                        if (col.prefix || resolvedSuffix) {
                            wrapped = <span>{col.prefix}{wrapped}{resolvedSuffix}</span>;
                        }
                        // Color map
                        if (col.colorMap) {
                            const colorClass = col.colorMap[String(value)] ?? null;
                            if (colorClass) wrapped = <span className={colorClass}>{wrapped}</span>;
                        }
                        // Line clamp
                        if (col.lineClamp) {
                            wrapped = <span className="block overflow-hidden" style={{ display: "-webkit-box", WebkitLineClamp: col.lineClamp, WebkitBoxOrient: "vertical" }}>{wrapped}</span>;
                        }
                        // Tooltip
                        if (col.tooltip) {
                            const tooltipText = rowData[col.tooltip] != null ? String(rowData[col.tooltip]) : col.tooltip;
                            wrapped = <span title={tooltipText}>{wrapped}</span>;
                        }
                        return wrapped;
                    };

                    // Icon column
                    if (col.type === "icon" && col.iconMap) {
                        const iconName = col.iconMap[String(value)] ?? String(value);
                        return wrapCell(<span className="inline-flex items-center gap-1.5"><span className="text-sm">{iconName}</span></span>);
                    }

                    // Color swatch column
                    if (col.type === "color" && typeof value === "string") {
                        return wrapCell(
                            <div className="flex items-center gap-2">
                                <span className="inline-block h-5 w-5 rounded border" style={{ backgroundColor: value }} />
                                <span className="text-xs text-muted-foreground font-mono">{value}</span>
                            </div>
                        );
                    }

                    if (col.type === "image" && typeof value === "string") {
                        return <img src={value} alt={col.label} className="h-8 w-8 rounded-full object-cover ring-1 ring-border/50" />;
                    }
                    if (col.type === "badge") {
                        const strValue = String(value);
                        const opt = col.options?.find((o) => o.value === strValue);
                        return wrapCell(<span className={cn("inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium",
                            BADGE_VARIANTS[opt?.variant ?? "default"] ?? BADGE_VARIANTS.default)}>{opt?.label ?? strValue}</span>);
                    }
                    if (col.type === "currency" && (typeof value === "number" || typeof value === "string")) {
                        const numValue = typeof value === "string" ? parseFloat(value) : value;
                        if (!isNaN(numValue)) {
                            // Per-row currency via currencyColumn, or static currency, or default USD
                            const rowCurrency = col.currencyColumn
                                ? String((rowData as Record<string, unknown>)[col.currencyColumn] ?? col.currency ?? "USD")
                                : (col.currency ?? "USD");
                            try { return wrapCell(<span className="tabular-nums">{numValue.toLocaleString(col.locale ?? undefined, { style: "currency", currency: rowCurrency })}</span>); }
                            catch { return wrapCell(<span className="tabular-nums">{numValue.toLocaleString()}</span>); }
                        }
                    }
                    if (col.type === "percentage" && (typeof value === "number" || typeof value === "string")) {
                        const numValue = typeof value === "string" ? parseFloat(value) : value;
                        if (!isNaN(numValue)) return wrapCell(<span className="tabular-nums">{numValue.toLocaleString(col.locale ?? undefined, { style: "percent", minimumFractionDigits: 0, maximumFractionDigits: 2 })}</span>);
                    }
                    if (col.type === "link" && typeof value === "string") {
                        return wrapCell(<a href={value} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1 text-primary hover:underline" onClick={(e) => e.stopPropagation()}>
                            <span className="max-w-[200px] truncate">{value.replace(/^https?:\/\//, "")}</span><ExternalLink className="h-3 w-3 shrink-0" /></a>);
                    }
                    if (col.type === "email" && typeof value === "string") {
                        return wrapCell(<a href={`mailto:${value}`} className="text-primary hover:underline" onClick={(e) => e.stopPropagation()}>{value}</a>);
                    }
                    if (col.type === "phone" && typeof value === "string") {
                        return wrapCell(<a href={`tel:${value}`} className="text-primary hover:underline" onClick={(e) => e.stopPropagation()}>{value}</a>);
                    }
                    if (typeof value === "boolean") {
                        return value ? <Check className="h-4 w-4 text-emerald-600" />
                            : <X className="h-4 w-4 text-muted-foreground/40" />;
                    }

                    // HTML rendering (sanitized)
                    if (col.html && typeof value === "string") {
                        return wrapCell(<span dangerouslySetInnerHTML={{ __html: sanitizeHtml(value) }} />);
                    }

                    // Markdown rendering (simplified — bold, italic, code, links — sanitized)
                    if (col.markdown && typeof value === "string") {
                        const rendered = value
                            .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
                            .replace(/\*(.+?)\*/g, "<em>$1</em>")
                            .replace(/`(.+?)`/g, "<code class='rounded bg-muted px-1 py-0.5 text-xs'>$1</code>")
                            .replace(/\[(.+?)\]\((.+?)\)/g, "<a href='$2' class='text-primary hover:underline'>$1</a>");
                        return wrapCell(<span dangerouslySetInnerHTML={{ __html: sanitizeHtml(rendered) }} />);
                    }

                    // Bulleted list
                    if (col.bulleted && Array.isArray(value)) {
                        return wrapCell(
                            <ul className="list-disc list-inside space-y-0.5 text-sm">
                                {(value as unknown[]).map((item, i) => <li key={i}>{String(item)}</li>)}
                            </ul>
                        );
                    }

                    if (col.type === "date" && typeof value === "string" && value) {
                        const d = new Date(value);
                        if (!isNaN(d.getTime())) {
                            const now = new Date();
                            const diffDays = Math.floor((now.getTime() - d.getTime()) / 86400000);
                            let display: string;
                            if (diffDays === 0) display = "Today";
                            else if (diffDays === 1) display = "Yesterday";
                            else if (diffDays < 7) display = `${diffDays}d ago`;
                            else display = d.toLocaleDateString(col.locale ?? undefined, {
                                month: "short", day: "numeric",
                                ...(d.getFullYear() !== now.getFullYear() ? { year: "numeric" } : {}),
                            });
                            return wrapCell(<span className="tabular-nums text-muted-foreground" title={value}>{display}</span>);
                        }
                    }

                    if (col.type === "number" && typeof value === "number") return wrapCell(<span className="tabular-nums">{value.toLocaleString()}</span>);

                    // valueFormatter: apply format string (e.g., '{value} USD') or JS expression (e.g., '(value, row) => ...')
                    if (col.valueFormatter) {
                        let formatted: string;
                        if (col.valueFormatter.trim().startsWith("(")) {
                            try {
                                const fn = new Function("return " + col.valueFormatter)();
                                formatted = String(fn(value, rowData));
                            } catch {
                                formatted = col.valueFormatter.replace(/\{value\}/g, String(value));
                            }
                        } else {
                            formatted = col.valueFormatter.replace(/\{value\}/g, String(value));
                        }
                        return wrapCell(formatted);
                    }

                    // Search highlighting for text values
                    const strValue = String(value);
                    if (resolvedOptions.searchHighlight && currentSearchTerm && col.type === "text") {
                        return wrapCell(highlightText(strValue, currentSearchTerm));
                    }
                    return wrapCell(strValue);
                },
            };
        }

        const result: ColumnDef<TData>[] = [];

        // Row reorder drag handle column
        if (resolvedOptions.rowReorder && onReorder) {
            result.push({
                id: "_reorder", header: "", enableHiding: false, enableResizing: false, size: 36,
                cell: ({ row }) => (
                    <DragHandleCell rowIndex={row.index}
                        onDragStart={handleRowDragStart} onDragOver={handleRowDragOver} onDragEnd={handleRowDragEnd} />
                ),
            });
        }

        // Detail row expand column (supports inline, modal, and drawer modes)
        if (hasDetailRows) {
            result.push({
                id: "_expand", header: "", enableHiding: false, enableResizing: false, size: 36,
                cell: ({ row }) => {
                    const rowId = String((row.original as Record<string, unknown>).id ?? row.index);
                    const isExpanded = expandedRows.has(rowId);

                    if (detailDisplay === "modal" || detailDisplay === "drawer") {
                        return (
                            <Button variant="ghost" size="icon" className="h-6 w-6"
                                onClick={(e) => { e.stopPropagation(); setDetailRow(row.original); }}
                                aria-label={t.expand}>
                                {detailDisplay === "drawer" ? <PanelRight className="h-4 w-4" /> : <Expand className="h-4 w-4" />}
                            </Button>
                        );
                    }

                    return (
                        <Button variant="ghost" size="icon" className="h-6 w-6"
                            onClick={(e) => { e.stopPropagation(); setExpandedRows((prev) => {
                                const next = new Set(prev); if (next.has(rowId)) next.delete(rowId); else next.add(rowId); return next;
                            }); }} aria-label={isExpanded ? t.collapse : t.expand}>
                            {isExpanded ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                        </Button>
                    );
                },
            });
        }

        // Master/Detail expand column
        if (hasMasterDetail) {
            result.push({
                id: "_masterDetail", header: "", enableHiding: false, enableResizing: false, size: 36,
                cell: ({ row }) => {
                    const rowId = String((row.original as Record<string, unknown>).id ?? row.index);
                    const isMdExpanded = masterDetailExpanded.has(rowId);
                    return (
                        <Button variant="ghost" size="icon" className="h-6 w-6"
                            onClick={(e) => { e.stopPropagation(); toggleMasterDetail(rowId); }}
                            aria-label={isMdExpanded ? t.masterDetailCollapse : t.masterDetailExpand}>
                            {isMdExpanded ? <ChevronDown className="h-4 w-4 text-primary" /> : <ChevronRight className="h-4 w-4" />}
                        </Button>
                    );
                },
            });
        }

        // Selection column (checkbox or radio)
        if (hasBulkActions || selectionMode === "radio") {
            if (selectionMode === "radio") {
                result.push({
                    id: "_select", header: "", enableHiding: false, enableResizing: false, size: 40,
                    cell: ({ row, table: tbl }) => (
                        <input type="radio" name={`dt-radio-${tableName}`} checked={row.getIsSelected()}
                            onChange={() => { tbl.toggleAllRowsSelected(false); row.toggleSelected(true); }}
                            className="h-4 w-4 accent-primary" aria-label={t.selectRow} />
                    ),
                });
            } else {
                result.push({
                    id: "_select", enableHiding: false, enableResizing: false, size: 40,
                    header: ({ table: tbl }) => (
                        <Checkbox checked={tbl.getIsAllPageRowsSelected() || (tbl.getIsSomePageRowsSelected() && "indeterminate")}
                            onCheckedChange={(value) => tbl.toggleAllPageRowsSelected(!!value)} aria-label={t.selectAll} />
                    ),
                    cell: ({ row }) => (
                        <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} aria-label={t.selectRow} />
                    ),
                });
            }
        }

        const processedGroups = new Set<string>();
        for (const col of mergedColumns) {
            if (responsiveHiddenCols.has(col.id)) continue;
            if (!col.group) { result.push(makeLeafCol(col)); }
            else if (!processedGroups.has(col.group)) {
                processedGroups.add(col.group);
                const groupCols = mergedColumns.filter((c) => c.group === col.group && !responsiveHiddenCols.has(c.id));
                result.push({ id: `_group_${col.group}`, header: col.group, columns: groupCols.map(makeLeafCol) });
            }
        }

        if (actions && actions.length > 0) {
            result.push({ id: "_actions", header: "", enableHiding: false, enableResizing: false, size: 48,
                cell: ({ row }) => {
                    // Apply server-driven action visibility rules
                    const filteredActions = tableData.actionRules
                        ? actions.filter((a) => checkActionRule(a.id, a.label, row.original as Record<string, unknown>))
                        : actions;
                    return filteredActions.length > 0 ? (
                        <div className={cn("opacity-0 group-hover/row:opacity-100 focus-within:opacity-100 transition-opacity", row.getIsSelected() && "opacity-100")}>
                            <DataTableRowActions row={row.original} actions={filteredActions} t={t}
                                onFormAction={(action, r) => setFormAction({ action, row: r })} />
                        </div>
                    ) : null;
                } });
        }

        return result;
    }, [mergedColumns, actions, hasBulkActions, renderCell, t, onInlineEdit, resolvedOptions.columnResizing, resolvedOptions.rowReorder, resolvedOptions.searchHighlight, resolvedOptions.copyCell, columnSizing, hasDetailRows, expandedRows, tableName, selectionMode, responsiveHiddenCols, tableData.toggleUrl, onReorder, handleRowDragStart, handleRowDragOver, handleRowDragEnd, currentSearchTerm, hasMasterDetail, masterDetailExpanded, toggleMasterDetail]);

    const { table, meta, columnVisibility, columnOrder, setColumnOrder, rowSelection, setRowSelection,
        applyColumns, handleSort, handlePageChange, handlePerPageChange, handleCursorChange,
        handleGlobalSearch, handleApplyQuickView, handleApplyCustomSearch,
    } = useDataTable<TData>({ tableData, tableName, columnDefs, prefix, debounceMs, partialReloadKey,
        columnResizing: resolvedOptions.columnResizing, columnSizing, onColumnSizingChange: setColumnSizing, onStateChange });

    const allRows = table.getRowModel().rows;
    const { virtualRows, totalHeight, offsetTop, scrollToIndex } = useVirtualRows(
        resolvedOptions.virtualScrolling, virtualContainerRef, allRows.length,
        density === "compact" ? 32 : density === "spacious" ? 52 : 40,
        resolvedOptions.directionalOverscan
    );

    // Imperative API ref
    useEffect(() => {
        if (!apiRef) return;
        apiRef.current = {
            scrollToRow: async (index: number) => { scrollToIndex?.(index); },
            autosizeColumns: async () => {
                if (tableElementRef.current) {
                    const sizes = autosizeAllColumns(tableElementRef, visibleColumnIds);
                    Object.entries(sizes).forEach(([id, width]) => {
                        const col = table.getColumn(id);
                        if (col) col.getSize();
                    });
                }
            },
            triggerExport: async (format: string) => {
                if (tableData.exportUrl) {
                    const url = buildExportUrl(tableData.exportUrl, format, visibleColumnIds);
                    window.open(url, "_blank");
                }
            },
            resetFilters: async () => {
                const url = new URL(window.location.href);
                for (const key of [...url.searchParams.keys()]) {
                    if (key.startsWith("filter")) url.searchParams.delete(key);
                }
                router.visit(url.toString());
            },
            getState: () => ({ sorting: meta.sorts, filters: meta.filters, page: meta.currentPage, perPage: meta.perPage }),
            focusCell: async (rowIndex: number, columnId: string) => {
                const cell = tableElementRef.current?.querySelector(`[data-row-index="${rowIndex}"][data-column-id="${columnId}"]`) as HTMLElement;
                cell?.focus();
            },
        };
    }, [apiRef, table, tableData.exportUrl, visibleColumnIds, meta, scrollToIndex]);

    // Infinite scroll observer
    useEffect(() => {
        if (!resolvedOptions.infiniteScroll || !onLoadMore || !hasMore) return;
        const sentinel = infiniteScrollSentinelRef.current;
        if (!sentinel) return;
        const observer = new IntersectionObserver((entries) => {
            if (entries[0]?.isIntersecting && !isLoadingMore) {
                setIsLoadingMore(true);
                Promise.resolve(onLoadMore(meta.currentPage + 1)).finally(() => setIsLoadingMore(false));
            }
        }, { threshold: 0.1 });
        observer.observe(sentinel);
        return () => observer.disconnect();
    }, [resolvedOptions.infiniteScroll, onLoadMore, hasMore, isLoadingMore, meta.currentPage]);

    // Clipboard paste handler
    useEffect(() => {
        if (!resolvedOptions.clipboardPaste || !onClipboardPaste) return;
        const handlePaste = (e: ClipboardEvent) => {
            const target = e.target as HTMLElement;
            if (target.tagName === "INPUT" || target.tagName === "TEXTAREA" || target.isContentEditable) return;
            const text = e.clipboardData?.getData("text/plain");
            if (!text) return;
            const rows = text.split("\n").filter(Boolean).map((line) => line.split("\t"));
            if (rows.length === 0 || rows[0].length === 0) return;
            e.preventDefault();
            const startRow = focusedRowIndex ?? 0;
            const visibleCols = table.getVisibleLeafColumns().filter((c) => c.getCanHide()).map((c) => c.id);
            const editableCols = visibleCols.filter((id) => mergedColumns.find((c) => c.id === id)?.editable);
            if (editableCols.length === 0) return;
            onClipboardPaste(startRow, editableCols[0], rows).then(() => showToast(t.pasteSuccess, "success")).catch(() => showToast(t.pasteError, "error"));
        };
        document.addEventListener("paste", handlePaste);
        return () => document.removeEventListener("paste", handlePaste);
    }, [resolvedOptions.clipboardPaste, onClipboardPaste, focusedRowIndex, table, mergedColumns, t]);

    const treeRows = useMemo(() => {
        if (!treeConfig?.treeDataEnabled) return null;
        const parentKey = treeConfig.treeDataParentKey ?? "parent_id";
        const rows = allRows;
        // Group rows by parent
        const childMap = new Map<string | null, typeof rows>();
        for (const row of rows) {
            const parentId = String((row.original as Record<string, unknown>)[parentKey] ?? "null");
            const parent = parentId === "null" || parentId === "undefined" || parentId === "" ? null : parentId;
            if (!childMap.has(parent)) childMap.set(parent, []);
            childMap.get(parent)!.push(row);
        }
        return childMap;
    }, [treeConfig, allRows]);

    const filterColumns = useMemo(() => buildFilterColumns(mergedColumns), [mergedColumns]);
    const selectedRows = useMemo(() => table.getFilteredSelectedRowModel().rows.map((r) => r.original),
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [rowSelection, tableData.data]);

    const searchKey = prefix ? `${prefix}_search` : "search";
    const initialSearch = typeof window !== "undefined" ? new URL(window.location.href).searchParams.get(searchKey) ?? "" : "";
    const [globalSearchValue, setGlobalSearchValue] = useState(initialSearch);

    const handleGlobalSearchChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
        setGlobalSearchValue(e.target.value); handleGlobalSearch(e.target.value);
    }, [handleGlobalSearch]);

    const handleBulkClick = useCallback((action: NonNullable<typeof bulkActions>[number]) => {
        if (action.confirm) {
            const opts: DataTableConfirmOptions = typeof action.confirm === "object" ? action.confirm : {};
            setBulkConfirm({ action, opts, rows: serverSelectAll ? serverSelectedIds as TData[] : selectedRows });
        } else { action.onClick(serverSelectAll ? serverSelectedIds as TData[] : selectedRows); }
    }, [serverSelectAll, serverSelectedIds, selectedRows]);

    const handleRowInteraction = useCallback((row: TData, e: React.MouseEvent) => {
        if (rowLink) { const href = rowLink(row); if (e.metaKey || e.ctrlKey) window.open(href, "_blank"); else window.location.href = href; }
        else if (onRowClick) onRowClick(row);
    }, [rowLink, onRowClick]);

    const handleRowCheckboxClick = useCallback((rowIndex: number, e: React.MouseEvent) => {
        if (e.shiftKey && lastSelectedIndex.current !== null && lastSelectedIndex.current !== rowIndex) {
            const start = Math.min(lastSelectedIndex.current, rowIndex);
            const end = Math.max(lastSelectedIndex.current, rowIndex);
            const newSelection: Record<string, boolean> = { ...rowSelection };
            for (let i = start; i <= end; i++) newSelection[String(i)] = true;
            setRowSelection(newSelection);
        }
        lastSelectedIndex.current = rowIndex;
    }, [rowSelection, setRowSelection]);

    const handleSelectAllMatching = useCallback(async () => {
        if (!tableData.selectAllUrl) return;
        try {
            const currentParams = new URL(window.location.href).searchParams;
            const url = new URL(tableData.selectAllUrl, window.location.origin);
            for (const [key, value] of currentParams.entries()) url.searchParams.set(key, value);
            const response = await fetch(url.toString());
            const data = await response.json();
            setServerSelectAll(true); setServerSelectedIds(data.ids ?? []);
            const allSelection: Record<string, boolean> = {};
            table.getRowModel().rows.forEach((_, i) => { allSelection[String(i)] = true; });
            setRowSelection(allSelection);
        } catch { /* silently fail */ }
    }, [tableData.selectAllUrl, table, setRowSelection]);

    const clearServerSelectAll = useCallback(() => { setServerSelectAll(false); setServerSelectedIds([]); setRowSelection({}); }, [setRowSelection]);

    const handleTableKeyDown = useCallback((e: React.KeyboardEvent) => {
        if (!resolvedOptions.keyboardNavigation) return;
        const rows = table.getRowModel().rows;
        if (rows.length === 0) return;
        if (e.key === "ArrowDown") { e.preventDefault(); setFocusedRowIndex((prev) => prev === null ? 0 : Math.min(prev + 1, rows.length - 1)); }
        else if (e.key === "ArrowUp") { e.preventDefault(); setFocusedRowIndex((prev) => prev === null ? 0 : Math.max(prev - 1, 0)); }
        else if (e.key === "Enter" && focusedRowIndex !== null) { e.preventDefault(); const row = rows[focusedRowIndex]; if (row) handleRowInteraction(row.original, e as unknown as React.MouseEvent); }
        else if (e.key === "Escape") { setFocusedRowIndex(null); setRowSelection({}); }
        else if (e.key === " " && focusedRowIndex !== null && hasBulkActions) { e.preventDefault(); const row = rows[focusedRowIndex]; if (row) row.toggleSelected(!row.getIsSelected()); }
    }, [resolvedOptions.keyboardNavigation, table, focusedRowIndex, handleRowInteraction, setRowSelection, hasBulkActions]);

    useEffect(() => {
        if (focusedRowIndex === null || !tableBodyRef.current) return;
        tableBodyRef.current.querySelectorAll("tr")[focusedRowIndex]?.scrollIntoView({ block: "nearest" });
    }, [focusedRowIndex]);

    // Soft deletes toggle handler
    const handleTrashedToggle = useCallback(() => {
        const newValue = !showTrashed;
        setShowTrashed(newValue);
        const p = prefix ? `${prefix}_` : "";
        const currentUrl = new URL(window.location.href);
        if (newValue) currentUrl.searchParams.set(`${p}with_trashed`, "1");
        else currentUrl.searchParams.delete(`${p}with_trashed`);
        router.get(currentUrl.pathname + "?" + currentUrl.searchParams.toString(), {}, { preserveScroll: true });
    }, [showTrashed, prefix]);

    // Conditional rules
    const rules = config?.rules ?? [];

    const getRowRuleClass = useCallback((row: TData): string => {
        if (rules.length === 0) return "";
        return rules.filter((rule) => {
            const value = (row as Record<string, unknown>)[rule.column];
            return evaluateRule(rule, value) && rule.row?.class;
        }).map((r) => r.row!.class!).join(" ");
    }, [rules]);

    const getCellRuleClass = useCallback((row: TData, columnId: string): string => {
        if (rules.length === 0) return "";
        return rules.filter((rule) => {
            if (rule.column !== columnId) return false;
            const value = (row as Record<string, unknown>)[rule.column];
            return evaluateRule(rule, value) && rule.cell?.class;
        }).map((r) => r.cell!.class!).join(" ");
    }, [rules]);

    const editableColumns = useMemo(() => mergedColumns.filter((c) => c.editable), [mergedColumns]);

    // Conditional formatting: compute cell styles from user-created rules
    const getCondFormatStyle = useCallback((row: TData, columnId: string): React.CSSProperties => {
        if (condFormat.rules.length === 0) return {};
        const rowData = row as Record<string, unknown>;
        for (const rule of condFormat.rules) {
            if (rule.column !== columnId) continue;
            if (evaluateConditionalFormat(rule, rowData[columnId])) {
                return {
                    backgroundColor: rule.style.backgroundColor,
                    color: rule.style.textColor,
                    fontWeight: rule.style.fontWeight === "bold" ? "bold" : undefined,
                };
            }
        }
        return {};
    }, [condFormat.rules]);

    // Undo/Redo handlers
    const handleUndo = useCallback(() => {
        const action = undoEdit();
        if (action && onInlineEdit) {
            // Find the row with matching ID and apply old value
            const row = tableData.data.find((r) => (r as Record<string, unknown>).id === action.rowId);
            if (row) onInlineEdit(row, action.columnId, action.oldValue);
        }
    }, [undoEdit, onInlineEdit, tableData.data]);

    const handleRedo = useCallback(() => {
        const action = redoEdit();
        if (action && onInlineEdit) {
            const row = tableData.data.find((r) => (r as Record<string, unknown>).id === action.rowId);
            if (row) onInlineEdit(row, action.columnId, action.newValue);
        }
    }, [redoEdit, onInlineEdit, tableData.data]);

    // Column pin handler
    const handlePinColumn = useCallback((columnId: string, direction: false | "left" | "right") => {
        const col = table.getColumn(columnId);
        if (col) col.pin(direction);
    }, [table]);

    // Keyboard shortcut: ? to show shortcuts overlay
    const searchInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (!resolvedOptions.shortcutsOverlay) return;
        const handler = (e: KeyboardEvent) => {
            if (e.key === "?" && !e.ctrlKey && !e.metaKey && !(e.target instanceof HTMLInputElement) && !(e.target instanceof HTMLTextAreaElement)) {
                setShortcutsOpen(true);
            }
            // Ctrl+F: focus global search input
            if (resolvedOptions.globalSearch && (e.ctrlKey || e.metaKey) && e.key === "f" && !(e.target instanceof HTMLInputElement)) {
                e.preventDefault();
                searchInputRef.current?.focus();
            }
            // Ctrl+Z / Ctrl+Y for undo/redo
            if (resolvedOptions.undoRedo && e.ctrlKey && !e.shiftKey && e.key === "z") {
                e.preventDefault(); handleUndo();
            }
            if (resolvedOptions.undoRedo && e.ctrlKey && (e.key === "y" || (e.shiftKey && e.key === "z"))) {
                e.preventDefault(); handleRedo();
            }
        };
        window.addEventListener("keydown", handler);
        return () => window.removeEventListener("keydown", handler);
    }, [resolvedOptions.shortcutsOverlay, resolvedOptions.undoRedo, resolvedOptions.globalSearch, handleUndo, handleRedo]);

    // Selection persistence: sync with TanStack row selection
    useEffect(() => {
        if (!resolvedOptions.persistSelection) return;
        const selected = table.getSelectedRowModel().rows;
        const currentIds = selected.map((r) => (r.original as Record<string, unknown>).id);
        if (currentIds.length > 0) addPersistedIds(currentIds);
    }, [resolvedOptions.persistSelection, table.getSelectedRowModel().rows, addPersistedIds]);

    const toolbarProps = { tableData, table, tableName, columnVisibility, columnOrder, applyColumns,
        onReorderColumns: setColumnOrder, handleApplyQuickView, handleApplyCustomSearch, resolvedOptions, t,
        density, onDensityChange: handleDensityChange, onImportClick: tableData.importUrl ? () => setImportDialogOpen(true) : undefined,
        onShowShortcuts: () => setShortcutsOpen(true), canUndo, canRedo, onUndo: handleUndo, onRedo: handleRedo };

    // Mobile breakpoint detection
    const isMobile = useMobileBreakpoint(mobileBreakpoint);

    // Filter chip clear handlers
    const filterKeyForChips = prefix ? `${prefix}_filter` : "filter";
    const pageKeyForChips = prefix ? `${prefix}_page` : "page";
    const handleClearFilterChip = useCallback((columnId: string) => {
        const url = new URL(window.location.href);
        url.searchParams.delete(`${filterKeyForChips}[${columnId}]`);
        url.searchParams.delete(pageKeyForChips);
        const options: Record<string, unknown> = { preserveScroll: true };
        if (partialReloadKey) options.only = [partialReloadKey];
        router.get(url.pathname + "?" + url.searchParams.toString(), {}, options);
    }, [filterKeyForChips, pageKeyForChips, partialReloadKey]);

    const handleClearAllFilterChips = useCallback(() => {
        const url = new URL(window.location.href);
        for (const key of [...url.searchParams.keys()]) {
            if (key.startsWith(`${filterKeyForChips}[`)) url.searchParams.delete(key);
        }
        url.searchParams.delete(pageKeyForChips);
        const options: Record<string, unknown> = { preserveScroll: true };
        if (partialReloadKey) options.only = [partialReloadKey];
        router.get(url.pathname + "?" + url.searchParams.toString(), {}, options);
    }, [filterKeyForChips, pageKeyForChips, partialReloadKey]);

    const activeFilterColumnIds = useMemo(() => new Set(Object.keys(meta.filters as Record<string, unknown>)), [meta.filters]);

    const summaryLabels: Record<string, string> = useMemo(() => ({
        sum: t.summarySum, avg: t.summaryAvg, min: t.summaryMin, max: t.summaryMax, count: t.summaryCount,
        range: t.summaryRange ?? "Range",
    }), [t.summarySum, t.summaryAvg, t.summaryMin, t.summaryMax, t.summaryCount]);

    const allVisibleLeafColumns = useMemo(() => [
        ...table.getLeftVisibleLeafColumns(),
        ...table.getCenterVisibleLeafColumns(),
        ...table.getRightVisibleLeafColumns(),
    ], [table.getLeftVisibleLeafColumns(), table.getCenterVisibleLeafColumns(), table.getRightVisibleLeafColumns()]);

    // Column virtualization: only render columns in the visible range
    const visibleLeafColumns = useMemo(() => {
        if (!visibleColumnRange) return allVisibleLeafColumns;
        return allVisibleLeafColumns.filter((_, i) => i >= visibleColumnRange.startIndex && i <= visibleColumnRange.endIndex);
    }, [allVisibleLeafColumns, visibleColumnRange]);

    // Context menu: hide column handler
    const handleHideColumn = useCallback((columnId: string) => {
        const col = table.getColumn(columnId);
        if (col) col.toggleVisibility(false);
    }, [table]);

    // Row grouping: group rows by column value (server-side or user-selectable)
    const groupByColumn = userGroupBy ?? tableData.groupByColumn;
    const groupedRows = useMemo(() => {
        if (!groupByColumn || (!resolvedOptions.rowGrouping && !userGroupBy)) return null;
        const rows = table.getRowModel().rows;
        const groups = new Map<string, typeof rows>();
        for (const row of rows) {
            const val = String((row.original as Record<string, unknown>)[groupByColumn] ?? t.ungrouped);
            if (!groups.has(val)) groups.set(val, []);
            groups.get(val)!.push(row);
        }
        return groups;
    }, [groupByColumn, resolvedOptions.rowGrouping, table, t.ungrouped]);

    // Batch edit handler
    const handleBatchEditApply = useCallback((columnId: string, value: unknown) => {
        if (onBatchEdit) onBatchEdit(selectedRows, columnId, value);
    }, [onBatchEdit, selectedRows]);

    return (
        <div className="space-y-4 dt-root">
            {/* Skip-link for keyboard users to bypass toolbar */}
            <a href={`#dt-table-${tableName}`} className="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:rounded-md focus:bg-primary focus:px-3 focus:py-1.5 focus:text-primary-foreground focus:text-sm focus:font-medium focus:shadow-lg">
                {t.skipToTable}
            </a>
            {slots?.beforeTable}

            {/* ── Analytics section ── */}
            {(tableData.analytics?.length || slots?.analytics) && (
                <AnalyticsSection<TData>
                    analytics={tableData.analytics ?? []}
                    slot={slots?.analytics}
                    data={tableData.data}
                    columns={mergedColumns}
                    t={t}
                />
            )}

            {/* ── Toolbar ── */}
            <div className="flex flex-wrap items-center justify-between gap-2 sm:gap-3 print:hidden">
                <div className="flex flex-1 items-center gap-2 min-w-0">
                    {resolvedOptions.globalSearch && (
                        <div className="relative w-56 lg:w-64">
                            <Search className="absolute left-2.5 top-2 h-4 w-4 text-muted-foreground/60" />
                            <Input ref={searchInputRef} placeholder={t.search} value={globalSearchValue} onChange={handleGlobalSearchChange}
                                className="h-8 pl-8 text-sm rounded-lg border-border/60 focus-visible:ring-primary/30" aria-label={t.search} />
                        </div>
                    )}
                    {resolvedOptions.filters && (
                        <Filters columns={filterColumns} serverFilters={meta.filters as Record<string, unknown>} t={t}
                            prefix={prefix} debounceMs={debounceMs} partialReloadKey={partialReloadKey} renderFilter={renderFilter} />
                    )}
                    {config?.softDeletesEnabled && (
                        <Button variant={showTrashed ? "secondary" : "outline"} size="sm" className="h-8 gap-1.5" onClick={handleTrashedToggle}>
                            {showTrashed ? <EyeOff className="h-3.5 w-3.5" /> : <Trash2 className="h-3.5 w-3.5" />}
                            <span className="hidden sm:inline">{showTrashed ? t.hideTrashed : t.showTrashed}</span>
                        </Button>
                    )}
                    {resolvedOptions.headerFilters && (
                        <Button variant={headerFiltersVisible ? "secondary" : "outline"} size="sm" className="h-8 gap-1.5"
                            onClick={() => setHeaderFiltersVisible(v => !v)}
                            title={t.headerFilterToggle ?? "Toggle column filters"}
                            aria-label={t.headerFilterToggle ?? "Toggle column filters"}
                            aria-pressed={headerFiltersVisible}>
                            <Rows3 className="h-3.5 w-3.5" />
                            <span className="hidden sm:inline">{t.headerFilterToggle ?? "Column filters"}</span>
                        </Button>
                    )}
                </div>
                <div className="flex items-center gap-2">
                    {/* Presence indicators */}
                    {resolvedOptions.presence && <PresenceIndicator users={presenceUsers} t={t} />}

                    {/* Layout switcher */}
                    {resolvedOptions.layoutSwitcher && (
                        <LayoutSwitcher layout={layoutMode} onLayoutChange={handleLayoutChange}
                            showKanban={resolvedOptions.kanbanView && !!kanbanColumnId} t={t} />
                    )}

                    {/* Secondary actions — overflow "More" menu */}
                    {(resolvedOptions.conditionalFormatting || (resolvedOptions.integratedCharts && numericCols.length > 0) || resolvedOptions.findReplace || aiBaseUrl) && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="sm" className="h-8 gap-1.5">
                                    <Plus className="h-3.5 w-3.5" />
                                    <span className="hidden sm:inline">{t.more ?? "More"}</span>
                                    {(condFormat.rules.length > 0 || chartState || findReplaceOpen || aiPanelOpen) && (
                                        <span className="h-1.5 w-1.5 rounded-full bg-primary shrink-0" />
                                    )}
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-52">
                                {resolvedOptions.conditionalFormatting && (
                                    <DropdownMenuItem onClick={() => setCondFormatOpen(true)}>
                                        <Paintbrush className="mr-2 h-4 w-4" />
                                        {t.conditionalFormatting}
                                        {condFormat.rules.length > 0 && (
                                            <span className="ml-auto rounded-full bg-primary/10 px-1.5 text-[10px] font-medium text-primary">{condFormat.rules.length}</span>
                                        )}
                                    </DropdownMenuItem>
                                )}
                                {resolvedOptions.integratedCharts && numericCols.length > 0 && (
                                    <DropdownMenuItem onClick={() => chartState ? setChartState(null) : openChart()}>
                                        <BarChart3 className="mr-2 h-4 w-4" />
                                        {t.chartTitle}
                                        {chartState && <span className="ml-auto h-1.5 w-1.5 rounded-full bg-primary shrink-0" />}
                                    </DropdownMenuItem>
                                )}
                                {resolvedOptions.findReplace && (
                                    <DropdownMenuItem onClick={() => setFindReplaceOpen(v => !v)}>
                                        <Search className="mr-2 h-4 w-4" />
                                        {t.findReplace}
                                        {findReplaceOpen && <span className="ml-auto h-1.5 w-1.5 rounded-full bg-primary shrink-0" />}
                                    </DropdownMenuItem>
                                )}
                                {aiBaseUrl && (
                                    <DropdownMenuItem onClick={() => setAiPanelOpen(v => !v)}>
                                        <Sparkles className="mr-2 h-4 w-4" />
                                        {t.aiAssistant}
                                        {aiPanelOpen && <span className="ml-auto h-1.5 w-1.5 rounded-full bg-primary shrink-0" />}
                                    </DropdownMenuItem>
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    )}

                    {slots?.toolbar ?? (
                        <>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <Button variant="ghost" size="icon" className="h-8 w-8 shrink-0 md:hidden"><EllipsisVertical className="h-4 w-4" /></Button>
                                </PopoverTrigger>
                                <PopoverContent align="end" className="flex w-auto flex-col gap-2 p-2"><DataTableToolbar {...toolbarProps} /></PopoverContent>
                            </Popover>
                            <div className="hidden items-center gap-2 md:flex">
                                {/* Header actions */}
                                {headerActions?.map((action, i) => {
                                    const Icon = action.icon;
                                    return (
                                        <Button key={i} variant={action.variant ?? "outline"} size="sm" className="h-8 gap-1.5" onClick={action.onClick}>
                                            {Icon && <Icon className="h-3.5 w-3.5" />}
                                            <span className="hidden sm:inline">{action.label}</span>
                                        </Button>
                                    );
                                })}
                                {/* User-selectable grouping */}
                                {groupByOptions && groupByOptions.length > 0 && (
                                    <GroupBySelector options={groupByOptions} columns={mergedColumns}
                                        currentGroupBy={userGroupBy} onChange={handleGroupByChange} t={t} />
                                )}
                                <DataTableToolbar {...toolbarProps} />
                            </div>
                        </>
                    )}
                </div>
            </div>

            {/* ── Active filter chips ── */}
            <FilterChips filters={meta.filters as Record<string, unknown>} columns={mergedColumns}
                onClear={handleClearFilterChip} onClearAll={handleClearAllFilterChips} t={t} />

            {/* ── Faceted filters with counts ── */}
            {resolvedOptions.facetedFilters && facetedCounts && (
                <FacetedFilterSection columns={mergedColumns} facetedCounts={facetedCounts}
                    serverFilters={meta.filters as Record<string, unknown>}
                    prefix={prefix} partialReloadKey={partialReloadKey} t={t} />
            )}

            {/* ── Find & Replace bar ── */}
            {resolvedOptions.findReplace && findReplaceOpen && (
                <FindReplaceBar state={findReplace}
                    onReplace={handleFindReplace} onReplaceAll={handleFindReplaceAll}
                    onClose={() => { setFindReplaceOpen(false); findReplace.setQuery(""); }}
                    t={t} />
            )}

            {/* ── Integrated Charts panel ── */}
            {resolvedOptions.integratedCharts && chartState && (
                <IntegratedChartPanel data={tableData.data as Record<string, unknown>[]}
                    columns={mergedColumns} chartState={chartState}
                    onClose={() => setChartState(null)}
                    onChangeColumn={(colId) => setChartState(prev => prev ? { ...prev, columnId: colId } : null)}
                    onChangeType={(type) => setChartState(prev => prev ? { ...prev, chartType: type } : null)}
                    availableTypes={resolvedChartTypes} t={t} />
            )}

            {/* ── Inline row creation ── */}
            {onRowCreate && (
                <InlineRowCreator columns={mergedColumns} onRowCreate={onRowCreate} t={t} />
            )}

            {/* ── Bulk actions bar ── */}
            {hasBulkActions && selectedRows.length > 0 && (
                <div className="flex items-center gap-2 rounded-lg border bg-primary/5 border-primary/20 px-3 py-2 text-sm print:hidden">
                    <span className="font-medium tabular-nums">{serverSelectAll ? t.selected(serverSelectedIds.length) : t.selected(selectedRows.length)}</span>
                    {!serverSelectAll && tableData.selectAllUrl && meta.total > tableData.data.length && table.getIsAllPageRowsSelected() && (
                        <Button variant="link" size="sm" className="h-auto p-0 text-xs" onClick={handleSelectAllMatching}>{t.selectAllMatching(meta.total)}</Button>
                    )}
                    {serverSelectAll && (
                        <Button variant="link" size="sm" className="h-auto p-0 text-xs" onClick={clearServerSelectAll}>{t.clearSelection}</Button>
                    )}
                    <div className="ml-auto flex items-center gap-1.5">
                        {resolvedOptions.batchEdit && editableColumns.length > 0 && onBatchEdit && (
                            <Button variant="outline" size="sm" className="h-7 text-xs" onClick={() => setBatchEditOpen(true)}>
                                <Pencil className="mr-1 h-3.5 w-3.5" />{t.batchEdit}
                            </Button>
                        )}
                        {bulkActions.map((action) => {
                            const Icon = action.icon;
                            return (
                                <Button key={action.id} variant={action.variant === "destructive" ? "destructive" : "outline"}
                                    size="sm" className="h-7 text-xs" disabled={action.disabled?.(selectedRows) ?? false}
                                    onClick={() => handleBulkClick(action)}>
                                    {Icon && <Icon className="mr-1 h-3.5 w-3.5" />}{action.label}
                                </Button>
                            );
                        })}
                        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={clearServerSelectAll}><X className="h-3.5 w-3.5" /></Button>
                    </div>
                </div>
            )}

            {/* ── Loading indicator ── */}
            {resolvedOptions.loading && isNavigating && (
                <div className="flex items-center justify-center gap-2 py-1.5 text-xs text-muted-foreground/70 print:hidden">
                    <Loader2 className="h-3.5 w-3.5 animate-spin" />{t.loading}
                </div>
            )}

            {/* ── Deferred loading placeholder ── */}
            {config?.deferLoading && !deferLoaded && (
                <div className="flex items-center justify-center gap-2 py-12 text-sm text-muted-foreground">
                    <Loader2 className="h-5 w-5 animate-spin" />{t.loading}
                </div>
            )}

            {/* ── Mobile card layout ── */}
            {isMobile && (!config?.deferLoading || deferLoaded) && (
                <MobileCardLayout rows={tableData.data} columns={mergedColumns}
                    renderCell={renderCell} actions={actions} onRowClick={onRowClick}
                    rowLink={rowLink} t={t} density={density} />
            )}

            {/* ── Grid Layout ── */}
            {!isMobile && layoutMode === "grid" && (!config?.deferLoading || deferLoaded) && (
                <GridLayout rows={tableData.data} columns={mergedColumns}
                    renderCell={renderCell} actions={actions} onRowClick={onRowClick}
                    rowLink={rowLink} t={t} density={density}
                    imageColumn={cardImageColumn} titleColumn={cardTitleColumn}
                    subtitleColumn={cardSubtitleColumn} />
            )}

            {/* ── Cards Layout ── */}
            {!isMobile && layoutMode === "cards" && (!config?.deferLoading || deferLoaded) && (
                <CardLayout rows={tableData.data} columns={mergedColumns}
                    renderCell={renderCell} actions={actions} onRowClick={onRowClick}
                    rowLink={rowLink} t={t} density={density}
                    titleColumn={cardTitleColumn} subtitleColumn={cardSubtitleColumn}
                    imageColumn={cardImageColumn} />
            )}

            {/* ── Kanban Layout ── */}
            {!isMobile && layoutMode === "kanban" && kanbanColumnId && (!config?.deferLoading || deferLoaded) && (
                <KanbanLayout rows={tableData.data} columns={mergedColumns}
                    kanbanColumnId={kanbanColumnId} renderCell={renderCell}
                    actions={actions} onRowClick={onRowClick} rowLink={rowLink}
                    onKanbanMove={onKanbanMove} t={t} density={density}
                    titleColumn={cardTitleColumn} subtitleColumn={cardSubtitleColumn} />
            )}

            {/* ── Table ── */}
            {!isMobile && layoutMode === "table" && (!config?.deferLoading || deferLoaded) && (
                <div id={`dt-table-${tableName}`} className={cn("rounded-lg border overflow-hidden relative", className)}
                    tabIndex={resolvedOptions.keyboardNavigation ? 0 : undefined}
                    onKeyDown={resolvedOptions.keyboardNavigation ? handleTableKeyDown : undefined}>
                    <div ref={scrollShadowLeftRef} className="pointer-events-none absolute left-0 top-0 bottom-0 w-6 z-20 bg-gradient-to-r from-background/80 to-transparent opacity-0 transition-opacity duration-150" aria-hidden="true" />
                    <div ref={scrollShadowRightRef} className="pointer-events-none absolute right-0 top-0 bottom-0 w-6 z-20 bg-gradient-to-l from-background/80 to-transparent opacity-0 transition-opacity duration-150" aria-hidden="true" />
                    <div ref={virtualContainerRef} className={cn("overflow-x-auto", resolvedOptions.virtualScrolling && "max-h-[600px] overflow-y-auto")}
                        style={resolvedOptions.virtualScrolling && autoSizerDimensions && autoSizerDimensions.height > 0 ? { height: autoSizerDimensions.height } : undefined}>
                        <Table ref={tableElementRef} className="w-max min-w-full" style={resolvedOptions.columnResizing ? { width: table.getCenterTotalSize() } : undefined}
                            role="grid" aria-rowcount={meta.total} aria-colcount={table.getVisibleLeafColumns().length}>
                            <TableHeader className={cn(resolvedOptions.stickyHeader && "sticky top-0 z-10 bg-background shadow-[0_1px_3px_-1px_rgba(0,0,0,0.1)]")}>
                                {table.getHeaderGroups().map((headerGroup, groupIdx) => {
                                    const isGroupRow = groupIdx < table.getHeaderGroups().length - 1;
                                    return (
                                        <TableRow key={headerGroup.id} className={cn(isGroupRow && "border-b-0")}>
                                            {headerGroup.headers.map((header) => {
                                                if (isGroupRow) {
                                                    const pin = getColumnPinningProps(header.column);
                                                    const isActualGroup = !header.isPlaceholder && header.colSpan > 1;
                                                    return (
                                                        <TableHead key={header.id} colSpan={header.colSpan} style={pin.style}
                                                            className={cn("h-8",
                                                                isActualGroup && "text-center text-[11px] font-semibold uppercase tracking-widest text-muted-foreground bg-muted/50 border-b border-border/60",
                                                                isActualGroup && groupClassName?.[header.column.columnDef.header as string],
                                                                pin.className)}>
                                                            {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                                        </TableHead>
                                                    );
                                                }
                                                const colDef = mergedColumns.find((c) => c.id === header.column.id);
                                                const isNumber = colDef?.type === "number" || colDef?.type === "currency" || colDef?.type === "percentage";
                                                const leafGroup = colDef?.group;
                                                const pin = getColumnPinningProps(header.column);
                                                const hasActiveFilter = colDef ? activeFilterColumnIds.has(colDef.id) : false;
                                                const sortState = colDef?.sortable ? meta.sorts.find((s) => s.id === colDef.id) : undefined;
                                                const ariaSort = sortState ? (sortState.direction === "asc" ? "ascending" : "descending") as const : colDef?.sortable ? "none" as const : undefined;
                                                const headerContent = (
                                                    <>
                                                        {header.isPlaceholder ? null : colDef?.sortable ? (
                                                            <div className="flex flex-col">
                                                                <div className="flex items-center gap-1">
                                                                    <DataTableColumnHeader label={colDef.label} sortable={colDef.sortable} sorts={meta.sorts}
                                                                        columnId={colDef.id} onSort={handleSort} align={isNumber ? "right" : "left"}>
                                                                        {renderHeader?.[colDef.id]}
                                                                    </DataTableColumnHeader>
                                                                    {hasActiveFilter && <span className="h-1.5 w-1.5 rounded-full bg-primary shrink-0" />}
                                                                    {resolvedOptions.columnStatistics && colDef && (
                                                                        <ColumnStatsPopover columnId={colDef.id} columnLabel={colDef.label}
                                                                            columnType={colDef.type} data={tableData.data as Record<string, unknown>[]} t={t} />
                                                                    )}
                                                                </div>
                                                                {colDef.description && <span className="text-[11px] font-normal text-muted-foreground/60 leading-tight line-clamp-1" title={colDef.description}>{colDef.description}</span>}
                                                            </div>
                                                        ) : (
                                                            <div className="flex flex-col">
                                                                <div className="flex items-center gap-1">
                                                                    {renderHeader?.[header.column.id] ?? flexRender(header.column.columnDef.header, header.getContext())}
                                                                    {hasActiveFilter && <span className="h-1.5 w-1.5 rounded-full bg-primary shrink-0" />}
                                                                    {resolvedOptions.columnStatistics && colDef && (
                                                                        <ColumnStatsPopover columnId={colDef.id} columnLabel={colDef.label}
                                                                            columnType={colDef.type} data={tableData.data as Record<string, unknown>[]} t={t} />
                                                                    )}
                                                                </div>
                                                                {colDef?.description && <span className="text-[11px] font-normal text-muted-foreground/60 leading-tight">{colDef.description}</span>}
                                                            </div>
                                                        )}
                                                        {resolvedOptions.columnResizing && header.column.getCanResize() && (
                                                            <div onMouseDown={header.getResizeHandler()} onTouchStart={header.getResizeHandler()}
                                                                onDoubleClick={resolvedOptions.columnAutoSize ? () => {
                                                                    const width = autosizeColumn(tableElementRef, header.column.id);
                                                                    if (width) header.column.getSize(); // trigger re-render
                                                                } : undefined}
                                                                className={cn("absolute right-0 top-0 h-full w-1 cursor-col-resize select-none touch-none",
                                                                    header.column.getIsResizing() ? "bg-primary" : "hover:bg-border")} />
                                                        )}
                                                    </>
                                                );
                                                return (
                                                    <TableHead key={header.id} colSpan={header.colSpan}
                                                        style={{ ...pin.style, ...(resolvedOptions.columnResizing ? { width: header.getSize() } : {}) }}
                                                        className={cn("h-9 text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-border/40", isNumber && "text-right",
                                                            header.column.id.startsWith("_") && "w-px",
                                                            leafGroup && groupClassName?.[leafGroup],
                                                            pin.className, "relative")}
                                                        aria-sort={ariaSort} role="columnheader">
                                                        {resolvedOptions.contextMenu && colDef ? (
                                                            <ColumnContextMenu columnId={colDef.id} sortable={colDef.sortable}
                                                                isPinned={header.column.getIsPinned() || false}
                                                                showPinning={resolvedOptions.columnPinning}
                                                                onSort={handleSort} onHide={handleHideColumn}
                                                                onPin={handlePinColumn} t={t}>
                                                                {headerContent}
                                                            </ColumnContextMenu>
                                                        ) : headerContent}
                                                    </TableHead>
                                                );
                                            })}
                                        </TableRow>
                                    );
                                })}
                                {/* Header filters row — toggleable */}
                                {resolvedOptions.headerFilters && headerFiltersVisible && (
                                    <TableRow className="border-b border-border/30 animate-in fade-in-0 slide-in-from-top-1 duration-150">
                                        {table.getVisibleLeafColumns().map((col) => {
                                            const colDef = tableData.columns.find(c => c.id === col.id);
                                            const isFilterable = colDef?.filterable || colDef?.headerFilter;
                                            return (
                                                <TableHead key={`hf-${col.id}`} className="py-1 px-2">
                                                    {isFilterable ? (
                                                        <Input
                                                            placeholder={t.headerFilterPlaceholder}
                                                            className="h-7 text-xs"
                                                            value={headerFilterValues[col.id] ?? ""}
                                                            onChange={(e) => handleHeaderFilterChange(col.id, e.target.value)}
                                                            data-header-filter={col.id}
                                                            aria-label={`${t.filter} ${colDef?.label ?? col.id}`}
                                                        />
                                                    ) : null}
                                                </TableHead>
                                            );
                                        })}
                                    </TableRow>
                                )}
                            </TableHeader>
                            <TableBody ref={tableBodyRef} role="rowgroup">
                                {/* Pinned top rows */}
                                {tableData.pinnedTopRows?.map((pinnedRow, pIdx) => (
                                    <TableRow key={`pinned-top-${pIdx}`} className="bg-primary/5 border-b-2 border-primary/30 font-medium"
                                        aria-label={t.pinnedRow}>
                                        {visibleLeafColumns.map((col) => {
                                            const val = (pinnedRow as Record<string, unknown>)[col.id];
                                            const colDef = mergedColumns.find((c) => c.id === col.id);
                                            const pin = getColumnPinningProps(col);
                                            let rendered: React.ReactNode = val != null ? String(val) : "";
                                            if (colDef?.type === "boolean" && typeof val === "boolean") {
                                                rendered = <Checkbox checked={val} disabled className="data-[state=checked]:bg-emerald-500 data-[state=checked]:border-emerald-500" aria-label={`${colDef.label}: ${val ? t.yes : t.no}`} />;
                                            } else if (colDef?.type === "badge" && val != null) {
                                                const badgeVal = String(val);
                                                rendered = <span className="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium">{badgeVal}</span>;
                                            }
                                            return (
                                                <TableCell key={col.id} style={pin.style} className={cn("whitespace-nowrap", densityClasses.cell, pin.className)}>
                                                    {rendered}
                                                </TableCell>
                                            );
                                        })}
                                    </TableRow>
                                ))}
                                {resolvedOptions.loading && isNavigating ? (
                                    <SkeletonRows count={Math.min(meta.perPage, 10)} colCount={table.getVisibleLeafColumns().length} />
                                ) : table.getRowModel().rows.length > 0 ? (
                                    (() => {
                                        const renderRow = (row: ReturnType<typeof table.getRowModel>["rows"][number], index: number) => {
                                            // Scroll-aware rendering: show simplified placeholder during fast scroll
                                            if (isScrollingFast) {
                                                return (
                                                    <TableRow key={row.id} className={cn("border-b border-border/40", densityClasses.row)}>
                                                        {row.getVisibleCells().map((cell) => (
                                                            <TableCell key={cell.id} className={cn("whitespace-nowrap", densityClasses.cell)}>
                                                                <div className="h-4 w-full rounded bg-muted/40 animate-pulse" />
                                                            </TableCell>
                                                        ))}
                                                    </TableRow>
                                                );
                                            }
                                            const dataAttrs = rowDataAttributes?.(row.original) ?? {};
                                            const rowRuleClass = getRowRuleClass(row.original);
                                            const rowId = String((row.original as Record<string, unknown>).id ?? row.index);
                                            const isExpanded = hasDetailRows && expandedRows.has(rowId);
                                            const isDragOver = dragOverRowIndex === index && dragRowIndex !== index;
                                            return (
                                                <>{/* keyed fragment */}
                                                    <TableRow key={row.id} data-state={row.getIsSelected() ? "selected" : undefined} {...dataAttrs}
                                                        role="row" aria-rowindex={(meta.currentPage - 1) * meta.perPage + index + 1}
                                                        aria-selected={row.getIsSelected() || undefined}
                                                        className={cn(
                                                            "group/row transition-colors border-b border-border/20",
                                                            densityClasses.row,
                                                            "hover:bg-muted/50",
                                                            row.getIsSelected() && "bg-primary/8 hover:bg-primary/12",
                                                            isClickable && "cursor-pointer",
                                                            focusedRowIndex === index && "ring-2 ring-inset ring-primary",
                                                            isDragOver && "border-t-2 border-t-primary",
                                                            rowRuleClass, rowClassName?.(row.original))}
                                                        onClick={(e) => {
                                                            if (hasBulkActions && e.shiftKey) { handleRowCheckboxClick(index, e); return; }
                                                            if (isClickable) {
                                                                const target = e.target as HTMLElement;
                                                                if (target.closest("button, a, input, [role='checkbox'], [role='switch'], [data-slot='clear']")) return;
                                                                handleRowInteraction(row.original, e);
                                                            }
                                                        }}>
                                                        {row.getVisibleCells().map((cell) => {
                                                            const pin = getColumnPinningProps(cell.column);
                                                            const cellMeta = cell.column.columnDef.meta as ColumnMeta | undefined;
                                                            const cellRuleClass = getCellRuleClass(row.original, cell.column.id);
                                                            const cellContent = flexRender(cell.column.columnDef.cell, cell.getContext());
                                                            // Column spanning
                                                            const colSpanVal = columnSpan?.[cell.column.id]?.(row.original) ?? cellMeta?.colSpan ?? undefined;
                                                            // Row spanning
                                                            const rowSpanVal = rowSpan?.[cell.column.id]?.(row.original, index, tableData.data as TData[]) ?? undefined;
                                                            if (rowSpanVal === 0) return null; // Skip cells covered by a previous row's span
                                                            // Cell flashing
                                                            const flashKey = `${rowId}:${cell.column.id}`;
                                                            const isFlashing = flashingCells.has(flashKey);
                                                            // Dynamic row height
                                                            const isAutoHeight = cellMeta?.autoHeight;
                                                            // Drag-to-fill highlight
                                                            const isDragFillTarget = dragFillState?.columnId === cell.column.id && dragFillEndIndex !== null &&
                                                                index >= Math.min(dragFillState.startRowIndex, dragFillEndIndex) &&
                                                                index <= Math.max(dragFillState.startRowIndex, dragFillEndIndex) &&
                                                                index !== dragFillState.startRowIndex;
                                                            // Cell range selection highlight
                                                            const isCellInRange = resolvedOptions.cellRangeSelection && cellRange.isCellInRange(index, cell.column.id, visibleColumnIds);
                                                            // Conditional formatting styles
                                                            const condStyle = resolvedOptions.conditionalFormatting ? getCondFormatStyle(row.original, cell.column.id) : {};
                                                            // Find & Replace highlight
                                                            const findKey = `${index}:${cell.column.id}`;
                                                            const isFindMatch = findReplaceHighlights.has(findKey);
                                                            const isFindCurrent = findKey === findReplaceCurrentKey;
                                                            return (
                                                                <TableCell key={cell.id} role="gridcell"
                                                                    colSpan={colSpanVal} rowSpan={rowSpanVal}
                                                                    data-editable-cell={cellMeta?.editable ? "" : undefined}
                                                                    data-column-id={cell.column.id}
                                                                    data-row-index={index}
                                                                    style={{ ...pin.style, ...(resolvedOptions.columnResizing ? { width: cell.column.getSize() } : {}), ...condStyle,
                                                                        ...(isFindCurrent ? { backgroundColor: "hsl(47.9 95.8% 53.1% / 0.4)", outline: "2px solid hsl(47.9 95.8% 53.1%)" } : isFindMatch ? { backgroundColor: "hsl(47.9 95.8% 53.1% / 0.15)" } : {}) }}
                                                                    className={cn(
                                                                        isAutoHeight ? "whitespace-normal" : "whitespace-nowrap",
                                                                        densityClasses.cell,
                                                                        cell.column.id.startsWith("_") && "w-px",
                                                                        cellMeta?.type === "number" && "text-right",
                                                                        cellMeta?.type === "currency" && "text-right",
                                                                        cellMeta?.type === "percentage" && "text-right",
                                                                        cellMeta?.group && groupClassName?.[cellMeta.group],
                                                                        isFlashing && "animate-cell-flash",
                                                                        isDragFillTarget && "bg-primary/10 ring-1 ring-inset ring-primary/30",
                                                                        isCellInRange && "bg-primary/15 ring-1 ring-inset ring-primary/40",
                                                                        pin.className, cellRuleClass)}
                                                                    onMouseDown={resolvedOptions.cellRangeSelection ? () => cellRange.startSelection(index, cell.column.id) : undefined}
                                                                    onMouseOver={resolvedOptions.cellRangeSelection ? () => cellRange.updateSelection(index, cell.column.id) : undefined}
                                                                    onMouseUp={resolvedOptions.cellRangeSelection ? () => { cellRange.endSelection(); onCellRangeSelect?.(cellRange.rangeStart?.row ?? index, cellRange.rangeStart?.col ?? cell.column.id, index, cell.column.id); } : undefined}
                                                                    onDragOver={resolvedOptions.dragToFill && dragFillState ? (e) => { e.preventDefault(); handleDragFillOver(e, index); } : undefined}>
                                                                    <div className="relative">
                                                                        {resolvedOptions.copyCell && cell.column.id !== "_select" && cell.column.id !== "_actions" && cell.column.id !== "_expand" && cell.column.id !== "_reorder" ? (
                                                                            <CopyableCell value={cell.getValue()} enabled={true} t={t}>{cellContent}</CopyableCell>
                                                                        ) : cellContent}
                                                                        {/* Drag-to-fill handle */}
                                                                        {resolvedOptions.dragToFill && cellMeta?.editable && !dragFillState && (
                                                                            <span className="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 cursor-crosshair bg-primary border border-background rounded-sm opacity-0 group-hover/cell:opacity-100 hover:opacity-100 transition-opacity"
                                                                                draggable onDragStart={(e) => { e.stopPropagation(); handleDragFillStart(cell.column.id, index, cell.getValue()); }}
                                                                                onDragEnd={handleDragFillEnd} title={t.dragToFill} />
                                                                        )}
                                                                    </div>
                                                                </TableCell>
                                                            );
                                                        })}
                                                    </TableRow>
                                                    {isExpanded && renderDetailRow && detailDisplay === "inline" && (
                                                        <TableRow key={`${row.id}-detail`} className="bg-muted/20 hover:bg-muted/30 border-b border-border/30">
                                                            <TableCell colSpan={table.getVisibleLeafColumns().length} className="p-4">
                                                                {renderDetailRow(row.original)}
                                                            </TableCell>
                                                        </TableRow>
                                                    )}
                                                    {hasMasterDetail && masterDetailExpanded.has(rowId) && renderMasterDetail && (
                                                        <MasterDetailRow key={`${row.id}-master-detail`}
                                                            row={row.original}
                                                            colSpan={table.getVisibleLeafColumns().length}
                                                            renderContent={renderMasterDetail}
                                                            t={t} />
                                                    )}
                                                </>
                                            );
                                        };

                                        // Row grouping mode
                                        if (groupedRows) {
                                            // Resolve group header display labels using column options/type
                                            const groupCol = groupByColumn ? mergedColumns.find((c) => c.id === groupByColumn) : null;
                                            const resolveGroupLabel = (rawValue: string): string => {
                                                if (!groupCol) return rawValue;
                                                // Badge/option columns: map value to option label
                                                if ((groupCol.type === "badge" || groupCol.type === "option") && groupCol.options) {
                                                    const opt = groupCol.options.find((o) => o.value === rawValue);
                                                    if (opt) return opt.label;
                                                }
                                                // Boolean columns: show Yes/No instead of true/false
                                                if (groupCol.type === "boolean" || rawValue === "true" || rawValue === "false") {
                                                    if (rawValue === "true" || rawValue === "1") return t.yes;
                                                    if (rawValue === "false" || rawValue === "0") return t.no;
                                                }
                                                return rawValue;
                                            };
                                            let rowIdx = 0;
                                            return [...groupedRows.entries()].map(([groupName, rows]) => {
                                                const isCollapsed = collapsedGroups.has(groupName);
                                                const startIdx = rowIdx;
                                                rowIdx += rows.length;
                                                const displayLabel = resolveGroupLabel(groupName);
                                                return (
                                                    <>{/* group fragment */}
                                                        <TableRow key={`group-${groupName}`}
                                                            className="bg-muted/40 hover:bg-muted/50 cursor-pointer border-b border-border/40 font-medium"
                                                            onClick={() => setCollapsedGroups((prev) => {
                                                                const next = new Set(prev);
                                                                if (next.has(groupName)) next.delete(groupName); else next.add(groupName);
                                                                return next;
                                                            })}>
                                                            <TableCell colSpan={table.getVisibleLeafColumns().length} className="py-2 font-medium">
                                                                <div className="flex items-center gap-2">
                                                                    {isCollapsed ? <ChevronRight className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                                                                    <span>{displayLabel}</span>
                                                                    <span className="text-muted-foreground text-xs">({rows.length})</span>
                                                                </div>
                                                            </TableCell>
                                                        </TableRow>
                                                        {!isCollapsed && rows.map((row, i) => renderRow(row, startIdx + i))}
                                                    </>
                                                );
                                            });
                                        }

                                        // Tree data mode — hierarchical rows with expand/collapse
                                        if (treeRows) {
                                            const labelKey = treeConfig?.treeDataLabelKey ?? "name";
                                            const renderTreeRows = (parentId: string | null, depth: number): React.ReactNode[] => {
                                                const children = treeRows.get(parentId);
                                                if (!children) return [];
                                                return children.flatMap((row, i) => {
                                                    const rowId = String((row.original as Record<string, unknown>).id ?? row.index);
                                                    const hasChildren = treeRows.has(rowId);
                                                    const isExpanded = expandedTreeNodes.has(rowId);
                                                    const label = String((row.original as Record<string, unknown>)[labelKey] ?? "");
                                                    return [
                                                        <TableRow key={row.id} className={cn("border-b border-border/40", densityClasses.row)}>
                                                            <TableCell colSpan={table.getVisibleLeafColumns().length} className={densityClasses.cell}>
                                                                <div className="flex items-center" style={{ paddingLeft: `${depth * 1.5}rem` }}>
                                                                    {hasChildren ? (
                                                                        <button type="button" className="mr-1 p-0.5 hover:bg-muted rounded" onClick={() => {
                                                                            setExpandedTreeNodes(prev => {
                                                                                const next = new Set(prev);
                                                                                if (next.has(rowId)) next.delete(rowId); else next.add(rowId);
                                                                                return next;
                                                                            });
                                                                        }}>
                                                                            {isExpanded ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                                                                        </button>
                                                                    ) : <span className="inline-block w-5" />}
                                                                    <span>{label}</span>
                                                                </div>
                                                            </TableCell>
                                                        </TableRow>,
                                                        ...(isExpanded ? renderTreeRows(rowId, depth + 1) : []),
                                                    ];
                                                });
                                            };
                                            return <>{renderTreeRows(null, 0)}</>;
                                        }

                                        // Normal mode — with optional virtual scrolling
                                        if (virtualRows) {
                                            const rows = allRows.slice(virtualRows.startIndex, virtualRows.endIndex);
                                            return (
                                                <>
                                                    {offsetTop > 0 && <tr style={{ height: offsetTop }} />}
                                                    {rows.map((row, i) => renderRow(row, virtualRows.startIndex + i))}
                                                    {(totalHeight - offsetTop - rows.length * (density === "compact" ? 32 : density === "spacious" ? 52 : 40)) > 0 && (
                                                        <tr style={{ height: totalHeight - offsetTop - rows.length * (density === "compact" ? 32 : density === "spacious" ? 52 : 40) }} />
                                                    )}
                                                </>
                                            );
                                        }
                                        return allRows.map((row, index) => renderRow(row, index));
                                    })()
                                ) : (
                                    <TableRow>
                                        <TableCell colSpan={table.getVisibleLeafColumns().length} className="h-48 text-center text-muted-foreground/70">
                                            <EmptyState customEmpty={emptyState} illustration={emptyStateIllustration}
                                                showIllustration={resolvedOptions.emptyStateIllustration} t={t} />
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>

                            {/* Pinned bottom rows */}
                            {tableData.pinnedBottomRows?.map((pinnedRow, pIdx) => (
                                <TableRow key={`pinned-bottom-${pIdx}`} className="bg-primary/5 border-t border-primary/20 font-medium">
                                    {visibleLeafColumns.map((col) => {
                                        const val = (pinnedRow as Record<string, unknown>)[col.id];
                                        const pin = getColumnPinningProps(col);
                                        return (
                                            <TableCell key={col.id} style={pin.style} className={cn("whitespace-nowrap", densityClasses.cell, pin.className)}>
                                                {val != null ? String(val) : ""}
                                            </TableCell>
                                        );
                                    })}
                                </TableRow>
                            ))}

                            {/* Per-page footer */}
                            {tableData.footer && (
                                <TableFooter>
                                    <TableRow className="bg-muted/40 font-medium border-t-2 border-border/60">
                                        {visibleLeafColumns.map((col) => {
                                            const footerValue = tableData.footer?.[col.id];
                                            const colMeta = col.columnDef.meta as ColumnMeta | undefined;
                                            const isNumber = colMeta?.type === "number" || colMeta?.type === "currency" || colMeta?.type === "percentage";
                                            const group = colMeta?.group;
                                            const pin = getColumnPinningProps(col);
                                            let content: React.ReactNode = null;
                                            if (footerValue !== undefined && footerValue !== null) {
                                                if (renderFooterCell) {
                                                    const custom = renderFooterCell(col.id, footerValue);
                                                    content = custom !== undefined ? custom : (isNumber && typeof footerValue === "number" ? footerValue.toLocaleString() : String(footerValue));
                                                } else { content = isNumber && typeof footerValue === "number" ? footerValue.toLocaleString() : String(footerValue); }
                                            }
                                            return (
                                                <TableCell key={col.id} style={pin.style}
                                                    className={cn("whitespace-nowrap py-2 font-semibold", isNumber && "text-right tabular-nums",
                                                        group && groupClassName?.[group], pin.className)}>
                                                    {content}
                                                </TableCell>
                                            );
                                        })}
                                    </TableRow>
                                </TableFooter>
                            )}

                            {/* Full-dataset summary row */}
                            {tableData.summary && (
                                <TableFooter>
                                    <TableRow className="bg-muted/20 border-t-2 border-border/60">
                                        {visibleLeafColumns.map((col) => {
                                            const summaryValue = tableData.summary?.[col.id];
                                            const colDef = mergedColumns.find((c) => c.id === col.id);
                                            const colMeta = col.columnDef.meta as ColumnMeta | undefined;
                                            const isNumber = colMeta?.type === "number" || colMeta?.type === "currency" || colMeta?.type === "percentage";
                                            const pin = getColumnPinningProps(col);
                                            let content: React.ReactNode = null;
                                            if (summaryValue !== undefined && summaryValue !== null) {
                                                const label = summaryLabels[colDef?.summary ?? ""] ?? "";
                                                const formatted = isNumber && typeof summaryValue === "number" ? summaryValue.toLocaleString() : String(summaryValue);
                                                content = (
                                                    <span className="text-xs">
                                                        <span className="text-muted-foreground">{label} </span>
                                                        <span className="font-semibold tabular-nums">{formatted}</span>
                                                    </span>
                                                );
                                            }
                                            return (
                                                <TableCell key={col.id} style={pin.style}
                                                    className={cn("whitespace-nowrap py-1.5", isNumber && "text-right", pin.className)}>
                                                    {content}
                                                </TableCell>
                                            );
                                        })}
                                    </TableRow>
                                </TableFooter>
                            )}
                        </Table>
                    </div>
                    {/* ── Pagination + Auto-refresh ── */}
                    <div className="flex items-center justify-between border-t print:hidden">
                        <div className="flex-1">
                            {(config?.pollingInterval ?? 0) > 0 && (
                                <div className="flex items-center gap-1.5 px-3 text-xs text-muted-foreground">
                                    <RefreshCw className="h-3 w-3 animate-spin" style={{ animationDuration: "3s" }} />
                                    {t.autoRefresh} ({config!.pollingInterval}s)
                                </div>
                            )}
                        </div>
                        <div className="flex-1">
                            {slots?.pagination ?? (
                                <DataTablePagination meta={meta} onPageChange={handlePageChange} onPerPageChange={handlePerPageChange} onCursorChange={handleCursorChange} t={t} prefix={prefix} partialReloadKey={partialReloadKey} />
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* ── Status bar ── */}
            {resolvedOptions.statusBar && selectedRows.length > 0 && (
                slots?.statusBar ?? (() => {
                    const agg = computeStatusBarAggregates(
                        selectedRows as unknown as Record<string, unknown>[],
                        mergedColumns.map((c) => ({ id: c.id, type: c.type })),
                    );
                    if (!agg) return null;
                    return (
                        <div className="flex items-center gap-4 rounded-lg border bg-muted/30 px-3 py-1.5 text-xs text-muted-foreground print:hidden">
                            <span className="font-medium">{t.statusBarCount}: <span className="text-foreground tabular-nums">{agg.count}</span></span>
                            <span>{t.statusBarSum}: <span className="text-foreground tabular-nums">{agg.sum.toLocaleString()}</span></span>
                            <span>{t.statusBarAvg}: <span className="text-foreground tabular-nums">{agg.avg.toLocaleString(undefined, { maximumFractionDigits: 2 })}</span></span>
                            <span>{t.statusBarMin}: <span className="text-foreground tabular-nums">{agg.min.toLocaleString()}</span></span>
                            <span>{t.statusBarMax}: <span className="text-foreground tabular-nums">{agg.max.toLocaleString()}</span></span>
                        </div>
                    );
                })()
            )}

            {/* ── Infinite scroll sentinel ── */}
            {resolvedOptions.infiniteScroll && (
                <div ref={infiniteScrollSentinelRef} className="flex items-center justify-center py-4 print:hidden">
                    {isLoadingMore ? (
                        <div className="flex items-center gap-2 text-sm text-muted-foreground"><Loader2 className="h-4 w-4 animate-spin" />{t.loadingMore}</div>
                    ) : hasMore === false ? (
                        <span className="text-xs text-muted-foreground">{t.noMoreData}</span>
                    ) : null}
                </div>
            )}

            {/* ── Cell range selection indicator ── */}
            {resolvedOptions.cellRangeSelection && cellRange.selectedCellCount > 0 && (
                <div className="flex items-center gap-2 text-xs text-muted-foreground print:hidden">
                    <span>{t.cellsSelected(cellRange.selectedCellCount)}</span>
                    <Button variant="ghost" size="sm" className="h-6 px-2 text-xs" onClick={cellRange.clearSelection}>{t.clearSelection}</Button>
                </div>
            )}

            {/* ── AI Assistant ── */}
            {(onAiQuery || aiBaseUrl) && (
                <div className="space-y-3 print:hidden">
                    {/* NLQ input bar */}
                    <div className="flex items-center gap-2 rounded-lg border bg-muted/30 px-3 py-2">
                        <Sparkles className="h-4 w-4 shrink-0 text-muted-foreground" />
                        <Input
                            placeholder={t.aiPlaceholder}
                            value={aiQuery}
                            onChange={(e) => setAiQuery(e.target.value)}
                            onKeyDown={(e) => { if (e.key === "Enter") handleAiQuery(); }}
                            className="h-8 border-0 bg-transparent text-sm shadow-none focus-visible:ring-0"
                            disabled={aiQuerying}
                        />
                        {aiQuerying ? (
                            <div className="flex shrink-0 items-center gap-1.5 text-xs text-muted-foreground">
                                <Loader2 className="h-3.5 w-3.5 animate-spin" />
                                <span>{t.aiQuerying}</span>
                            </div>
                        ) : (
                            <Button
                                variant="ghost" size="sm"
                                className="h-7 shrink-0 gap-1.5 px-2.5 text-xs"
                                onClick={handleAiQuery}
                                disabled={!aiQuery.trim()}
                            >
                                <MessageSquare className="h-3 w-3" />
                                {t.aiAssistant}
                            </Button>
                        )}
                    </div>

                    {/* AI Panel */}
                    {aiBaseUrl && aiPanelOpen && (
                        <AiAssistantPanel
                            ai={ai}
                            t={t}
                            onApplyAction={handleAiApplyAction}
                            onClose={() => setAiPanelOpen(false)}
                            columns={mergedColumns}
                            selectedRowIds={Object.keys(rowSelection).filter(k => rowSelection[k])}
                            hasThesys={!!aiThesys}
                        />
                    )}
                </div>
            )}

            {/* ── Pivot mode controls ── */}
            {config?.pivotEnabled && onPivotChange && (
                <div className="flex items-center gap-3 rounded-lg border bg-muted/30 px-3 py-2 text-xs print:hidden">
                    <label className="flex items-center gap-1.5 font-medium">
                        <Checkbox checked={pivotActive} onCheckedChange={(checked) => setPivotActive(!!checked)} />
                        {t.pivotMode}
                    </label>
                    {pivotActive && config.pivotConfig && (
                        <span className="text-muted-foreground">
                            {t.pivotRowFields}: {config.pivotConfig.rowFields?.join(", ")} | {t.pivotValueField}: {config.pivotConfig.valueField} ({config.pivotConfig.aggregation})
                        </span>
                    )}
                </div>
            )}

            {/* ── Window scroller: scroll to top button ── */}
            {resolvedOptions.windowScroller && windowScrollTop > 400 && (
                <Button
                    variant="outline"
                    size="sm"
                    className="fixed bottom-6 right-6 z-50 shadow-lg print:hidden"
                    onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
                >
                    {t.scrollToTop}
                </Button>
            )}

            {slots?.afterTable}

            {/* ── Bulk action confirmation dialog ── */}
            <Dialog open={!!bulkConfirm} onOpenChange={(open) => { if (!open) setBulkConfirm(null); }}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{bulkConfirm?.opts.title ?? t.confirmTitle}</DialogTitle>
                        <DialogDescription>{bulkConfirm?.opts.description ?? t.confirmDescription}</DialogDescription>
                    </DialogHeader>
                    <DialogFoot>
                        <Button variant="outline" onClick={() => setBulkConfirm(null)}>{bulkConfirm?.opts.cancelLabel ?? t.confirmCancel}</Button>
                        <Button variant={bulkConfirm?.opts.variant ?? bulkConfirm?.action.variant ?? "default"}
                            onClick={() => { if (bulkConfirm) { bulkConfirm.action.onClick(bulkConfirm.rows); setBulkConfirm(null); } }}>
                            {bulkConfirm?.opts.confirmLabel ?? t.confirmAction}
                        </Button>
                    </DialogFoot>
                </DialogContent>
            </Dialog>

            {/* ── Keyboard shortcuts dialog ── */}
            {resolvedOptions.shortcutsOverlay && (
                <KeyboardShortcutsDialog open={shortcutsOpen} onOpenChange={setShortcutsOpen} t={t} />
            )}

            {/* ── Persisted selection indicator ── */}
            {resolvedOptions.persistSelection && persistedSelectionCount > 0 && (
                <div className="flex items-center gap-2 text-xs text-muted-foreground print:hidden">
                    <span>{t.selected(persistedSelectionCount)} (across pages)</span>
                    <Button variant="ghost" size="sm" className="h-6 px-2 text-xs" onClick={clearPersistedSelection}>
                        {t.clearSelection}
                    </Button>
                </div>
            )}

            {/* ── Batch edit dialog ── */}
            {resolvedOptions.batchEdit && editableColumns.length > 0 && onBatchEdit && (
                <BatchEditDialog open={batchEditOpen} onOpenChange={setBatchEditOpen}
                    selectedRows={selectedRows} editableColumns={editableColumns}
                    onApply={handleBatchEditApply} t={t} />
            )}

            {/* ── Import dialog ── */}
            {tableData.importUrl && (
                <ImportDialog open={importDialogOpen} onOpenChange={setImportDialogOpen}
                    importUrl={tableData.importUrl} t={t} />
            )}

            {/* ── Detail row modal ── */}
            {hasDetailRows && detailDisplay === "modal" && renderDetailRow && (
                <Dialog open={!!detailRow} onOpenChange={(open) => { if (!open) setDetailRow(null); }}>
                    <DialogContent className="sm:max-w-2xl max-h-[85vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>{t.expand}</DialogTitle>
                            <DialogDescription className="sr-only">{t.expand}</DialogDescription>
                        </DialogHeader>
                        <div className="py-2">
                            {detailRow && renderDetailRow(detailRow)}
                        </div>
                    </DialogContent>
                </Dialog>
            )}

            {/* ── Detail row drawer (side sheet) ── */}
            {hasDetailRows && detailDisplay === "drawer" && renderDetailRow && (
                <Sheet open={!!detailRow} onOpenChange={(open) => { if (!open) setDetailRow(null); }}>
                    <SheetContent className="sm:max-w-lg overflow-y-auto">
                        <SheetHeader>
                            <SheetTitle>{t.expand}</SheetTitle>
                            <SheetDescription className="sr-only">{t.expand}</SheetDescription>
                        </SheetHeader>
                        <div className="py-4">
                            {detailRow && renderDetailRow(detailRow)}
                        </div>
                    </SheetContent>
                </Sheet>
            )}

            {/* ── Form action dialog ── */}
            {formAction && formAction.action.form && (
                <FormActionDialog open={!!formAction} onOpenChange={(open) => { if (!open) setFormAction(null); }}
                    action={formAction.action} row={formAction.row} t={t} />
            )}

            {/* ── Conditional formatting dialog ── */}
            {resolvedOptions.conditionalFormatting && (
                <ConditionalFormatDialog open={condFormatOpen} onOpenChange={setCondFormatOpen}
                    columns={mergedColumns} rules={condFormat.rules}
                    onAddRule={condFormat.addRule} onUpdateRule={condFormat.updateRule}
                    onRemoveRule={condFormat.removeRule} t={t} />
            )}

            {resolvedOptions.printable && (
                <style>{`@media print { body * { visibility: hidden; } .dt-root, .dt-root * { visibility: visible; } .dt-root { position: absolute; left: 0; top: 0; width: 100%; } .print\\:hidden { display: none !important; } table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f5f5f5; font-weight: bold; } }`}</style>
            )}
            {/* Cell flashing animation */}
            {resolvedOptions.cellFlashing && (
                <style>{`@keyframes cell-flash { 0% { background-color: hsl(var(--primary) / 0.2); } 100% { background-color: transparent; } } .animate-cell-flash { animation: cell-flash 1.5s ease-out; }`}</style>
            )}
        </div>
    );
}

// ─── Exported component with Error Boundary ─────────────────────────────────

function DataTableWithBoundary<TData extends object>(props: DataTableProps<TData>) {
    return (
        <DataTableErrorBoundary>
            <DataTableInner {...props} />
        </DataTableErrorBoundary>
    );
}

/**
 * DataTable compound component.
 *
 * Supports both prop-based and JSX-based column configuration:
 *
 * @example Prop-based (traditional)
 * ```tsx
 * <DataTable tableData={data} tableName="products" renderCell={(col, val) => ...} />
 * ```
 *
 * @example JSX-based (declarative)
 * ```tsx
 * <DataTable tableData={data} tableName="products">
 *   <DataTable.Column id="name" renderCell={(val, row) => <strong>{val}</strong>} />
 *   <DataTable.Column id="status" renderHeader={<span>Status</span>} />
 * </DataTable>
 * ```
 */
export const DataTable = Object.assign(DataTableWithBoundary, {
    Column: DataTableColumn,
});
