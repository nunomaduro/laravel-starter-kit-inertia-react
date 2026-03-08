import React from 'react';

export interface DataListBlockProps {
    dataSource: string;
    title?: string;
    limit?: number;
    data?: Record<string, unknown>[];
}

export function DataListBlock({
    title,
    data = [],
}: DataListBlockProps): React.JSX.Element {
    const items = Array.isArray(data) ? data : [];
    return (
        <section className="container py-8">
            {title && (
                <h2 className="mb-4 text-2xl font-semibold tracking-tight">
                    {title}
                </h2>
            )}
            {items.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    No data to display.
                </p>
            ) : (
                <ul className="space-y-2 rounded-lg border bg-card p-4">
                    {items.map((item, i) => (
                        <li
                            key={(item.id as string) ?? i}
                            className="flex justify-between text-sm"
                        >
                            <span className="font-medium">
                                {String(
                                    item.name ?? item.number ?? item.id ?? i,
                                )}
                            </span>
                            {item.email !== undefined &&
                                item.email !== null && (
                                    <span className="text-muted-foreground">
                                        {String(item.email)}
                                    </span>
                                )}
                            {item.status != null && (
                                <span className="rounded bg-muted px-1.5 py-0.5 text-xs">
                                    {String(item.status)}
                                </span>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
