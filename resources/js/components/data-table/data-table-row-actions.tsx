import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreHorizontal } from 'lucide-react';
import { useState } from 'react';
import type { DataTableAction, DataTableConfirmOptions } from './types';

interface DataTableRowActionsProps<TData> {
    row: TData;
    actions: DataTableAction<TData>[];
}

function getConfirmOptions(
    confirm: boolean | DataTableConfirmOptions | undefined,
): DataTableConfirmOptions {
    if (typeof confirm === 'object' && confirm !== null) {
        return confirm;
    }
    return {
        title: 'Are you sure?',
        description: 'This action cannot be undone.',
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        variant: 'default',
    };
}

export function DataTableRowActions<TData>({
    row,
    actions,
}: DataTableRowActionsProps<TData>) {
    const [confirmingAction, setConfirmingAction] =
        useState<DataTableAction<TData> | null>(null);

    const visibleActions = actions.filter(
        (action) => !action.visible || action.visible(row),
    );

    if (visibleActions.length === 0) return null;

    const runAction = (action: DataTableAction<TData>) => {
        if (action.confirm) {
            setConfirmingAction(action);
            return;
        }
        action.onClick(row);
    };

    const runConfirmed = () => {
        if (confirmingAction) {
            confirmingAction.onClick(row);
            setConfirmingAction(null);
        }
    };

    const opts = confirmingAction
        ? getConfirmOptions(confirmingAction.confirm)
        : null;

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="h-8 w-8 p-0">
                        <span className="sr-only">Actions</span>
                        <MoreHorizontal className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    {visibleActions.map((action) =>
                        action.group && action.group.length > 0 ? (
                            <DropdownMenuSub key={action.label}>
                                <DropdownMenuSubTrigger>
                                    {action.label}
                                </DropdownMenuSubTrigger>
                                <DropdownMenuSubContent>
                                    {action.group.map((sub) => (
                                        <DropdownMenuItem
                                            key={sub.label}
                                            onClick={() => runAction(sub)}
                                            className={
                                                sub.variant === 'destructive'
                                                    ? 'text-destructive focus:text-destructive'
                                                    : ''
                                            }
                                        >
                                            {sub.label}
                                        </DropdownMenuItem>
                                    ))}
                                </DropdownMenuSubContent>
                            </DropdownMenuSub>
                        ) : (
                            <DropdownMenuItem
                                key={action.label}
                                onClick={() => runAction(action)}
                                className={
                                    action.variant === 'destructive'
                                        ? 'text-destructive focus:text-destructive'
                                        : ''
                                }
                            >
                                {action.label}
                            </DropdownMenuItem>
                        ),
                    )}
                </DropdownMenuContent>
            </DropdownMenu>
            {opts && (
                <Dialog
                    open={!!confirmingAction}
                    onOpenChange={(open) => !open && setConfirmingAction(null)}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{opts.title}</DialogTitle>
                            {opts.description && (
                                <DialogDescription>
                                    {opts.description}
                                </DialogDescription>
                            )}
                        </DialogHeader>
                        <DialogFooter>
                            <Button
                                variant="outline"
                                onClick={() => setConfirmingAction(null)}
                            >
                                {opts.cancelLabel ?? 'Cancel'}
                            </Button>
                            <Button
                                variant={
                                    opts.variant === 'destructive'
                                        ? 'destructive'
                                        : 'default'
                                }
                                onClick={runConfirmed}
                            >
                                {opts.confirmLabel ?? 'Confirm'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            )}
        </>
    );
}
