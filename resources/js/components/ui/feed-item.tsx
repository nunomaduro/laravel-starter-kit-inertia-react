import * as React from "react"

import { cn } from "@/lib/utils"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"

interface FeedItemProps {
  id?: string | number
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
  isLast?: boolean
  className?: string
}

function FeedItem({
  actor,
  action,
  target,
  timestamp,
  content,
  icon,
  iconClassName,
  isLast = false,
  className,
}: FeedItemProps) {
  return (
    <li
      data-slot="feed-item"
      className={cn("relative flex gap-3 pb-6 last:pb-0", className)}
    >
      <div className="flex flex-col items-center">
        {icon ? (
          <div
            className={cn(
              "flex size-8 shrink-0 items-center justify-center rounded-full border bg-background text-muted-foreground",
              iconClassName
            )}
          >
            {icon}
          </div>
        ) : actor ? (
          <Avatar className="size-8 shrink-0">
            <AvatarImage src={actor.avatar} alt={actor.name} />
            <AvatarFallback className="text-xs">
              {actor.name
                .split(" ")
                .map((n) => n[0])
                .join("")
                .slice(0, 2)
                .toUpperCase()}
            </AvatarFallback>
          </Avatar>
        ) : (
          <div className="size-2 shrink-0 rounded-full bg-border mt-3" />
        )}
        {!isLast && <div className="mt-2 w-px flex-1 bg-border" />}
      </div>
      <div className="flex-1 pt-1">
        <p className="text-sm">
          {actor && (
            <span className="font-medium">
              {actor.href ? (
                <a href={actor.href} className="hover:underline">
                  {actor.name}
                </a>
              ) : (
                actor.name
              )}
            </span>
          )}
          {action && <span className="text-muted-foreground"> {action}</span>}
          {target && <span> {target}</span>}
        </p>
        {timestamp && (
          <p className="mt-0.5 text-xs text-muted-foreground">{timestamp}</p>
        )}
        {content && (
          <div className="mt-2 rounded-lg border bg-muted/50 px-3 py-2 text-sm">
            {content}
          </div>
        )}
      </div>
    </li>
  )
}

export { FeedItem }
