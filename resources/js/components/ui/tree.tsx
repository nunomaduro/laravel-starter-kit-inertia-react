import * as React from "react"
import { ChevronRightIcon } from "lucide-react"

import { cn } from "@/lib/utils"

export interface TreeNode {
  id: string | number
  label: React.ReactNode
  icon?: React.ReactNode
  children?: TreeNode[]
  disabled?: boolean
}

interface TreeProps {
  nodes: TreeNode[]
  selected?: string | number
  expanded?: (string | number)[]
  onSelect?: (id: string | number) => void
  onToggle?: (id: string | number, expanded: boolean) => void
  className?: string
  defaultExpanded?: (string | number)[]
}

function Tree({
  nodes,
  selected,
  expanded: controlledExpanded,
  onSelect,
  onToggle,
  className,
  defaultExpanded = [],
}: TreeProps) {
  const [internalExpanded, setInternalExpanded] = React.useState<Set<string | number>>(
    new Set(defaultExpanded)
  )

  const isExpanded = (id: string | number) =>
    controlledExpanded ? controlledExpanded.includes(id) : internalExpanded.has(id)

  const handleToggle = (id: string | number) => {
    const next = !isExpanded(id)
    if (!controlledExpanded) {
      setInternalExpanded((prev) => {
        const s = new Set(prev)
        if (next) s.add(id)
        else s.delete(id)
        return s
      })
    }
    onToggle?.(id, next)
  }

  return (
    <ul
      data-slot="tree"
      role="tree"
      className={cn("space-y-0.5", className)}
    >
      {nodes.map((node) => (
        <TreeNodeComponent
          key={node.id}
          node={node}
          selected={selected}
          isExpanded={isExpanded}
          onSelect={onSelect}
          onToggle={handleToggle}
          depth={0}
        />
      ))}
    </ul>
  )
}

function TreeNodeComponent({
  node,
  selected,
  isExpanded,
  onSelect,
  onToggle,
  depth,
}: {
  node: TreeNode
  selected?: string | number
  isExpanded: (id: string | number) => boolean
  onSelect?: (id: string | number) => void
  onToggle: (id: string | number) => void
  depth: number
}) {
  const hasChildren = (node.children?.length ?? 0) > 0
  const expanded = isExpanded(node.id)
  const isSelected = selected === node.id

  return (
    <li role="treeitem" aria-expanded={hasChildren ? expanded : undefined}>
      <button
        type="button"
        disabled={node.disabled}
        className={cn(
          "flex w-full items-center gap-1.5 rounded-md px-2 py-1.5 text-left text-sm transition-colors",
          "hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
          isSelected && "bg-accent text-accent-foreground font-medium",
          node.disabled && "pointer-events-none opacity-50"
        )}
        style={{ paddingLeft: `${depth * 16 + 8}px` }}
        onClick={() => {
          if (hasChildren) onToggle(node.id)
          onSelect?.(node.id)
        }}
      >
        {hasChildren ? (
          <ChevronRightIcon
            className={cn(
              "size-3.5 shrink-0 text-muted-foreground transition-transform",
              expanded && "rotate-90"
            )}
          />
        ) : (
          <span className="size-3.5 shrink-0" />
        )}
        {node.icon && <span className="shrink-0">{node.icon}</span>}
        <span className="truncate">{node.label}</span>
      </button>
      {hasChildren && expanded && (
        <ul role="group">
          {node.children!.map((child) => (
            <TreeNodeComponent
              key={child.id}
              node={child}
              selected={selected}
              isExpanded={isExpanded}
              onSelect={onSelect}
              onToggle={onToggle}
              depth={depth + 1}
            />
          ))}
        </ul>
      )}
    </li>
  )
}

export { Tree }
