import * as React from "react"
import { PlusIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

interface FabAction {
  icon: React.ReactNode
  label: string
  onClick?: () => void
}

interface FabProps extends Omit<React.ComponentProps<"button">, "color"> {
  icon?: React.ReactNode
  label?: string
  /** Speed-dial actions shown when the FAB is expanded. */
  actions?: FabAction[]
  position?: "bottom-right" | "bottom-left" | "top-right" | "top-left"
}

const positionClasses: Record<NonNullable<FabProps["position"]>, string> = {
  "bottom-right": "bottom-6 right-6",
  "bottom-left": "bottom-6 left-6",
  "top-right": "top-6 right-6",
  "top-left": "top-6 left-6",
}

function Fab({
  className,
  icon,
  label = "Open actions",
  actions,
  position = "bottom-right",
  onClick,
  ...props
}: FabProps) {
  const [open, setOpen] = React.useState(false)
  const hasActions = actions && actions.length > 0

  const handleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
    if (hasActions) {
      setOpen((prev) => !prev)
    }
    onClick?.(e)
  }

  return (
    <div
      data-slot="fab"
      className={cn("fixed z-50 flex flex-col-reverse items-center gap-3", positionClasses[position], className)}
    >
      {/* Speed-dial actions */}
      {hasActions && open && (
        <div className="flex flex-col-reverse items-center gap-2">
          {actions!.map((action, idx) => (
            <div key={idx} className="flex items-center gap-3">
              <span className="rounded-md bg-popover px-2 py-1 text-xs text-popover-foreground shadow-md whitespace-nowrap">
                {action.label}
              </span>
              <button
                type="button"
                aria-label={action.label}
                title={action.label}
                onClick={() => {
                  action.onClick?.()
                  setOpen(false)
                }}
                className="inline-flex size-10 items-center justify-center rounded-full bg-background text-foreground shadow-md ring-1 ring-border transition-all hover:bg-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              >
                {action.icon}
              </button>
            </div>
          ))}
        </div>
      )}

      {/* Main FAB button */}
      <Button
        type="button"
        aria-label={label}
        aria-expanded={hasActions ? open : undefined}
        size="icon"
        className={cn("size-14 rounded-full shadow-lg [&_svg:not([class*='size-'])]:size-6", props.disabled && "opacity-50 pointer-events-none")}
        onClick={handleClick}
        {...props}
      >
        {hasActions ? (
          open ? <XIcon /> : (icon ?? <PlusIcon />)
        ) : (
          (icon ?? <PlusIcon />)
        )}
      </Button>
    </div>
  )
}

export { Fab }
export type { FabProps, FabAction }
