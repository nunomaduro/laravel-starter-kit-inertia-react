import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { sanitizeHtml } from '@/lib/sanitize-html';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertTriangle, Info, Wrench, X } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';

type AlertColor = 'info' | 'warning' | 'error' | 'neutral';

const levelConfig: Record<string, { color: AlertColor; icon: typeof Info }> = {
    info: { color: 'info', icon: Info },
    warning: { color: 'warning', icon: AlertTriangle },
    maintenance: { color: 'error', icon: Wrench },
};

export function AnnouncementsBanner() {
    const { announcements = [] } = usePage<SharedData>().props;
    const [dismissedIds, setDismissedIds] = useState<Set<number>>(
        () => new Set(),
    );

    const visible = useMemo(
        () => announcements.filter((a) => !dismissedIds.has(a.id)),
        [announcements, dismissedIds],
    );

    const dismiss = useCallback((id: number) => {
        setDismissedIds((prev) => new Set(prev).add(id));
    }, []);

    if (visible.length === 0) return null;

    return (
        <div className="space-y-2 px-4 py-2" data-pan="announcements-banner">
            {visible.map((announcement) => {
                const { color, icon: Icon } =
                    levelConfig[announcement.level] ?? levelConfig.info;
                return (
                    <Alert
                        key={announcement.id}
                        variant="soft"
                        color={color}
                        className="flex items-start gap-2 pr-10"
                    >
                        <Icon className="size-4 shrink-0 translate-y-0.5" />
                        <div className="min-w-0 flex-1">
                            <AlertTitle>{announcement.title}</AlertTitle>
                            <AlertDescription>
                                {}
                                <span
                                    className="break-words whitespace-pre-wrap"
                                    dangerouslySetInnerHTML={{
                                        __html: sanitizeHtml(announcement.body),
                                    }}
                                />
                            </AlertDescription>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="absolute top-2 right-2 size-7 shrink-0"
                            aria-label="Dismiss"
                            onClick={() => dismiss(announcement.id)}
                        >
                            <X className="size-4" />
                        </Button>
                    </Alert>
                );
            })}
        </div>
    );
}
