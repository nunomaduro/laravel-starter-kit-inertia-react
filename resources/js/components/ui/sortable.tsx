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
  horizontalListSortingStrategy,
  rectSortingStrategy,
} from "@dnd-kit/sortable"
import { CSS } from "@dnd-kit/utilities"
import { GripVerticalIcon } from "lucide-react"

import { cn } from "@/lib/utils"

export interface SortableItem {
  id: string
  [key: string]: unknown
}

type SortableStrategy = "vertical" | "horizontal" | "grid"

interface SortableProps<T extends SortableItem> {
  items: T[]
  onReorder: (items: T[]) => void
  renderItem: (item: T, props: { dragHandle: React.ReactNode; isDragging: boolean }) => React.ReactNode
  strategy?: SortableStrategy
  className?: string
}

const strategyMap = {
  vertical: verticalListSortingStrategy,
  horizontal: horizontalListSortingStrategy,
  grid: rectSortingStrategy,
}

function Sortable<T extends SortableItem>({
  items,
  onReorder,
  renderItem,
  strategy = "vertical",
  className,
}: SortableProps<T>) {
  const [activeId, setActiveId] = React.useState<string | null>(null)
  const activeItem = items.find((i) => i.id === activeId)

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: { distance: 5 },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  )

  const handleDragStart = ({ active }: DragStartEvent) => {
    setActiveId(String(active.id))
  }

  const handleDragEnd = ({ active, over }: DragEndEvent) => {
    setActiveId(null)
    if (over && active.id !== over.id) {
      const oldIdx = items.findIndex((i) => i.id === active.id)
      const newIdx = items.findIndex((i) => i.id === over.id)
      onReorder(arrayMove(items, oldIdx, newIdx))
    }
  }

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragStart={handleDragStart}
      onDragEnd={handleDragEnd}
    >
      <SortableContext items={items.map((i) => i.id)} strategy={strategyMap[strategy]}>
        <div
          data-slot="sortable"
          data-strategy={strategy}
          className={className}
        >
          {items.map((item) => (
            <SortableItemWrapper
              key={item.id}
              item={item}
              renderItem={renderItem}
            />
          ))}
        </div>
      </SortableContext>
      <DragOverlay>
        {activeItem ? (
          <div className="opacity-90 shadow-2xl">
            {renderItem(activeItem, {
              dragHandle: <GripVerticalIcon className="size-4" />,
              isDragging: true,
            })}
          </div>
        ) : null}
      </DragOverlay>
    </DndContext>
  )
}

function SortableItemWrapper<T extends SortableItem>({
  item,
  renderItem,
}: {
  item: T
  renderItem: (item: T, props: { dragHandle: React.ReactNode; isDragging: boolean }) => React.ReactNode
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
      className={cn(
        "cursor-grab text-muted-foreground hover:text-foreground active:cursor-grabbing",
        isDragging && "cursor-grabbing"
      )}
      {...attributes}
      {...listeners}
      aria-label="Drag to reorder"
    >
      <GripVerticalIcon className="size-4" />
    </button>
  )

  return (
    <div
      ref={setNodeRef}
      style={style}
      data-slot="sortable-item"
      className={cn(isDragging && "opacity-40")}
    >
      {renderItem(item, { dragHandle, isDragging })}
    </div>
  )
}

export { Sortable }
