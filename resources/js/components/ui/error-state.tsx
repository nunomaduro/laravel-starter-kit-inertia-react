import * as React from "react"
import { AlertTriangleIcon, RefreshCwIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

interface ErrorStateProps extends React.HTMLAttributes<HTMLDivElement> {
  title?: string
  description?: string
  error?: Error | string | null
  onRetry?: () => void
  retryLabel?: string
  action?: React.ReactNode
  icon?: React.ReactNode
  bordered?: boolean
}

function ErrorState({
  title = "Something went wrong",
  description,
  error,
  onRetry,
  retryLabel = "Try again",
  action,
  icon,
  bordered = false,
  className,
  ...props
}: ErrorStateProps) {
  const errorMessage =
    description ??
    (error instanceof Error ? error.message : typeof error === "string" ? error : undefined)

  return (
    <div
      data-slot="error-state"
      className={cn(
        "flex flex-col items-center justify-center gap-4 px-6 py-12 text-center",
        bordered && "rounded-lg border border-dashed border-destructive/30",
        className,
      )}
      {...props}
    >
      <div className="flex size-12 items-center justify-center rounded-full bg-destructive/10 text-destructive">
        {icon ?? <AlertTriangleIcon className="size-6" />}
      </div>
      <div className="space-y-1">
        <h3 className="text-base font-semibold">{title}</h3>
        {errorMessage && (
          <p className="text-muted-foreground max-w-sm text-sm">{errorMessage}</p>
        )}
      </div>
      <div className="flex flex-wrap items-center justify-center gap-2">
        {onRetry && (
          <Button variant="outline" size="sm" onClick={onRetry}>
            <RefreshCwIcon className="mr-2 size-4" />
            {retryLabel}
          </Button>
        )}
        {action}
      </div>
    </div>
  )
}

export { ErrorState }
export type { ErrorStateProps }
