export interface SummaryBlockProps {
    content: string;
    dataSource?: string;
    data?: Record<string, unknown>;
}

export function SummaryBlock({ content, data }: SummaryBlockProps) {
    const resolved = resolveTemplateVariables(content, data);

    return (
        <div className="rounded-lg border bg-card p-4">
            <p className="text-sm leading-relaxed text-foreground">{resolved}</p>
        </div>
    );
}

function resolveTemplateVariables(
    template: string,
    data?: Record<string, unknown>,
): string {
    if (!data) return template;
    return template.replace(/\{\{\s*([\w.]+)\s*\}\}/g, (_match, key: string) => {
        const value = key
            .split('.')
            .reduce<unknown>(
                (obj, k) =>
                    obj != null && typeof obj === 'object'
                        ? (obj as Record<string, unknown>)[k]
                        : undefined,
                data,
            );
        return value != null ? String(value) : `{{${key}}}`;
    });
}
