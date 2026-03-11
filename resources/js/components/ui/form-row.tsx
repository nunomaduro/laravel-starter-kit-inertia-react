import * as React from "react"

import { cn } from "@/lib/utils"

interface FormRowProps {
  children: React.ReactNode
  className?: string
  /** Number of columns on small screens and up. Default 2. */
  cols?: 1 | 2 | 3
}

function FormRow({
  children,
  className,
  cols = 2,
}: FormRowProps) {
  return (
    <div
      data-slot="form-row"
      className={cn(
        "grid grid-cols-1 gap-4",
        cols === 2 && "sm:grid-cols-2",
        cols === 3 && "sm:grid-cols-3",
        className
      )}
    >
      {children}
    </div>
  )
}

export { FormRow }
