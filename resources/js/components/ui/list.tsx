import * as React from "react"

import { cn } from "@/lib/utils"

interface ListProps extends React.HTMLAttributes<HTMLUListElement> {
  divided?: boolean
  bordered?: boolean
  compact?: boolean
  flush?: boolean
}

function List({
  divided = false,
  bordered = false,
  compact = false,
  flush = false,
  className,
  children,
  ...props
}: ListProps) {
  return (
    <ul
      data-slot="list"
      className={cn(
        "overflow-hidden",
        !flush && "rounded-lg",
        bordered && "border",
        divided && "divide-y",
        compact && "text-xs",
        className
      )}
      {...props}
    >
      {children}
    </ul>
  )
}

export { List }
