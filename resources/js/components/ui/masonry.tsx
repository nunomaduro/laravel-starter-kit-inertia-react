import * as React from "react"

import { cn } from "@/lib/utils"

interface MasonryProps extends React.HTMLAttributes<HTMLDivElement> {
  columns?: number | { sm?: number; md?: number; lg?: number; xl?: number }
  gap?: number
}

const COLUMN_CLASS_MAP: Record<number, string> = {
  1: "columns-1",
  2: "columns-2",
  3: "columns-3",
  4: "columns-4",
  5: "columns-5",
  6: "columns-6",
  7: "columns-7",
  8: "columns-8",
  9: "columns-9",
  10: "columns-10",
}

const GAP_CLASS_MAP: Record<number, string> = {
  1: "gap-1",
  2: "gap-2",
  3: "gap-3",
  4: "gap-4",
  5: "gap-5",
  6: "gap-6",
  8: "gap-8",
}

function getColumnClass(
  columns: number | { sm?: number; md?: number; lg?: number; xl?: number }
): string {
  if (typeof columns === "number") {
    return COLUMN_CLASS_MAP[columns] ?? "columns-3"
  }

  const classes: string[] = []

  if (columns.sm) {
    classes.push(`sm:${COLUMN_CLASS_MAP[columns.sm] ?? "columns-2"}`)
  }
  if (columns.md) {
    classes.push(`md:${COLUMN_CLASS_MAP[columns.md] ?? "columns-3"}`)
  }
  if (columns.lg) {
    classes.push(`lg:${COLUMN_CLASS_MAP[columns.lg] ?? "columns-4"}`)
  }
  if (columns.xl) {
    classes.push(`xl:${COLUMN_CLASS_MAP[columns.xl] ?? "columns-5"}`)
  }

  return classes.join(" ") || "columns-3"
}

function Masonry({
  className,
  columns = 3,
  gap = 4,
  children,
  ...props
}: MasonryProps) {
  const columnClass = getColumnClass(columns)
  const gapClass = GAP_CLASS_MAP[gap] ?? "gap-4"

  return (
    <div
      data-slot="masonry"
      className={cn(columnClass, gapClass, className)}
      {...props}
    >
      {React.Children.map(children, (child, index) => (
        <div key={index} className="break-inside-avoid mb-4 last:mb-0">
          {child}
        </div>
      ))}
    </div>
  )
}

export { Masonry }
export type { MasonryProps }
