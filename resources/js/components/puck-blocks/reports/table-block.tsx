import { useMemo, useState } from 'react';

export interface TableBlockProps {
    dataSource: string;
    title: string;
    data?: Record<string, unknown>[];
}

export function TableBlock({ title, data }: TableBlockProps) {
    const rows = data ?? [];
    const columns = rows.length > 0 ? Object.keys(rows[0]) : [];

    const [sortColumn, setSortColumn] = useState<string | null>(null);
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
    const [filterText, setFilterText] = useState('');

    const filteredRows = useMemo(() => {
        if (!filterText) return rows;
        const lower = filterText.toLowerCase();
        return rows.filter((row) =>
            columns.some((col) =>
                String(row[col] ?? '')
                    .toLowerCase()
                    .includes(lower),
            ),
        );
    }, [rows, columns, filterText]);

    const sortedRows = useMemo(() => {
        if (!sortColumn) return filteredRows;
        return [...filteredRows].sort((a, b) => {
            const aVal = a[sortColumn] ?? '';
            const bVal = b[sortColumn] ?? '';
            const aStr = String(aVal);
            const bStr = String(bVal);
            const comparison = aStr.localeCompare(bStr, undefined, {
                numeric: true,
            });
            return sortDirection === 'asc' ? comparison : -comparison;
        });
    }, [filteredRows, sortColumn, sortDirection]);

    function handleSort(col: string) {
        if (sortColumn === col) {
            setSortDirection((d) => (d === 'asc' ? 'desc' : 'asc'));
        } else {
            setSortColumn(col);
            setSortDirection('asc');
        }
    }

    function sortIndicator(col: string) {
        if (sortColumn !== col) return ' ↕';
        return sortDirection === 'asc' ? ' ↑' : ' ↓';
    }

    return (
        <div className="rounded-lg border bg-card p-4">
            {title && <h3 className="mb-3 text-lg font-semibold">{title}</h3>}
            {rows.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    No data available. Select a data source.
                </p>
            ) : (
                <>
                    <div className="mb-3">
                        <input
                            type="text"
                            placeholder="Filter rows…"
                            value={filterText}
                            onChange={(e) => setFilterText(e.target.value)}
                            className="w-full max-w-xs rounded-md border bg-background px-3 py-1.5 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        />
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b">
                                    {columns.map((col) => (
                                        <th
                                            key={col}
                                            className="cursor-pointer select-none px-3 py-2 text-left font-medium text-muted-foreground hover:text-foreground"
                                            onClick={() => handleSort(col)}
                                        >
                                            {col}
                                            <span className="text-xs">
                                                {sortIndicator(col)}
                                            </span>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {sortedRows.map((row, i) => (
                                    <tr
                                        key={i}
                                        className="border-b last:border-0"
                                    >
                                        {columns.map((col) => (
                                            <td key={col} className="px-3 py-2">
                                                {String(row[col] ?? '')}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    {filterText && (
                        <p className="mt-2 text-xs text-muted-foreground">
                            Showing {sortedRows.length} of {rows.length} rows
                        </p>
                    )}
                </>
            )}
        </div>
    );
}
