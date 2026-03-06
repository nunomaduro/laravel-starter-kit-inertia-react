import * as React from "react"
import { PlusIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"

export interface RoadmapItem {
  id: string | number
  title: string
  description?: string
  status: string
  priority?: "low" | "medium" | "high" | "critical"
  tags?: string[]
  votes?: number
  assignee?: string
}

export interface RoadmapLane {
  id: string
  title: string
  items: RoadmapItem[]
  color?: string
}

interface RoadmapProps {
  lanes: RoadmapLane[]
  onAddItem?: (laneId: string) => void
  onItemClick?: (item: RoadmapItem) => void
  className?: string
}

const priorityBadgeVariant: Record<string, "default" | "destructive" | "outline" | "secondary"> = {
  low: "secondary",
  medium: "outline",
  high: "default",
  critical: "destructive",
}

function Roadmap({ lanes, onAddItem, onItemClick, className }: RoadmapProps) {
  return (
    <div
      data-slot="roadmap"
      className={cn("flex gap-4 overflow-x-auto pb-4", className)}
    >
      {lanes.map((lane) => (
        <RoadmapLaneColumn
          key={lane.id}
          lane={lane}
          onAddItem={onAddItem}
          onItemClick={onItemClick}
        />
      ))}
    </div>
  )
}

function RoadmapLaneColumn({
  lane,
  onAddItem,
  onItemClick,
}: {
  lane: RoadmapLane
  onAddItem?: (laneId: string) => void
  onItemClick?: (item: RoadmapItem) => void
}) {
  return (
    <div
      data-slot="roadmap-lane"
      className="flex w-72 shrink-0 flex-col gap-3"
    >
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          {lane.color && (
            <div
              className="size-2.5 rounded-full"
              style={{ backgroundColor: lane.color }}
            />
          )}
          <h3 className="text-sm font-semibold">{lane.title}</h3>
          <Badge variant="secondary" className="text-xs">
            {lane.items.length}
          </Badge>
        </div>
        {onAddItem && (
          <Button
            variant="ghost"
            size="icon-xs"
            onClick={() => onAddItem(String(lane.id))}
          >
            <PlusIcon className="size-3.5" />
          </Button>
        )}
      </div>
      <div className="flex flex-col gap-2">
        {lane.items.map((item) => (
          <RoadmapCard
            key={item.id}
            item={item}
            onClick={onItemClick}
          />
        ))}
      </div>
    </div>
  )
}

function RoadmapCard({
  item,
  onClick,
}: {
  item: RoadmapItem
  onClick?: (item: RoadmapItem) => void
}) {
  return (
    <Card
      data-slot="roadmap-card"
      className={cn(
        "transition-shadow",
        onClick && "cursor-pointer hover:shadow-md"
      )}
      onClick={() => onClick?.(item)}
    >
      <CardHeader className="pb-2">
        <CardTitle className="text-sm font-medium">{item.title}</CardTitle>
      </CardHeader>
      {(item.description || item.priority || item.tags?.length) && (
        <CardContent className="pb-3 pt-0">
          {item.description && (
            <p className="mb-2 text-xs text-muted-foreground line-clamp-2">
              {item.description}
            </p>
          )}
          <div className="flex flex-wrap gap-1">
            {item.priority && (
              <Badge
                variant={priorityBadgeVariant[item.priority] ?? "secondary"}
                className="text-xs"
              >
                {item.priority}
              </Badge>
            )}
            {item.tags?.map((tag) => (
              <Badge key={tag} variant="outline" className="text-xs">
                {tag}
              </Badge>
            ))}
          </div>
        </CardContent>
      )}
    </Card>
  )
}

export { Roadmap }
