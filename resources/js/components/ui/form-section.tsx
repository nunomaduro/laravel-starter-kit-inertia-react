import * as React from "react"

import { cn } from "@/lib/utils"
import { Separator } from "@/components/ui/separator"

interface FormSectionProps {
  title?: string
  description?: string
  className?: string
  contentClassName?: string
  children: React.ReactNode
  actions?: React.ReactNode
  collapsible?: boolean
  defaultOpen?: boolean
}

function FormSection({
  title,
  description,
  className,
  contentClassName,
  children,
  actions,
  collapsible = false,
  defaultOpen = true,
}: FormSectionProps) {
  const [open, setOpen] = React.useState(defaultOpen)
  const hasHeader = title || description || actions

  return (
    <div data-slot="form-section" className={cn("space-y-4", className)}>
      {hasHeader && (
        <div className="flex items-start justify-between gap-4">
          <div className="space-y-0.5">
            {title && (
              <h3
                className={cn("text-base font-semibold", collapsible && "cursor-pointer")}
                onClick={collapsible ? () => setOpen((o) => !o) : undefined}
              >
                {collapsible && (
                  <span
                    className="mr-2 inline-block transition-transform duration-200"
                    style={{ transform: open ? "rotate(90deg)" : "rotate(0deg)" }}
                    aria-hidden
                  >
                    ›
                  </span>
                )}
                {title}
              </h3>
            )}
            {description && (
              <p className="text-muted-foreground text-sm">{description}</p>
            )}
          </div>
          {actions && <div className="shrink-0">{actions}</div>}
        </div>
      )}
      {hasHeader && <Separator />}
      {(!collapsible || open) && (
        <div className={cn("space-y-4", contentClassName)}>{children}</div>
      )}
    </div>
  )
}

export { FormSection }
