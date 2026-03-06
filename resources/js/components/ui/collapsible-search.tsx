import * as React from "react"
import { SearchIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"

interface CollapsibleSearchProps extends React.HTMLAttributes<HTMLDivElement> {
  placeholder?: string
  value?: string
  onValueChange?: (value: string) => void
  defaultOpen?: boolean
  expandDirection?: "left" | "right"
}

function CollapsibleSearch({
  className,
  placeholder = "Search…",
  value: valueProp,
  onValueChange,
  defaultOpen = false,
  expandDirection = "left",
  ...props
}: CollapsibleSearchProps) {
  const [isOpen, setIsOpen] = React.useState(defaultOpen)
  const [value, setValue] = React.useState(valueProp ?? "")
  const inputRef = React.useRef<HTMLInputElement>(null)

  const isControlled = valueProp !== undefined
  const currentValue = isControlled ? valueProp : value

  const handleOpen = () => {
    setIsOpen(true)
    requestAnimationFrame(() => inputRef.current?.focus())
  }

  const handleClose = () => {
    setIsOpen(false)
    const newValue = ""
    if (!isControlled) setValue(newValue)
    onValueChange?.(newValue)
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newValue = e.target.value
    if (!isControlled) setValue(newValue)
    onValueChange?.(newValue)
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Escape") {
      handleClose()
    }
  }

  return (
    <div
      data-slot="collapsible-search"
      data-open={isOpen || undefined}
      className={cn("relative flex items-center", className)}
      {...props}
    >
      <div
        className={cn(
          "flex items-center overflow-hidden rounded-full border bg-background transition-all duration-200",
          isOpen ? "w-64 border-ring shadow-sm" : "w-9 border-transparent",
          expandDirection === "right" ? "flex-row" : "flex-row-reverse"
        )}
      >
        <button
          type="button"
          onClick={isOpen ? undefined : handleOpen}
          aria-label="Open search"
          className={cn(
            "flex size-9 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors",
            "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1",
            !isOpen && "hover:bg-accent hover:text-foreground"
          )}
        >
          <SearchIcon className="size-4" />
        </button>
        <input
          ref={inputRef}
          type="search"
          value={currentValue}
          onChange={handleChange}
          onKeyDown={handleKeyDown}
          placeholder={placeholder}
          className={cn(
            "w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground",
            expandDirection === "right" ? "pr-2" : "pl-2",
            !isOpen && "pointer-events-none"
          )}
          aria-hidden={!isOpen}
          tabIndex={isOpen ? 0 : -1}
        />
        {isOpen && currentValue && (
          <button
            type="button"
            onClick={handleClose}
            aria-label="Clear search"
            className={cn(
              "flex size-9 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors",
              "hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            )}
          >
            <XIcon className="size-3.5" />
          </button>
        )}
      </div>
    </div>
  )
}

export { CollapsibleSearch }
export type { CollapsibleSearchProps }
