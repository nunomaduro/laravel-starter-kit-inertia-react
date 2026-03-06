import * as React from "react"

import { cn } from "@/lib/utils"

export interface TocItem {
  id: string
  label: string
  level: number
  children?: TocItem[]
}

interface TocProps extends React.HTMLAttributes<HTMLElement> {
  items: TocItem[]
  activeId?: string
  title?: string
  offsetPx?: number
  onItemClick?: (id: string) => void
}

function Toc({
  className,
  items,
  activeId: activeIdProp,
  title = "On this page",
  offsetPx = 80,
  onItemClick,
  ...props
}: TocProps) {
  const [activeId, setActiveId] = React.useState<string | null>(activeIdProp ?? null)

  React.useEffect(() => {
    if (activeIdProp !== undefined) return

    const allIds = flattenItems(items).map((item) => item.id)
    const elements = allIds
      .map((id) => document.getElementById(id))
      .filter((el): el is HTMLElement => el !== null)

    if (elements.length === 0) return

    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((e) => e.isIntersecting)
          .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)

        if (visible.length > 0) {
          setActiveId(visible[0].target.id)
        }
      },
      {
        rootMargin: `-${offsetPx}px 0px -66% 0px`,
        threshold: 0,
      }
    )

    elements.forEach((el) => observer.observe(el))
    return () => observer.disconnect()
  }, [items, activeIdProp, offsetPx])

  const effectiveActiveId = activeIdProp ?? activeId

  const handleItemClick = (id: string) => {
    onItemClick?.(id)
    const el = document.getElementById(id)
    if (el) {
      const top = el.getBoundingClientRect().top + window.scrollY - offsetPx
      window.scrollTo({ top, behavior: "smooth" })
    }
    setActiveId(id)
  }

  return (
    <nav
      data-slot="toc"
      aria-label="Table of contents"
      className={cn("w-full", className)}
      {...props}
    >
      {title && (
        <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          {title}
        </p>
      )}
      <TocList
        items={items}
        activeId={effectiveActiveId}
        onItemClick={handleItemClick}
        depth={0}
      />
    </nav>
  )
}

function TocList({
  items,
  activeId,
  onItemClick,
  depth,
}: {
  items: TocItem[]
  activeId: string | null
  onItemClick: (id: string) => void
  depth: number
}) {
  return (
    <ul className="space-y-1">
      {items.map((item) => (
        <li key={item.id}>
          <button
            onClick={() => onItemClick(item.id)}
            className={cn(
              "w-full truncate rounded px-2 py-1 text-left text-sm transition-colors",
              "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
              item.id === activeId
                ? "font-medium text-primary"
                : "text-muted-foreground hover:text-foreground"
            )}
            style={{ paddingLeft: `${(depth + 1) * 0.75}rem` }}
          >
            {item.label}
          </button>
          {item.children && item.children.length > 0 && (
            <TocList
              items={item.children}
              activeId={activeId}
              onItemClick={onItemClick}
              depth={depth + 1}
            />
          )}
        </li>
      ))}
    </ul>
  )
}

function flattenItems(items: TocItem[]): TocItem[] {
  return items.flatMap((item) => [
    item,
    ...(item.children ? flattenItems(item.children) : []),
  ])
}

export { Toc, TocList }
export type { TocProps }
