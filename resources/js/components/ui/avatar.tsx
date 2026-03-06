import * as React from "react"
import * as AvatarPrimitive from "@radix-ui/react-avatar"

import { cn } from "@/lib/utils"

type IndicatorStatus = "online" | "offline" | "busy" | "away"

const indicatorStatusClasses: Record<IndicatorStatus, string> = {
  online: "bg-success",
  offline: "bg-muted-foreground",
  busy: "bg-error",
  away: "bg-warning",
}

const nameColors = [
  "bg-primary/20 text-primary",
  "bg-info/20 text-info",
  "bg-success/20 text-success",
  "bg-warning/20 text-warning",
  "bg-error/20 text-error",
  "bg-secondary/20 text-secondary",
  "bg-purple-500/20 text-purple-600 dark:text-purple-400",
  "bg-pink-500/20 text-pink-600 dark:text-pink-400",
]

function getNameColor(name: string): string {
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return nameColors[Math.abs(hash) % nameColors.length]
}

function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.slice(0, 2).toUpperCase()
}

function Avatar({
  className,
  indicator,
  ...props
}: React.ComponentProps<typeof AvatarPrimitive.Root> & {
  indicator?: IndicatorStatus
}) {
  return (
    <AvatarPrimitive.Root
      data-slot="avatar"
      className={cn(
        "relative flex size-8 shrink-0 overflow-hidden rounded-full",
        className
      )}
      {...props}
    >
      {props.children}
      {indicator && (
        <span
          data-slot="avatar-indicator"
          className={cn(
            "absolute right-0 bottom-0 size-2.5 rounded-full border-2 border-background",
            indicatorStatusClasses[indicator]
          )}
        />
      )}
    </AvatarPrimitive.Root>
  )
}

function AvatarImage({
  className,
  ...props
}: React.ComponentProps<typeof AvatarPrimitive.Image>) {
  return (
    <AvatarPrimitive.Image
      data-slot="avatar-image"
      className={cn("aspect-square size-full", className)}
      {...props}
    />
  )
}

function AvatarFallback({
  className,
  name,
  ...props
}: React.ComponentProps<typeof AvatarPrimitive.Fallback> & {
  name?: string
}) {
  const autoColorClass = name ? getNameColor(name) : undefined

  return (
    <AvatarPrimitive.Fallback
      data-slot="avatar-fallback"
      className={cn(
        "flex size-full items-center justify-center rounded-full text-xs font-medium",
        autoColorClass ?? "bg-muted",
        className
      )}
      {...props}
    >
      {props.children ?? (name ? getInitials(name) : null)}
    </AvatarPrimitive.Fallback>
  )
}

function AvatarGroup({
  className,
  max,
  children,
  ...props
}: React.ComponentProps<"div"> & { max?: number }) {
  const childArray = React.Children.toArray(children)
  const visible = max ? childArray.slice(0, max) : childArray
  const overflow = max ? childArray.length - max : 0

  return (
    <div
      data-slot="avatar-group"
      className={cn("flex -space-x-2", className)}
      {...props}
    >
      {visible}
      {overflow > 0 && (
        <span className="relative flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-background bg-muted text-xs font-medium text-muted-foreground">
          +{overflow}
        </span>
      )}
    </div>
  )
}

export { Avatar, AvatarImage, AvatarFallback, AvatarGroup }
