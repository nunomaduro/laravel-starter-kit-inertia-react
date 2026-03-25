import type { RendererProps } from './renderer-registry';
import { registerRenderer } from './renderer-registry';

function TableRenderer({ data }: RendererProps) {
    const headers = (data.headers as string[]) ?? [];
    const rows = (data.rows as string[][]) ?? [];
    const caption = data.caption as string | undefined;

    if (headers.length === 0 && rows.length === 0) return null;

    return (
        <div className="overflow-x-auto rounded-lg border">
            <table className="w-full text-left text-sm">
                {caption && (
                    <caption className="border-b px-3 py-2 text-left text-xs font-medium text-muted-foreground">
                        {caption}
                    </caption>
                )}
                {headers.length > 0 && (
                    <thead>
                        <tr className="border-b bg-muted/50">
                            {headers.map((h, i) => (
                                <th
                                    key={i}
                                    className="px-3 py-2 font-mono text-xs font-medium text-muted-foreground"
                                >
                                    {h}
                                </th>
                            ))}
                        </tr>
                    </thead>
                )}
                <tbody>
                    {rows.map((row, ri) => (
                        <tr
                            key={ri}
                            className="border-b last:border-0 transition-colors duration-100 hover:bg-muted/30"
                        >
                            {row.map((cell, ci) => (
                                <td
                                    key={ci}
                                    className="px-3 py-2 font-mono text-xs"
                                >
                                    {cell}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

registerRenderer('table', TableRenderer);

export { TableRenderer };
