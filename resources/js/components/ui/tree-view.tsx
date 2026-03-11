import * as React from "react"
import { ChevronRightIcon, FileIcon, FolderIcon, FolderOpenIcon } from "lucide-react"

import { cn } from "@/lib/utils"

export interface TreeNode {
  id: string
  name: string
  children?: TreeNode[]
  icon?: React.ReactNode
  data?: unknown
}

interface TreeViewProps {
  data: TreeNode[]
  defaultExpanded?: string[]
  selectedId?: string
  onSelect?: (node: TreeNode) => void
  className?: string
}

function TreeView({
  data,
  defaultExpanded = [],
  selectedId,
  onSelect,
  className,
}: TreeViewProps) {
  const [expanded, setExpanded] = React.useState<Set<string>>(
    () => new Set(defaultExpanded)
  )

  const toggleExpand = React.useCallback((id: string) => {
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
    <div
      data-slot="tree-view"
      role="tree"
      className={cn("select-none text-sm", className)}
    >
      <TreeNodeList
        nodes={data}
        expanded={expanded}
        selectedId={selectedId}
        onToggle={toggleExpand}
        onSelect={onSelect}
        depth={0}
      />
    </div>
  )
}

interface TreeNodeListProps {
  nodes: TreeNode[]
  expanded: Set<string>
  selectedId?: string
  onToggle: (id: string) => void
  onSelect?: (node: TreeNode) => void
  depth: number
}

function TreeNodeList({
  nodes,
  expanded,
  selectedId,
  onToggle,
  onSelect,
  depth,
}: TreeNodeListProps) {
  return (
    <ul role="group" className="space-y-0.5">
      {nodes.map((node) => (
        <TreeNodeItem
          key={node.id}
          node={node}
          expanded={expanded}
          selectedId={selectedId}
          onToggle={onToggle}
          onSelect={onSelect}
          depth={depth}
        />
      ))}
    </ul>
  )
}

interface TreeNodeItemProps {
  node: TreeNode
  expanded: Set<string>
  selectedId?: string
  onToggle: (id: string) => void
  onSelect?: (node: TreeNode) => void
  depth: number
}

function TreeNodeItem({
  node,
  expanded,
  selectedId,
  onToggle,
  onSelect,
  depth,
}: TreeNodeItemProps) {
  const hasChildren = Boolean(node.children?.length)
  const isExpanded = expanded.has(node.id)
  const isSelected = node.id === selectedId

  const handleClick = () => {
    if (hasChildren) {
      onToggle(node.id)
    }
    onSelect?.(node)
  }

  const DefaultIcon = hasChildren
    ? isExpanded
      ? FolderOpenIcon
      : FolderIcon
    : FileIcon

  return (
    <li role="treeitem" aria-expanded={hasChildren ? isExpanded : undefined}>
      <div
        className={cn(
          "flex items-center gap-1.5 rounded-sm px-2 py-1 hover:bg-accent cursor-pointer",
          isSelected && "bg-accent text-accent-foreground"
        )}
        style={{ paddingLeft: `${depth * 16 + 8}px` }}
        onClick={handleClick}
      >
        {hasChildren && (
          <ChevronRightIcon
            className={cn(
              "size-3.5 shrink-0 text-muted-foreground transition-transform",
              isExpanded && "rotate-90"
            )}
          />
        )}
        {!hasChildren && <span className="size-3.5" />}
        <span className="shrink-0 text-muted-foreground">
          {node.icon ?? <DefaultIcon className="size-4" />}
        </span>
        <span className="truncate">{node.name}</span>
      </div>
      {hasChildren && isExpanded && (
        <TreeNodeList
          nodes={node.children!}
          expanded={expanded}
          selectedId={selectedId}
          onToggle={onToggle}
          onSelect={onSelect}
          depth={depth + 1}
        />
      )}
    </li>
  )
}

export { TreeView }
