import * as React from "react"

import { cn } from "@/lib/utils"
import { FeedItem } from "@/components/ui/feed-item"

interface FeedItemData {
  id: string | number
  actor?: {
    name: string
    avatar?: string
    href?: string
  }
  action?: React.ReactNode
  target?: React.ReactNode
  timestamp?: React.ReactNode
  content?: React.ReactNode
  icon?: React.ReactNode
  iconClassName?: string
}

interface FeedProps {
  items?: FeedItemData[]
  className?: string
  children?: React.ReactNode
}

function Feed({ items, className, children }: FeedProps) {
  return (
    <ul
      data-slot="feed"
      className={cn("space-y-0", className)}
    >
      {items
        ? items.map((item, index) => (
            <FeedItem
              key={item.id}
              {...item}
              isLast={index === items.length - 1}
            />
          ))
        : children}
    </ul>
  )
}

export { Feed, FeedItem }
