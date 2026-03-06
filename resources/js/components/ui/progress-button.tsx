import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { Loader2Icon } from "lucide-react"

import { cn } from "@/lib/utils"

type ProgressButtonState = "idle" | "loading" | "success" | "error"

const progressButtonVariants = cva(
  "relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground hover:bg-primary/90",
        outline: "border bg-background hover:bg-accent hover:text-accent-foreground",
        ghost: "hover:bg-accent hover:text-accent-foreground",
      },
      size: {
        default: "h-9 px-4 py-2",
        sm: "h-8 rounded-md px-3",
        lg: "h-10 rounded-md px-6",
        icon: "size-9",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

interface ProgressButtonProps
  extends React.ComponentProps<"button">,
    VariantProps<typeof progressButtonVariants> {
  state?: ProgressButtonState
  progress?: number
  successLabel?: React.ReactNode
  errorLabel?: React.ReactNode
  loadingLabel?: React.ReactNode
}

function ProgressButton({
  className,
  variant,
  size,
  state = "idle",
  progress,
  children,
  successLabel,
  errorLabel,
  loadingLabel,
  disabled,
  ...props
}: ProgressButtonProps) {
  const isDisabled = disabled || state === "loading"

  const label = React.useMemo(() => {
    if (state === "loading") return loadingLabel ?? children
    if (state === "success") return successLabel ?? children
    if (state === "error") return errorLabel ?? children
    return children
  }, [state, children, loadingLabel, successLabel, errorLabel])

  return (
    <button
      data-slot="progress-button"
      data-state={state}
      disabled={isDisabled}
      className={cn(progressButtonVariants({ variant, size }), className)}
      {...props}
    >
      {progress !== undefined && (
        <span
          className="absolute inset-0 origin-left bg-white/20 transition-transform duration-300"
          style={{ transform: `scaleX(${progress / 100})` }}
        />
      )}
      {state === "loading" && (
        <Loader2Icon className="size-4 animate-spin" />
      )}
      <span className="relative">{label}</span>
    </button>
  )
}

export { ProgressButton }
export type { ProgressButtonState }
