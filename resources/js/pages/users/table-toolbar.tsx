import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Keyboard } from 'lucide-react';

interface KeyboardShortcutsDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function KeyboardShortcutsDialog({ open, onOpenChange }: KeyboardShortcutsDialogProps) {
    return (
        <div className="flex justify-end px-2">
            <Dialog open={open} onOpenChange={onOpenChange}>
                <DialogTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8"
                        title="Keyboard shortcuts"
                        aria-label="Keyboard shortcuts"
                    >
                        <Keyboard className="h-4 w-4" />
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Keyboard shortcuts</DialogTitle>
                    </DialogHeader>
                    <ul className="list-inside list-disc space-y-1 text-sm text-muted-foreground">
                        <li>
                            <kbd className="rounded border bg-muted px-1.5 py-0.5 font-mono text-xs">
                                ?
                            </kbd>{' '}
                            Show this help
                        </li>
                        <li>
                            <kbd className="rounded border bg-muted px-1.5 py-0.5 font-mono text-xs">
                                Ctrl
                            </kbd>{' '}
                            + click row to open in new tab
                        </li>
                    </ul>
                </DialogContent>
            </Dialog>
        </div>
    );
}

export interface SendMessageDialogState {
    row: { email: string };
    subject: string;
    body: string;
}

interface SendMessageDialogProps {
    state: SendMessageDialogState | null;
    onStateChange: React.Dispatch<React.SetStateAction<SendMessageDialogState | null>>;
}

export function SendMessageDialog({ state, onStateChange }: SendMessageDialogProps) {
    return (
        <Dialog
            open={!!state}
            onOpenChange={(open) => !open && onStateChange(null)}
        >
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Send message</DialogTitle>
                </DialogHeader>
                {state && (
                    <form
                        className="grid gap-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            const { row, subject, body } = state;
                            const mailto = `mailto:${row.email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                            window.open(mailto, '_blank');
                            onStateChange(null);
                        }}
                    >
                        <p className="text-sm text-muted-foreground">
                            To: {state.row.email}
                        </p>
                        <div className="grid gap-2">
                            <Label htmlFor="msg-subject">Subject</Label>
                            <Input
                                id="msg-subject"
                                value={state.subject}
                                onChange={(e) =>
                                    onStateChange(
                                        (d) =>
                                            d && {
                                                ...d,
                                                subject: e.target.value,
                                            },
                                    )
                                }
                                placeholder="Subject"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="msg-body">Body</Label>
                            <Textarea
                                id="msg-body"
                                value={state.body}
                                onChange={(e) =>
                                    onStateChange(
                                        (d) =>
                                            d && {
                                                ...d,
                                                body: e.target.value,
                                            },
                                    )
                                }
                                placeholder="Message body"
                                rows={4}
                            />
                        </div>
                        <div className="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => onStateChange(null)}
                            >
                                Cancel
                            </Button>
                            <Button type="submit">Open in email client</Button>
                        </div>
                    </form>
                )}
            </DialogContent>
        </Dialog>
    );
}
