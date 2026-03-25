import { Button } from '@/components/ui/button';
import { ExternalLink } from 'lucide-react';
import { useCallback } from 'react';
import { toast } from 'sonner';
import type { RendererProps } from './renderer-registry';
import { registerRenderer } from './renderer-registry';

interface ActionItem {
    label: string;
    url?: string;
    action?: string;
    variant?: 'default' | 'outline' | 'ghost';
}

function ActionRenderer({ data }: RendererProps) {
    const actions = (data.actions as ActionItem[]) ?? [];
    const description = data.description as string | undefined;

    const handleAction = useCallback((action: ActionItem) => {
        if (action.url) {
            window.open(action.url, '_blank', 'noopener,noreferrer');
            return;
        }
        if (action.action) {
            toast.info(`Action triggered: ${action.action}`);
        }
    }, []);

    if (actions.length === 0) return null;

    return (
        <div className="space-y-2">
            {description && (
                <p className="text-xs text-muted-foreground">{description}</p>
            )}
            <div className="flex flex-wrap gap-2">
                {actions.map((a, i) => (
                    <Button
                        key={i}
                        variant={a.variant ?? 'outline'}
                        size="sm"
                        onClick={() => handleAction(a)}
                        className="h-7 gap-1.5 text-xs"
                    >
                        {a.label}
                        {a.url && <ExternalLink className="size-3" />}
                    </Button>
                ))}
            </div>
        </div>
    );
}

registerRenderer('action', ActionRenderer);

export { ActionRenderer };
