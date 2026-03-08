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

export interface DndItem {
  id: string
  [key: string]: unknown
}

interface DndListProps<T extends DndItem> {
  items: T[]
  onReorder: (items: T[]) => void
  renderItem: (item: T, isDragging?: boolean) => React.ReactNode
  className?: string
  handle?: boolean
}

function DndList<T extends DndItem>({
  items,
  onReorder,
  renderItem,
  className,
  handle = true,
}: DndListProps<T>) {
  const [activeId, setActiveId] = React.useState<string | null>(null)
  const activeItem = items.find((i) => i.id === activeId)

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  )

  const handleDragStart = (event: DragStartEvent) => {
    setActiveId(String(event.active.id))
  }

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event
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
        <ul
          data-slot="dnd-list"
          className={cn("flex flex-col gap-2", className)}
        >
          {items.map((item) => (
            <DndListItem
              key={item.id}
              item={item}
              renderItem={renderItem}
              handle={handle}
            />
          ))}
        </ul>
      </SortableContext>
      <DragOverlay>
        {activeItem ? (
          <div className="opacity-90 shadow-xl">
            {renderItem(activeItem, true)}
          </div>
        ) : null}
      </DragOverlay>
    </DndContext>
  )
}

function DndListItem<T extends DndItem>({
  item,
  renderItem,
  handle,
}: {
  item: T
  renderItem: (item: T, isDragging?: boolean) => React.ReactNode
  handle: boolean
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
    opacity: isDragging ? 0.4 : 1,
  }

  return (
    <li
      ref={setNodeRef}
      style={style}
      data-slot="dnd-list-item"
      className="flex items-center gap-2"
      {...(!handle ? { ...attributes, ...listeners } : {})}
    >
      {handle && (
        <button
          type="button"
          className="cursor-grab touch-none text-muted-foreground hover:text-foreground active:cursor-grabbing"
          {...attributes}
          {...listeners}
          aria-label="Drag handle"
        >
          <GripVerticalIcon className="size-4" />
        </button>
      )}
      <div className="flex-1">{renderItem(item, isDragging)}</div>
    </li>
  )
}

export { DndList }
