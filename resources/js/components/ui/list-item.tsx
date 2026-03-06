import * as React from "react"

import { cn } from "@/lib/utils"

interface ListItemProps extends Omit<React.LiHTMLAttributes<HTMLLIElement>, "title"> {
  title?: React.ReactNode
  description?: React.ReactNode
  leading?: React.ReactNode
  trailing?: React.ReactNode
  href?: string
  onClick?: () => void
  active?: boolean
  disabled?: boolean
}

function ListItem({
  title,
  description,
  leading,
  trailing,
  href,
  onClick,
  active = false,
  disabled = false,
  className,
  children,
  ...props
}: ListItemProps) {
  const Tag: React.ElementType = href ? "a" : onClick ? "button" : "li"
  const isInteractive = Boolean(href || onClick)

  const content = (
    <>
      {leading && (
        <div className="shrink-0 text-muted-foreground">{leading}</div>
      )}
      <div className="min-w-0 flex-1">
        {title && <div className="truncate text-sm font-medium leading-none">{title}</div>}
        {description && (
          <div className="mt-1 truncate text-xs text-muted-foreground">{description}</div>
        )}
        {children}
      </div>
      {trailing && (
        <div className="shrink-0 text-muted-foreground">{trailing}</div>
      )}
    </>
  )

  if (isInteractive) {
    return (
      <li className={cn("list-none", className)} {...props}>
        <Tag
          href={href}
          type={Tag === "button" ? "button" : undefined}
          onClick={onClick}
          disabled={Tag === "button" ? disabled : undefined}
          aria-current={active ? "true" : undefined}
          className={cn(
            "flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors",
            "hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
            active && "bg-accent text-accent-foreground",
            disabled && "pointer-events-none opacity-50"
          )}
        >
          {content}
        </Tag>
      </li>
    )
  }

  return (
    <li
      data-slot="list-item"
      className={cn(
        "flex items-center gap-3 px-3 py-2.5 text-sm",
        active && "bg-accent",
        disabled && "opacity-50",
        className
      )}
      {...props}
    >
      {content}
    </li>
  )
}

export { ListItem }
