import type { RendererProps } from './renderer-registry';
import { registerRenderer } from './renderer-registry';

function CardRenderer({ data }: RendererProps) {
    const title = data.title as string | undefined;
    const subtitle = data.subtitle as string | undefined;
    const fields = (data.fields as Array<{ label: string; value: string }>) ?? [];
    const imageUrl = data.image_url as string | undefined;

    return (
        <div className="rounded-lg border bg-card">
            {imageUrl && (
                <div className="overflow-hidden rounded-t-lg">
                    <img
                        src={imageUrl}
                        alt={title ?? ''}
                        className="h-32 w-full object-cover"
                    />
                </div>
            )}
            <div className="p-4">
                {title && (
                    <h4 className="font-mono text-sm font-semibold tracking-tight">
                        {title}
                    </h4>
                )}
                {subtitle && (
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        {subtitle}
                    </p>
                )}
                {fields.length > 0 && (
                    <dl className="mt-3 space-y-1.5">
                        {fields.map((f, i) => (
                            <div key={i} className="flex items-baseline gap-2">
                                <dt className="shrink-0 text-xs font-medium text-muted-foreground">
                                    {f.label}
                                </dt>
                                <dd className="font-mono text-xs">
                                    {f.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                )}
            </div>
        </div>
    );
}

registerRenderer('card', CardRenderer);

export { CardRenderer };
