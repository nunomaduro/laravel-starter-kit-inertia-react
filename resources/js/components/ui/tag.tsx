import * as React from "react"
import { XIcon } from "lucide-react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const tagVariants = cva(
  "inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors",
  {
    variants: {
      variant: {
        default: "bg-primary/10 text-primary hover:bg-primary/20",
        secondary: "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        outline: "border border-input bg-transparent text-foreground hover:bg-accent",
        destructive: "bg-destructive/10 text-destructive hover:bg-destructive/20",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

interface TagProps extends React.HTMLAttributes<HTMLSpanElement>, VariantProps<typeof tagVariants> {
  onRemove?: () => void
  removable?: boolean
}

function Tag({ className, variant, onRemove, removable = false, children, ...props }: TagProps) {
  return (
    <span
      data-slot="tag"
      className={cn(tagVariants({ variant }), className)}
      {...props}
    >
      {children}
      {(removable || onRemove) && (
        <button
          type="button"
          onClick={(e) => {
            e.stopPropagation()
            onRemove?.()
          }}
          className="ml-0.5 -mr-1 inline-flex size-3.5 items-center justify-center rounded-full opacity-60 hover:opacity-100 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          aria-label="Remove"
        >
          <XIcon className="size-2.5" />
        </button>
      )}
    </span>
  )
}

export { Tag, tagVariants }
export type { TagProps }
