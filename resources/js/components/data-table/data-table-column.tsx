import type { ReactNode } from "react";

/**
 * Declarative column definition for JSX-based DataTable configuration.
 *
 * This is a configuration-only component — it renders nothing.
 * Use it as a child of <DataTable> to define columns declaratively.
 *
 * @example
 * ```tsx
 * <DataTable tableData={tableData} tableName="products">
 *   <DataTable.Column id="name" renderCell={(value, row) => <strong>{value}</strong>} />
 *   <DataTable.Column id="price" renderCell={(value) => `$${value}`} />
 *   <DataTable.Column id="status" renderHeader={<span className="text-blue-500">Status</span>} />
 * </DataTable>
 * ```
 */
export interface DataTableColumnProps<TData = unknown> {
    /** Column ID matching the server-side column definition */
    id: string;
    /** Custom cell renderer for this column */
    renderCell?: (value: unknown, row: TData) => ReactNode;
    /** Custom header content for this column */
    renderHeader?: ReactNode;
    /** Custom footer cell renderer */
    renderFooterCell?: (value: unknown) => ReactNode;
    /** Custom filter component */
    renderFilter?: (value: unknown, onChange: (value: unknown) => void) => ReactNode;
}

/**
 * DataTable.Column — a declarative, JSX-friendly way to configure columns.
 *
 * This component doesn't render anything. It's used by the parent <DataTable>
 * to collect column-level render overrides.
 */
export function DataTableColumn<TData = unknown>(_props: DataTableColumnProps<TData>): null {
    return null;
}

DataTableColumn.displayName = "DataTable.Column";

/**
 * Extract column configurations from DataTable children.
 * Scans React children for DataTable.Column elements and builds render maps.
 */
export function extractColumnConfigs<TData>(
    children: ReactNode,
): {
    renderCell?: (columnId: string, value: unknown, row: TData) => ReactNode | undefined;
    renderHeader?: Record<string, ReactNode>;
    renderFooterCell?: (columnId: string, value: unknown) => ReactNode | undefined;
    renderFilter?: Record<string, (value: unknown, onChange: (value: unknown) => void) => ReactNode>;
} {
    const cellRenderers = new Map<string, (value: unknown, row: TData) => ReactNode>();
    const headerMap: Record<string, ReactNode> = {};
    const footerRenderers = new Map<string, (value: unknown) => ReactNode>();
    const filterMap: Record<string, (value: unknown, onChange: (value: unknown) => void) => ReactNode> = {};

    // Iterate through children to find DataTable.Column elements
    const childArray = Array.isArray(children) ? children : children ? [children] : [];
    for (const child of childArray) {
        if (
            child &&
            typeof child === "object" &&
            "type" in child &&
            (child.type === DataTableColumn ||
                (typeof child.type === "function" && (child.type as { displayName?: string }).displayName === "DataTable.Column"))
        ) {
            const props = child.props as DataTableColumnProps<TData>;
            if (props.renderCell) cellRenderers.set(props.id, props.renderCell);
            if (props.renderHeader !== undefined) headerMap[props.id] = props.renderHeader;
            if (props.renderFooterCell) footerRenderers.set(props.id, props.renderFooterCell);
            if (props.renderFilter) filterMap[props.id] = props.renderFilter;
        }
    }

    const hasCell = cellRenderers.size > 0;
    const hasFooter = footerRenderers.size > 0;

    return {
        renderCell: hasCell
            ? (columnId, value, row) => cellRenderers.get(columnId)?.(value, row)
            : undefined,
        renderHeader: Object.keys(headerMap).length > 0 ? headerMap : undefined,
        renderFooterCell: hasFooter
            ? (columnId, value) => footerRenderers.get(columnId)?.(value)
            : undefined,
        renderFilter: Object.keys(filterMap).length > 0 ? filterMap : undefined,
    };
}
