import * as React from "react"
import { PlusIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

export interface MindMapNode {
  id: string
  label: string
  children?: MindMapNode[]
  color?: string
  expanded?: boolean
}

interface MindMapProps {
  data: MindMapNode
  onNodeClick?: (node: MindMapNode) => void
  onAddChild?: (parentId: string) => void
  className?: string
}

function MindMap({ data, onNodeClick, onAddChild, className }: MindMapProps) {
  const [expandedIds, setExpandedIds] = React.useState<Set<string>>(
    () => new Set([data.id])
  )

  const toggleExpand = (id: string) => {
    setExpandedIds((prev) => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
      } else {
        next.add(id)
      }
      return next
    })
  }

  return (
    <div
      data-slot="mind-map"
      className={cn("inline-flex flex-col items-center", className)}
    >
      <MindMapNodeItem
        node={data}
        depth={0}
        expandedIds={expandedIds}
        onToggle={toggleExpand}
        onNodeClick={onNodeClick}
        onAddChild={onAddChild}
      />
    </div>
  )
}

interface MindMapNodeItemProps {
  node: MindMapNode
  depth: number
  expandedIds: Set<string>
  onToggle: (id: string) => void
  onNodeClick?: (node: MindMapNode) => void
  onAddChild?: (parentId: string) => void
}

function MindMapNodeItem({
  node,
  depth,
  expandedIds,
  onToggle,
  onNodeClick,
  onAddChild,
}: MindMapNodeItemProps) {
  const isExpanded = expandedIds.has(node.id)
  const hasChildren = Boolean(node.children?.length)

  const depthColors = [
    "bg-primary text-primary-foreground",
    "bg-primary/20 text-primary border border-primary/30",
    "bg-muted text-muted-foreground border border-border",
    "bg-background text-foreground border border-border",
  ]
  const colorClass = node.color
    ? ""
    : (depthColors[Math.min(depth, depthColors.length - 1)] ?? depthColors[0]!)

  return (
    <div className="flex flex-col items-center gap-2">
      <div className="group relative">
        <button
          type="button"
          className={cn(
            "min-w-20 rounded-lg px-3 py-1.5 text-sm font-medium transition-all hover:scale-105",
            colorClass,
            node.color ? "text-white" : ""
          )}
          style={node.color ? { backgroundColor: node.color } : {}}
          onClick={() => {
            if (hasChildren) onToggle(node.id)
            onNodeClick?.(node)
          }}
        >
          {node.label}
          {hasChildren && (
            <span className="ml-1.5 text-xs opacity-60">
              {isExpanded ? "−" : "+"}
            </span>
          )}
        </button>
        {onAddChild && (
          <Button
            variant="ghost"
            size="icon-xs"
            className="absolute -right-6 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100"
            onClick={() => onAddChild(node.id)}
          >
            <PlusIcon className="size-3" />
          </Button>
        )}
      </div>
      {hasChildren && isExpanded && (
        <div className="relative flex gap-8">
          <div className="absolute -top-2 left-1/2 h-2 w-px -translate-x-1/2 bg-border" />
          {node.children!.map((child, idx, arr) => (
            <div key={child.id} className="relative flex flex-col items-center">
              {arr.length > 1 && (
                <div
                  className="absolute -top-0.5 h-px bg-border"
                  style={{
                    left: idx === 0 ? "50%" : "0",
                    right: idx === arr.length - 1 ? "50%" : "0",
                  }}
                />
              )}
              <div className="mb-2 h-2 w-px bg-border" />
              <MindMapNodeItem
                node={child}
                depth={depth + 1}
                expandedIds={expandedIds}
                onToggle={onToggle}
                onNodeClick={onNodeClick}
                onAddChild={onAddChild}
              />
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

export { MindMap }
