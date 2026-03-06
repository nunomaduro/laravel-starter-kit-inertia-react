import * as React from 'react';

import { cn } from '@/lib/utils';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

export type EntityType =
    | 'person'
    | 'organization'
    | 'location'
    | 'date'
    | 'product'
    | 'keyword'
    | string;

export interface Entity {
    text: string;
    type: EntityType;
    description?: string;
}

export interface EntityHighlightProps {
    /** Raw text content. */
    text: string;
    /** Entities to highlight within the text. */
    entities: Entity[];
    className?: string;
}

const ENTITY_STYLES: Record<string, string> = {
    person: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    organization: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
    location: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    date: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    product: 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300',
    keyword: 'bg-primary/10 text-primary',
};

function getEntityStyle(type: EntityType): string {
    return ENTITY_STYLES[type] ?? 'bg-muted text-muted-foreground';
}

interface Segment {
    text: string;
    entity?: Entity;
}

/** Splits `text` into segments, marking entity occurrences. */
function segmentize(text: string, entities: Entity[]): Segment[] {
    if (entities.length === 0) return [{ text }];

    const sorted = [...entities].sort((a, b) => {
        const ia = text.indexOf(a.text);
        const ib = text.indexOf(b.text);
        return ia - ib;
    });

    const segments: Segment[] = [];
    let cursor = 0;

    for (const entity of sorted) {
        const idx = text.indexOf(entity.text, cursor);
        if (idx === -1) continue;
        if (idx > cursor) {
            segments.push({ text: text.slice(cursor, idx) });
        }
        segments.push({ text: entity.text, entity });
        cursor = idx + entity.text.length;
    }

    if (cursor < text.length) {
        segments.push({ text: text.slice(cursor) });
    }

    return segments;
}

/**
 * Renders text with named entity spans highlighted by type.
 * Hovering a highlighted entity shows a tooltip with its type and optional description.
 */
export function EntityHighlight({ text, entities, className }: EntityHighlightProps) {
    const segments = React.useMemo(() => segmentize(text, entities), [text, entities]);

    return (
        <span className={cn('text-sm leading-relaxed', className)}>
            {segments.map((seg, i) => {
                if (!seg.entity) {
                    return <React.Fragment key={i}>{seg.text}</React.Fragment>;
                }

                const chip = (
                    <span
                        key={i}
                        className={cn(
                            'inline-block rounded px-1 py-0.5 text-xs font-medium cursor-default',
                            getEntityStyle(seg.entity.type),
                        )}
                    >
                        {seg.text}
                    </span>
                );

                if (seg.entity.description ?? seg.entity.type) {
                    return (
                        <Tooltip key={i}>
                            <TooltipTrigger asChild>{chip}</TooltipTrigger>
                            <TooltipContent side="top" className="text-xs">
                                <p className="font-semibold capitalize">{seg.entity.type}</p>
                                {seg.entity.description && (
                                    <p className="text-muted-foreground">{seg.entity.description}</p>
                                )}
                            </TooltipContent>
                        </Tooltip>
                    );
                }

                return chip;
            })}
        </span>
    );
}
