export interface TableBlockProps {
    dataSource: string;
    title: string;
    data?: Record<string, unknown>[];
}

export function TableBlock({ title, data }: TableBlockProps) {
    const rows = data ?? [];
    const columns = rows.length > 0 ? Object.keys(rows[0]) : [];

    return (
        <div className="rounded-lg border bg-card p-4">
            {title && <h3 className="mb-3 text-lg font-semibold">{title}</h3>}
            {rows.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    No data available. Select a data source.
                </p>
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b">
                                {columns.map((col) => (
                                    <th
                                        key={col}
                                        className="px-3 py-2 text-left font-medium text-muted-foreground"
                                    >
                                        {col}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row, i) => (
                                <tr key={i} className="border-b last:border-0">
                                    {columns.map((col) => (
                                        <td key={col} className="px-3 py-2">
                                            {String(
                                                row[col] ?? '',
                                            )}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
