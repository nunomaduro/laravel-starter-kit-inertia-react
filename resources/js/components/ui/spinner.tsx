import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { Loader2Icon } from "lucide-react"

import { cn } from "@/lib/utils"

const spinnerVariants = cva("animate-spin", {
  variants: {
    variant: {
      default: "text-primary",
      muted: "text-muted-foreground",
      white: "text-white",
      inherit: "text-current",
    },
    size: {
      xs: "size-3",
      sm: "size-4",
      md: "size-6",
      lg: "size-8",
      xl: "size-12",
    },
  },
  defaultVariants: {
    variant: "default",
    size: "md",
  },
})

interface SpinnerProps extends VariantProps<typeof spinnerVariants> {
  className?: string
  label?: string
}

function Spinner({ variant, size, className, label = "Loading..." }: SpinnerProps) {
  return (
    <span data-slot="spinner" role="status" aria-label={label}>
      <Loader2Icon
        className={cn(spinnerVariants({ variant, size }), className)}
        aria-hidden="true"
      />
      <span className="sr-only">{label}</span>
    </span>
  )
}

interface SpinnerOverlayProps extends SpinnerProps {
  visible?: boolean
}

function SpinnerOverlay({ visible = true, ...spinnerProps }: SpinnerOverlayProps) {
  if (!visible) return null

  return (
    <div
      data-slot="spinner-overlay"
      className="absolute inset-0 z-10 flex items-center justify-center rounded-[inherit] bg-background/60 backdrop-blur-sm"
    >
      <Spinner {...spinnerProps} />
    </div>
  )
}

export { Spinner, SpinnerOverlay, spinnerVariants }
