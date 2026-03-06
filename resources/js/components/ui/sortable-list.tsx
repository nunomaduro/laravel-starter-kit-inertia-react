import * as React from "react"
import {
  closestCenter,
  DndContext,
  type DragEndEvent,
  DragOverlay,
  type DragStartEvent,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from "@dnd-kit/core"
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  useSortable,
  verticalListSortingStrategy,
} from "@dnd-kit/sortable"
import { CSS } from "@dnd-kit/utilities"
import { GripVerticalIcon } from "lucide-react"

import { cn } from "@/lib/utils"

export interface SortableListItem {
  id: string
  label: React.ReactNode
  description?: React.ReactNode
  icon?: React.ReactNode
}

interface SortableListProps {
  items: SortableListItem[]
  onReorder: (items: SortableListItem[]) => void
  className?: string
  renderItem?: (item: SortableListItem, dragHandle: React.ReactNode) => React.ReactNode
}

function SortableList({ items, onReorder, className, renderItem }: SortableListProps) {
  const [activeId, setActiveId] = React.useState<string | null>(null)
  const activeItem = items.find((i) => i.id === activeId)

  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  )

  const handleDragStart = ({ active }: DragStartEvent) => {
    setActiveId(String(active.id))
  }

  const handleDragEnd = ({ active, over }: DragEndEvent) => {
    setActiveId(null)
    if (over && active.id !== over.id) {
      const oldIndex = items.findIndex((i) => i.id === active.id)
      const newIndex = items.findIndex((i) => i.id === over.id)
      onReorder(arrayMove(items, oldIndex, newIndex))
    }
  }

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragStart={handleDragStart}
      onDragEnd={handleDragEnd}
    >
      <SortableContext items={items.map((i) => i.id)} strategy={verticalListSortingStrategy}>
        <ul data-slot="sortable-list" className={cn("space-y-1", className)}>
          {items.map((item) => (
            <SortableListItemRow
              key={item.id}
              item={item}
              renderItem={renderItem}
            />
          ))}
        </ul>
      </SortableContext>
      <DragOverlay>
        {activeItem ? (
          <div className="opacity-90 shadow-xl">
            <DefaultRow item={activeItem} dragHandle={<GripVerticalIcon className="size-4" />} />
          </div>
        ) : null}
      </DragOverlay>
    </DndContext>
  )
}

function SortableListItemRow({
  item,
  renderItem,
}: {
  item: SortableListItem
  renderItem?: (item: SortableListItem, dragHandle: React.ReactNode) => React.ReactNode
}) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: item.id })

  const style: React.CSSProperties = {
    transform: CSS.Transform.toString(transform),
    transition,
  }

  const dragHandle = (
    <button
      type="button"
      className="cursor-grab text-muted-foreground hover:text-foreground active:cursor-grabbing"
      aria-label="Drag to reorder"
      {...attributes}
      {...listeners}
    >
      <GripVerticalIcon className="size-4" />
    </button>
  )

  return (
    <li
      ref={setNodeRef}
      style={style}
      className={cn(isDragging && "opacity-40")}
    >
      {renderItem ? (
        renderItem(item, dragHandle)
      ) : (
        <DefaultRow item={item} dragHandle={dragHandle} />
      )}
    </li>
  )
}

function DefaultRow({
  item,
  dragHandle,
}: {
  item: SortableListItem
  dragHandle: React.ReactNode
}) {
  return (
    <div className="flex items-center gap-3 rounded-lg border bg-card px-3 py-2 text-sm shadow-sm">
      {dragHandle}
      {item.icon && <span className="shrink-0 text-muted-foreground">{item.icon}</span>}
      <div className="min-w-0 flex-1">
        <div className="font-medium">{item.label}</div>
        {item.description && (
          <div className="text-xs text-muted-foreground">{item.description}</div>
        )}
      </div>
    </div>
  )
}

export { SortableList }
