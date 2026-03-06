import * as React from "react"

import { cn } from "@/lib/utils"

interface DescriptionListItem {
  term: React.ReactNode
  detail: React.ReactNode
  id?: string | number
}

type DescriptionListLayout = "stacked" | "inline" | "grid"

interface DescriptionListProps extends React.HTMLAttributes<HTMLDListElement> {
  items?: DescriptionListItem[]
  layout?: DescriptionListLayout
  termClassName?: string
  detailClassName?: string
  divided?: boolean
}

function DescriptionList({
  items,
  layout = "stacked",
  termClassName,
  detailClassName,
  divided = false,
  className,
  children,
  ...props
}: DescriptionListProps) {
  const isGrid = layout === "grid"
  const isInline = layout === "inline"

  return (
    <dl
      data-slot="description-list"
      className={cn(
        isGrid && "grid grid-cols-[auto_1fr] gap-x-6 gap-y-3",
        divided && !isGrid && "divide-y",
        className
      )}
      {...props}
    >
      {items
        ? items.map((item, i) => (
            <DescriptionListRow
              key={item.id ?? i}
              term={item.term}
              detail={item.detail}
              layout={layout}
              divided={divided}
              termClassName={termClassName}
              detailClassName={detailClassName}
            />
          ))
        : children}
    </dl>
  )
}

interface DescriptionListRowProps {
  term: React.ReactNode
  detail: React.ReactNode
  layout?: DescriptionListLayout
  divided?: boolean
  termClassName?: string
  detailClassName?: string
}

function DescriptionListRow({
  term,
  detail,
  layout = "stacked",
  divided = false,
  termClassName,
  detailClassName,
}: DescriptionListRowProps) {
  const isGrid = layout === "grid"
  const isInline = layout === "inline"

  if (isGrid) {
    return (
      <>
        <dt className={cn("text-sm font-medium text-muted-foreground", termClassName)}>
          {term}
        </dt>
        <dd className={cn("text-sm", detailClassName)}>{detail}</dd>
      </>
    )
  }

  if (isInline) {
    return (
      <div
        className={cn(
          "flex items-baseline justify-between gap-4 py-2.5",
          divided && "border-t first:border-t-0"
        )}
      >
        <dt className={cn("shrink-0 text-sm font-medium text-muted-foreground", termClassName)}>
          {term}
        </dt>
        <dd className={cn("text-right text-sm", detailClassName)}>{detail}</dd>
      </div>
    )
  }

  return (
    <div
      className={cn(
        "py-2.5",
        divided && "border-t first:border-t-0"
      )}
    >
      <dt className={cn("text-xs font-medium uppercase tracking-wider text-muted-foreground", termClassName)}>
        {term}
      </dt>
      <dd className={cn("mt-1 text-sm", detailClassName)}>{detail}</dd>
    </div>
  )
}

DescriptionList.Row = DescriptionListRow

export { DescriptionList }
