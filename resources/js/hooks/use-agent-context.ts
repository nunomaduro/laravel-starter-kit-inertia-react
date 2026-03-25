import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';

interface AgentContext {
    page: string;
    entity_type?: string;
    entity_id?: number;
    entity_name?: string;
}

export function useAgentContext(): AgentContext {
    const { url, props } = usePage();

    return useMemo(() => {
        const context: AgentContext = { page: url };
        const contextualKeys = ['contact', 'deal', 'project', 'lot', 'employee', 'activity'];

        for (const key of contextualKeys) {
            const entity = (props as Record<string, unknown>)[key];
            if (entity && typeof entity === 'object' && 'id' in entity) {
                context.entity_type = key;
                context.entity_id = (entity as { id: number }).id;
                const named = entity as Record<string, unknown>;
                context.entity_name =
                    ((named.name ?? named.title ?? named.full_name ?? `${named.first_name ?? ''} ${named.last_name ?? ''}`.trim()) as string) ||
                    undefined;
                break;
            }
        }

        return context;
    }, [url, props]);
}
