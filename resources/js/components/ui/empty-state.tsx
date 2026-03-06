import * as React from "react"

import { cn } from "@/lib/utils"

interface EmptyStateProps extends React.HTMLAttributes<HTMLDivElement> {
  icon?: React.ReactNode
  title: string
  description?: string
  action?: React.ReactNode
  secondaryAction?: React.ReactNode
  bordered?: boolean
}

function EmptyState({
  icon,
  title,
  description,
  action,
  secondaryAction,
  bordered = false,
  className,
  ...props
}: EmptyStateProps) {
  return (
    <div
      data-slot="empty-state"
      className={cn(
        "flex flex-col items-center justify-center gap-4 px-6 py-12 text-center",
        bordered && "rounded-lg border border-dashed",
        className,
      )}
      {...props}
    >
      {icon && (
        <div className="text-muted-foreground flex size-12 items-center justify-center rounded-full bg-muted">
          {icon}
        </div>
      )}
      <div className="space-y-1">
        <h3 className="text-base font-semibold">{title}</h3>
        {description && (
          <p className="text-muted-foreground max-w-sm text-sm">{description}</p>
        )}
      </div>
      {(action || secondaryAction) && (
        <div className="flex flex-wrap items-center justify-center gap-2">
          {action}
          {secondaryAction}
        </div>
      )}
    </div>
  )
}

export { EmptyState }
export type { EmptyStateProps }
