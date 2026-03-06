import * as React from "react"

import { cn } from "@/lib/utils"

export interface TimelineItem {
  id: string | number
  title: string
  description?: string
  date?: string
  icon?: React.ReactNode
  iconClassName?: string
  variant?: "default" | "success" | "warning" | "error" | "info"
}

interface TimelineProps {
  items: TimelineItem[]
  className?: string
}

function Timeline({ items, className }: TimelineProps) {
  return (
    <div
      data-slot="timeline"
      className={cn("relative flex flex-col gap-0", className)}
    >
      {items.map((item, index) => (
        <TimelineItemComponent
          key={item.id}
          item={item}
          isLast={index === items.length - 1}
        />
      ))}
    </div>
  )
}

const variantMap = {
  default: "bg-muted-foreground text-background",
  success: "bg-success text-success-foreground",
  warning: "bg-warning text-warning-foreground",
  error: "bg-error text-error-foreground",
  info: "bg-info text-info-foreground",
} as const

function TimelineItemComponent({
  item,
  isLast,
}: {
  item: TimelineItem
  isLast: boolean
}) {
  const variant = item.variant ?? "default"

  return (
    <div
      data-slot="timeline-item"
      className="relative flex gap-4 pb-6 last:pb-0"
    >
      <div className="flex flex-col items-center">
        <div
          className={cn(
            "flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-medium",
            variantMap[variant],
            item.iconClassName
          )}
        >
          {item.icon}
        </div>
        {!isLast && (
          <div className="mt-2 w-px flex-1 bg-border" />
        )}
      </div>
      <div className="flex flex-col gap-0.5 pt-1">
        <div className="flex items-baseline gap-2">
          <span className="text-sm font-medium leading-none">{item.title}</span>
          {item.date && (
            <span className="text-xs text-muted-foreground">{item.date}</span>
          )}
        </div>
        {item.description && (
          <p className="text-sm text-muted-foreground">{item.description}</p>
        )}
      </div>
    </div>
  )
}

export { Timeline }
