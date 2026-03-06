import * as React from "react"

import { cn } from "@/lib/utils"
import { Spinner } from "@/components/ui/spinner"
import { Skeleton } from "@/components/ui/skeleton"

type LoadingVariant = "spinner" | "skeleton" | "dots" | "pulse"

interface LoadingStateProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: LoadingVariant
  label?: string
  /** Number of skeleton rows to render when variant="skeleton" */
  skeletonRows?: number
}

function DotsIndicator({ className }: { className?: string }) {
  return (
    <div className={cn("flex items-center gap-1", className)}>
      {[0, 1, 2].map((i) => (
        <span
          key={i}
          className="size-2 rounded-full bg-primary animate-bounce"
          style={{ animationDelay: `${i * 0.15}s` }}
        />
      ))}
    </div>
  )
}

function LoadingState({
  variant = "spinner",
  label = "Loading...",
  skeletonRows = 3,
  className,
  ...props
}: LoadingStateProps) {
  return (
    <div
      data-slot="loading-state"
      role="status"
      aria-label={label}
      className={cn(
        "flex flex-col items-center justify-center gap-3 px-6 py-12",
        variant === "skeleton" && "items-stretch p-0",
        className,
      )}
      {...props}
    >
      {variant === "spinner" && (
        <>
          <Spinner size="lg" label={label} />
          <p className="text-muted-foreground text-sm">{label}</p>
        </>
      )}

      {variant === "dots" && (
        <>
          <DotsIndicator />
          <p className="text-muted-foreground text-sm">{label}</p>
        </>
      )}

      {variant === "pulse" && (
        <>
          <span className="size-10 rounded-full bg-primary/30 animate-ping" />
          <p className="text-muted-foreground text-sm">{label}</p>
        </>
      )}

      {variant === "skeleton" && (
        <div className="w-full space-y-3">
          {Array.from({ length: skeletonRows }).map((_, i) => (
            <Skeleton key={i} className="h-4 w-full" style={{ width: `${100 - i * 15}%` }} />
          ))}
        </div>
      )}
    </div>
  )
}

export { LoadingState }
export type { LoadingStateProps, LoadingVariant }
