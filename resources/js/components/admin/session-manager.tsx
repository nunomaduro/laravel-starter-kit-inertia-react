import {
    MonitorIcon,
    SmartphoneIcon,
    TabletIcon,
    Trash2Icon,
} from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';

export interface Session {
    id: string;
    device: string;
    deviceType?: 'desktop' | 'mobile' | 'tablet';
    browser?: string;
    ipAddress: string;
    location?: string;
    lastActive: string;
    isCurrent?: boolean;
}

interface SessionManagerProps {
    sessions: Session[];
    onRevoke: (id: string) => Promise<void> | void;
    onRevokeAll: () => Promise<void> | void;
    className?: string;
}

function DeviceIcon({
    type,
    className,
}: {
    type?: 'desktop' | 'mobile' | 'tablet';
    className?: string;
}) {
    switch (type) {
        case 'mobile':
            return <SmartphoneIcon className={className} />;
        case 'tablet':
            return <TabletIcon className={className} />;
        default:
            return <MonitorIcon className={className} />;
    }
}

function SessionManager({
    sessions,
    onRevoke,
    onRevokeAll,
    className,
}: SessionManagerProps) {
    const [revokeId, setRevokeId] = React.useState<string | null>(null);
    const [revokeAllOpen, setRevokeAllOpen] = React.useState(false);
    const [loading, setLoading] = React.useState(false);

    const otherSessions = sessions.filter((s) => !s.isCurrent);
    const currentSession = sessions.find((s) => s.isCurrent);

    const handleRevoke = async (id: string) => {
        setLoading(true);
        try {
            await onRevoke(id);
        } finally {
            setLoading(false);
            setRevokeId(null);
        }
    };

    const handleRevokeAll = async () => {
        setLoading(true);
        try {
            await onRevokeAll();
        } finally {
            setLoading(false);
            setRevokeAllOpen(false);
        }
    };

    return (
        <div className={cn('space-y-4', className)}>
            <Card>
                <CardHeader className="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle className="text-base">
                            Active Sessions
                        </CardTitle>
                        <CardDescription>
                            Manage and revoke active login sessions across all
                            devices.
                        </CardDescription>
                    </div>
                    {otherSessions.length > 0 && (
                        <Button
                            variant="outline"
                            size="sm"
                            className="border-destructive/30 text-destructive hover:bg-destructive/10 hover:text-destructive"
                            onClick={() => setRevokeAllOpen(true)}
                        >
                            <Trash2Icon className="mr-1.5 size-3.5" />
                            Revoke All Other Sessions
                        </Button>
                    )}
                </CardHeader>
                <CardContent className="space-y-3 p-4 pt-0">
                    {currentSession && (
                        <div className="space-y-3">
                            <h3 className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                Current Session
                            </h3>
                            <SessionRow
                                session={currentSession}
                                onRevoke={() => {}}
                            />
                        </div>
                    )}

                    {otherSessions.length > 0 && (
                        <>
                            {currentSession && <Separator />}
                            <div className="space-y-3">
                                <h3 className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                    Other Sessions
                                </h3>
                                {otherSessions.map((session) => (
                                    <SessionRow
                                        key={session.id}
                                        session={session}
                                        onRevoke={() => setRevokeId(session.id)}
                                    />
                                ))}
                            </div>
                        </>
                    )}

                    {sessions.length === 0 && (
                        <div className="py-8 text-center text-sm text-muted-foreground">
                            No active sessions found.
                        </div>
                    )}
                </CardContent>
            </Card>

            <ConfirmDialog
                open={revokeId !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setRevokeId(null);
                    }
                }}
                title="Revoke Session"
                description="This session will be immediately terminated and the device will need to log in again."
                confirmLabel="Revoke Session"
                variant="destructive"
                isLoading={loading}
                onConfirm={() => {
                    if (revokeId) {
                        void handleRevoke(revokeId);
                    }
                }}
            />

            <ConfirmDialog
                open={revokeAllOpen}
                onOpenChange={setRevokeAllOpen}
                title="Revoke All Other Sessions"
                description="All sessions except your current one will be terminated immediately. Those devices will need to log in again."
                confirmLabel="Revoke All"
                variant="destructive"
                isLoading={loading}
                onConfirm={() => void handleRevokeAll()}
            />
        </div>
    );
}

function SessionRow({
    session,
    onRevoke,
}: {
    session: Session;
    onRevoke: () => void;
}) {
    return (
        <div className="flex items-start gap-3 rounded-lg border p-3">
            <div className="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full bg-muted">
                <DeviceIcon
                    type={session.deviceType}
                    className="size-4 text-muted-foreground"
                />
            </div>
            <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                    <span className="text-sm font-medium">
                        {session.device}
                    </span>
                    {session.browser && (
                        <span className="text-xs text-muted-foreground">
                            {session.browser}
                        </span>
                    )}
                    {session.isCurrent && (
                        <Badge variant="secondary" className="text-xs">
                            Current
                        </Badge>
                    )}
                </div>
                <div className="mt-0.5 flex flex-wrap gap-3 text-xs text-muted-foreground">
                    <span>{session.ipAddress}</span>
                    {session.location && <span>{session.location}</span>}
                    <span>Active {session.lastActive}</span>
                </div>
            </div>
            {!session.isCurrent && (
                <Button
                    variant="ghost"
                    size="sm"
                    className="shrink-0 text-destructive hover:bg-destructive/10 hover:text-destructive"
                    onClick={onRevoke}
                >
                    <Trash2Icon className="mr-1 size-3.5" />
                    Revoke
                </Button>
            )}
        </div>
    );
}

export { SessionManager };
