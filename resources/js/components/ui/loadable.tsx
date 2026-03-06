import * as React from "react"

import { cn } from "@/lib/utils"
import { LoadingState, type LoadingVariant } from "@/components/ui/loading-state"
import { ErrorState } from "@/components/ui/error-state"
import { EmptyState } from "@/components/ui/empty-state"

interface LoadableProps<T> {
  /** The data to render */
  data: T | null | undefined
  /** Whether data is being fetched */
  isLoading?: boolean
  /** An error that occurred during fetch */
  error?: Error | string | null
  /** Callback to retry a failed fetch */
  onRetry?: () => void
  /** Render the data when it is available and non-empty */
  children: (data: T) => React.ReactNode
  /** Condition that decides whether the data is "empty" (default: falsy / empty array) */
  isEmpty?: (data: T) => boolean
  /** Props forwarded to the loading state */
  loadingVariant?: LoadingVariant
  loadingLabel?: string
  /** Props forwarded to the empty state */
  emptyTitle?: string
  emptyDescription?: string
  emptyIcon?: React.ReactNode
  emptyAction?: React.ReactNode
  /** Props forwarded to the error state */
  errorTitle?: string
  /** Custom className on the wrapper */
  className?: string
}

function Loadable<T>({
  data,
  isLoading = false,
  error = null,
  onRetry,
  children,
  isEmpty,
  loadingVariant = "spinner",
  loadingLabel = "Loading...",
  emptyTitle = "No data",
  emptyDescription,
  emptyIcon,
  emptyAction,
  errorTitle = "Something went wrong",
  className,
}: LoadableProps<T>) {
  const wrapperClass = cn("w-full", className)

  if (isLoading) {
    return (
      <div className={wrapperClass}>
        <LoadingState variant={loadingVariant} label={loadingLabel} />
      </div>
    )
  }

  if (error) {
    return (
      <div className={wrapperClass}>
        <ErrorState title={errorTitle} error={error} onRetry={onRetry} />
      </div>
    )
  }

  const isEmptyData = data == null || (isEmpty ? isEmpty(data) : Array.isArray(data) ? data.length === 0 : !data)

  if (isEmptyData) {
    return (
      <div className={wrapperClass}>
        <EmptyState
          title={emptyTitle}
          description={emptyDescription}
          icon={emptyIcon}
          action={emptyAction}
          bordered
        />
      </div>
    )
  }

  return <>{children(data as T)}</>
}

export { Loadable }
export type { LoadableProps }
