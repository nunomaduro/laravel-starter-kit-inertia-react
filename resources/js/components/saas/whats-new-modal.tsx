import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface WhatsNewItem {
    title: string;
    description: string;
    badge?: string;
}

interface WhatsNewModalProps {
    version: string;
    items: WhatsNewItem[];
    storageKey?: string;
}

function WhatsNewModal({
    version,
    items,
    storageKey = 'whats-new-seen-version',
}: WhatsNewModalProps) {
    const [open, setOpen] = React.useState(() => {
        if (typeof window === 'undefined') return false;
        return localStorage.getItem(storageKey) !== version;
    });

    const handleDismiss = () => {
        localStorage.setItem(storageKey, version);
        setOpen(false);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(v) => {
                if (!v) handleDismiss();
            }}
        >
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>What&apos;s New in {version}</DialogTitle>
                    <DialogDescription>
                        Here&apos;s what we&apos;ve been working on for you.
                    </DialogDescription>
                </DialogHeader>
                <ul className="divide-y divide-border">
                    {items.map((item, i) => (
                        <li key={i} className="py-3 first:pt-0 last:pb-0">
                            <div className="flex items-start justify-between gap-2">
                                <span className="text-sm font-medium">
                                    {item.title}
                                </span>
                                {item.badge && (
                                    <Badge
                                        variant="secondary"
                                        className="shrink-0 text-xs"
                                    >
                                        {item.badge}
                                    </Badge>
                                )}
                            </div>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                {item.description}
                            </p>
                        </li>
                    ))}
                </ul>
                <DialogFooter>
                    <Button onClick={handleDismiss} className="w-full">
                        Got it
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

export { WhatsNewModal };
export type { WhatsNewItem, WhatsNewModalProps };
