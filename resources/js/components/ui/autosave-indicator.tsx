import * as React from "react"
import { CheckIcon, Loader2Icon, AlertCircleIcon } from "lucide-react"

import { cn } from "@/lib/utils"

type AutosaveStatus = "idle" | "saving" | "saved" | "error"

interface AutosaveIndicatorProps {
  status: AutosaveStatus
  savingText?: string
  savedText?: string
  errorText?: string
  className?: string
}

const statusConfig = {
  idle: { icon: null, text: null, className: "" },
  saving: {
    icon: <Loader2Icon className="size-3.5 animate-spin" />,
    text: "Saving...",
    className: "text-muted-foreground",
  },
  saved: {
    icon: <CheckIcon className="size-3.5" />,
    text: "Saved ✓",
    className: "text-emerald-600 dark:text-emerald-400",
  },
  error: {
    icon: <AlertCircleIcon className="size-3.5" />,
    text: "Error",
    className: "text-destructive",
  },
}

function AutosaveIndicator({
  status,
  savingText,
  savedText,
  errorText,
  className,
}: AutosaveIndicatorProps) {
  const config = statusConfig[status]

  if (status === "idle") return null

  const text = status === "saving"
    ? (savingText ?? config.text)
    : status === "saved"
      ? (savedText ?? config.text)
      : (errorText ?? config.text)

  return (
    <span
      data-slot="autosave-indicator"
      data-status={status}
      className={cn(
        "inline-flex items-center gap-1 text-xs font-medium transition-all duration-200",
        config.className,
        className
      )}
      aria-live="polite"
      aria-atomic="true"
    >
      {config.icon}
      {text}
    </span>
  )
}

export { AutosaveIndicator }
export type { AutosaveStatus }
