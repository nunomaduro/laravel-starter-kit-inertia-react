import * as React from "react"
import { ChevronRightIcon, FolderIcon, FileIcon } from "lucide-react"

import { cn } from "@/lib/utils"

export interface TreeNavNode {
  id: string
  label: string
  icon?: React.ReactNode
  href?: string
  badge?: string | number
  children?: TreeNavNode[]
  disabled?: boolean
}

interface TreeNavContextValue {
  selected: string | null
  expanded: Set<string>
  onSelect: (id: string, href?: string) => void
  onToggle: (id: string) => void
}

const TreeNavContext = React.createContext<TreeNavContextValue>({
  selected: null,
  expanded: new Set(),
  onSelect: () => {},
  onToggle: () => {},
})

interface TreeNavProps extends Omit<React.HTMLAttributes<HTMLElement>, "onSelect"> {
  nodes: TreeNavNode[]
  selected?: string
  defaultExpanded?: string[]
  onSelect?: (id: string, href?: string) => void
}

function TreeNav({
  className,
  nodes,
  selected: selectedProp,
  defaultExpanded = [],
  onSelect,
  ...props
}: TreeNavProps) {
  const [selected, setSelected] = React.useState<string | null>(selectedProp ?? null)
  const [expanded, setExpanded] = React.useState<Set<string>>(
    () => new Set(defaultExpanded)
  )

  const handleSelect = React.useCallback(
    (id: string, href?: string) => {
      setSelected(id)
      onSelect?.(id, href)
    },
    [onSelect]
  )

  const handleToggle = React.useCallback((id: string) => {
    setExpanded((prev) => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
      } else {
        next.add(id)
      }
      return next
    })
  }, [])

  return (
    <TreeNavContext value={{ selected, expanded, onSelect: handleSelect, onToggle: handleToggle }}>
      <nav
        data-slot="tree-nav"
        className={cn("w-full", className)}
        {...props}
      >
        <TreeNavList nodes={nodes} depth={0} />
      </nav>
    </TreeNavContext>
  )
}

function TreeNavList({
  nodes,
  depth,
}: {
  nodes: TreeNavNode[]
  depth: number
}) {
  return (
    <ul role="tree" className="space-y-0.5">
      {nodes.map((node) => (
        <TreeNavNodeItem key={node.id} node={node} depth={depth} />
      ))}
    </ul>
  )
}

function TreeNavNodeItem({
  node,
  depth,
}: {
  node: TreeNavNode
  depth: number
}) {
  const { selected, expanded, onSelect, onToggle } = React.use(TreeNavContext)
  const hasChildren = node.children && node.children.length > 0
  const isExpanded = expanded.has(node.id)
  const isSelected = selected === node.id

  const handleClick = () => {
    if (node.disabled) return
    if (hasChildren) {
      onToggle(node.id)
    } else {
      onSelect(node.id, node.href)
    }
  }

  return (
    <li role="treeitem" aria-expanded={hasChildren ? isExpanded : undefined}>
      <button
        onClick={handleClick}
        disabled={node.disabled}
        className={cn(
          "flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors",
          "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
          "disabled:pointer-events-none disabled:opacity-50",
          isSelected
            ? "bg-accent text-accent-foreground font-medium"
            : "text-muted-foreground hover:bg-accent/50 hover:text-foreground"
        )}
        style={{ paddingLeft: `${(depth + 1) * 0.75}rem` }}
      >
        {hasChildren ? (
          <ChevronRightIcon
            className={cn(
              "size-3.5 shrink-0 transition-transform",
              isExpanded && "rotate-90"
            )}
          />
        ) : (
          <span className="size-3.5 shrink-0" />
        )}
        <span className="shrink-0 [&>svg]:size-4">
          {node.icon ?? (hasChildren ? <FolderIcon /> : <FileIcon />)}
        </span>
        <span className="flex-1 truncate text-left">{node.label}</span>
        {node.badge !== undefined && (
          <span className="ml-auto shrink-0 rounded-full bg-primary/10 px-1.5 py-0.5 text-xs font-medium text-primary">
            {node.badge}
          </span>
        )}
      </button>
      {hasChildren && isExpanded && (
        <TreeNavList nodes={node.children!} depth={depth + 1} />
      )}
    </li>
  )
}

export { TreeNav, TreeNavList, TreeNavNodeItem }
export type { TreeNavProps }
