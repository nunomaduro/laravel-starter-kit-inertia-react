export interface WidgetBlockProps {
    title: string;
    url: string;
    height: number;
    allowFullscreen: boolean;
}

export function WidgetBlock({
    title,
    url,
    height,
    allowFullscreen,
}: WidgetBlockProps) {
    if (!url) {
        return (
            <div className="rounded-lg border bg-card p-4">
                {title && (
                    <h3 className="mb-3 text-lg font-semibold">{title}</h3>
                )}
                <div
                    className="flex items-center justify-center rounded-md border bg-muted"
                    style={{ height: `${height}px` }}
                >
                    <p className="text-sm text-muted-foreground">
                        Enter a URL to embed content.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="rounded-lg border bg-card p-4">
            {title && <h3 className="mb-3 text-lg font-semibold">{title}</h3>}
            <iframe
                src={url}
                title={title || 'Embedded widget'}
                className="w-full rounded-md border"
                style={{ height: `${height}px` }}
                allowFullScreen={allowFullscreen}
                sandbox="allow-scripts allow-same-origin allow-popups"
            />
        </div>
    );
}
