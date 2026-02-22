import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal } from "lucide-react";
import type { DataTableAction } from "./types";

interface DataTableRowActionsProps<TData> {
    row: TData;
    actions: DataTableAction<TData>[];
}

export function DataTableRowActions<TData>({
    row,
    actions,
}: DataTableRowActionsProps<TData>) {
    const visibleActions = actions.filter(
        (action) => !action.visible || action.visible(row),
    );

    if (visibleActions.length === 0) return null;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="h-8 w-8 p-0">
                    <span className="sr-only">Actions</span>
                    <MoreHorizontal className="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {visibleActions.map((action, index) => (
                    <DropdownMenuItem
                        key={index}
                        onClick={() => action.onClick(row)}
                        className={
                            action.variant === "destructive"
                                ? "text-destructive focus:text-destructive"
                                : ""
                        }
                    >
                        {action.label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
