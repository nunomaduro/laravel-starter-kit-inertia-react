import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal } from "lucide-react";
import { useState } from "react";
import type { DataTableTranslations } from "./i18n";
import type { DataTableAction, DataTableConfirmOptions } from "./types";

interface DataTableRowActionsProps<TData> {
    row: TData;
    actions: DataTableAction<TData>[];
    t: DataTableTranslations;
    /** Callback to open form action dialog externally */
    onFormAction?: (action: DataTableAction<TData>, row: TData) => void;
}

export function DataTableRowActions<TData>({
    row,
    actions,
    t,
    onFormAction,
}: DataTableRowActionsProps<TData>) {
    const [confirmAction, setConfirmAction] = useState<{ action: DataTableAction<TData>; opts: DataTableConfirmOptions } | null>(null);

    const visibleActions = actions.filter(
        (action) => !action.visible || action.visible(row),
    );

    if (visibleActions.length === 0) return null;

    function handleClick(action: DataTableAction<TData>) {
        // Form actions open a separate dialog
        if (action.form && action.form.length > 0 && onFormAction) {
            onFormAction(action, row);
            return;
        }
        if (action.confirm) {
            const opts: DataTableConfirmOptions = typeof action.confirm === "object"
                ? action.confirm
                : {};
            setConfirmAction({ action, opts });
        } else {
            action.onClick(row);
        }
    }

    function handleConfirm() {
        if (confirmAction) {
            confirmAction.action.onClick(row);
            setConfirmAction(null);
        }
    }

    function renderActionItem(action: DataTableAction<TData>, index: number) {
        // Action group (nested submenu)
        if (action.group && action.group.length > 0) {
            const visibleGroupActions = action.group.filter(
                (a) => !a.visible || a.visible(row),
            );
            if (visibleGroupActions.length === 0) return null;
            return (
                <DropdownMenuSub key={index}>
                    <DropdownMenuSubTrigger className={
                        action.variant === "destructive" ? "text-destructive focus:text-destructive" : ""
                    }>
                        {action.label}
                    </DropdownMenuSubTrigger>
                    <DropdownMenuSubContent>
                        {visibleGroupActions.map((subAction, subIdx) => (
                            <DropdownMenuItem
                                key={subIdx}
                                onClick={() => handleClick(subAction)}
                                className={
                                    subAction.variant === "destructive"
                                        ? "text-destructive focus:text-destructive"
                                        : ""
                                }
                            >
                                {subAction.label}
                            </DropdownMenuItem>
                        ))}
                    </DropdownMenuSubContent>
                </DropdownMenuSub>
            );
        }

        return (
            <DropdownMenuItem
                key={index}
                onClick={() => handleClick(action)}
                className={
                    action.variant === "destructive"
                        ? "text-destructive focus:text-destructive"
                        : ""
                }
            >
                {action.label}
            </DropdownMenuItem>
        );
    }

    // Separate destructive actions with a separator
    const normalActions = visibleActions.filter((a) => a.variant !== "destructive");
    const destructiveActions = visibleActions.filter((a) => a.variant === "destructive");

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="h-9 w-9 p-0">
                        <span className="sr-only">{t.actions}</span>
                        <MoreHorizontal className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    {normalActions.map((action, index) => renderActionItem(action, index))}
                    {destructiveActions.length > 0 && normalActions.length > 0 && <DropdownMenuSeparator />}
                    {destructiveActions.map((action, index) => renderActionItem(action, normalActions.length + index))}
                </DropdownMenuContent>
            </DropdownMenu>

            <Dialog open={!!confirmAction} onOpenChange={(open) => { if (!open) setConfirmAction(null); }}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>
                            {confirmAction?.opts.title ?? t.confirmTitle}
                        </DialogTitle>
                        <DialogDescription>
                            {confirmAction?.opts.description ?? t.confirmDescription}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setConfirmAction(null)}>
                            {confirmAction?.opts.cancelLabel ?? t.confirmCancel}
                        </Button>
                        <Button
                            variant={confirmAction?.opts.variant ?? confirmAction?.action.variant ?? "default"}
                            onClick={handleConfirm}
                        >
                            {confirmAction?.opts.confirmLabel ?? t.confirmAction}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
