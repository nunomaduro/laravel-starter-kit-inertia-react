import * as React from "react"
import { XIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface TrialBannerProps {
  daysRemaining: number | null
  onUpgrade?: () => void
  className?: string
  storageKey?: string
}

function TrialBanner({
  daysRemaining,
  onUpgrade,
  className,
  storageKey = "trial-banner-dismissed",
}: TrialBannerProps) {
  const [dismissed, setDismissed] = React.useState(() => {
    if (typeof window === "undefined") return false
    return localStorage.getItem(storageKey) === "true"
  })

  if (daysRemaining === null || dismissed) return null

  const handleDismiss = () => {
    setDismissed(true)
    localStorage.setItem(storageKey, "true")
  }

  const isExpiring = daysRemaining <= 3

  return (
    <div
      className={cn(
        "relative flex items-center justify-center gap-3 px-4 py-2 text-sm font-medium",
        isExpiring
          ? "bg-destructive text-destructive-foreground"
          : "bg-primary text-primary-foreground",
        className
      )}
    >
      <span>
        Your trial ends in{" "}
        <strong>
          {daysRemaining} {daysRemaining === 1 ? "day" : "days"}
        </strong>
        .{" "}
        {onUpgrade && (
          <button
            onClick={onUpgrade}
            className="underline underline-offset-2 hover:no-underline"
          >
            Upgrade now &rarr;
          </button>
        )}
      </span>
      <button
        onClick={handleDismiss}
        aria-label="Dismiss trial banner"
        className="absolute right-3 top-1/2 -translate-y-1/2 rounded p-1 opacity-70 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current"
      >
        <XIcon className="size-4" />
      </button>
    </div>
  )
}

export { TrialBanner }
export type { TrialBannerProps }
