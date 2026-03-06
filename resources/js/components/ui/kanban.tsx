import * as React from "react"
import {
  closestCenter,
  DndContext,
  type DragEndEvent,
  type DragOverEvent,
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
import { Badge } from "@/components/ui/badge"
import { Card } from "@/components/ui/card"

export interface KanbanCard {
  id: string
  title: string
  description?: string
  badge?: string
  color?: string
}

export interface KanbanColumn {
  id: string
  title: string
  cards: KanbanCard[]
  color?: string
}

interface KanbanProps {
  columns: KanbanColumn[]
  onChange?: (columns: KanbanColumn[]) => void
  className?: string
  renderCard?: (card: KanbanCard, column: KanbanColumn) => React.ReactNode
}

function Kanban({ columns, onChange, className, renderCard }: KanbanProps) {
  const [activeCard, setActiveCard] = React.useState<KanbanCard | null>(null)
  const [activeColumnId, setActiveColumnId] = React.useState<string | null>(null)

  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  )

  const findCard = (id: string): { card: KanbanCard; columnId: string } | null => {
    for (const col of columns) {
      const card = col.cards.find((c) => c.id === id)
      if (card) return { card, columnId: col.id }
    }
    return null
  }

  const handleDragStart = ({ active }: DragStartEvent) => {
    const found = findCard(String(active.id))
    if (found) {
      setActiveCard(found.card)
      setActiveColumnId(found.columnId)
    }
  }

  const handleDragOver = ({ active, over }: DragOverEvent) => {
    if (!over) return
    const activeId = String(active.id)
    const overId = String(over.id)
    if (activeId === overId) return

    const activeInfo = findCard(activeId)
    if (!activeInfo) return

    const overColumn = columns.find((c) => c.id === overId)
    const overCardInfo = findCard(overId)
    const targetColumnId = overColumn?.id ?? overCardInfo?.columnId

    if (!targetColumnId || activeInfo.columnId === targetColumnId) return

    const updated = columns.map((col) => {
      if (col.id === activeInfo.columnId) {
        return { ...col, cards: col.cards.filter((c) => c.id !== activeId) }
      }
      if (col.id === targetColumnId) {
        const insertIndex = overCardInfo
          ? col.cards.findIndex((c) => c.id === overId)
          : col.cards.length
        const newCards = [...col.cards]
        newCards.splice(insertIndex, 0, activeInfo.card)
        return { ...col, cards: newCards }
      }
      return col
    })
    onChange?.(updated)
  }

  const handleDragEnd = ({ active, over }: DragEndEvent) => {
    setActiveCard(null)
    setActiveColumnId(null)
    if (!over) return

    const activeId = String(active.id)
    const overId = String(over.id)
    if (activeId === overId) return

    const activeInfo = findCard(activeId)
    const overCardInfo = findCard(overId)
    if (!activeInfo || !overCardInfo) return
    if (activeInfo.columnId !== overCardInfo.columnId) return

    const updated = columns.map((col) => {
      if (col.id !== activeInfo.columnId) return col
      const oldIndex = col.cards.findIndex((c) => c.id === activeId)
      const newIndex = col.cards.findIndex((c) => c.id === overId)
      return { ...col, cards: arrayMove(col.cards, oldIndex, newIndex) }
    })
    onChange?.(updated)
  }

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragStart={handleDragStart}
      onDragOver={handleDragOver}
      onDragEnd={handleDragEnd}
    >
      <div
        data-slot="kanban"
        className={cn("flex gap-4 overflow-x-auto pb-4", className)}
      >
        {columns.map((col) => (
          <KanbanColumnComponent
            key={col.id}
            column={col}
            renderCard={renderCard}
          />
        ))}
      </div>
      <DragOverlay>
        {activeCard ? (
          <div className="rotate-2 opacity-90">
            {renderCard ? (
              renderCard(activeCard, columns.find((c) => c.id === activeColumnId)!)
            ) : (
              <DefaultCard card={activeCard} isDragging />
            )}
          </div>
        ) : null}
      </DragOverlay>
    </DndContext>
  )
}

function KanbanColumnComponent({
  column,
  renderCard,
}: {
  column: KanbanColumn
  renderCard?: (card: KanbanCard, column: KanbanColumn) => React.ReactNode
}) {
  return (
    <SortableContext
      items={column.cards.map((c) => c.id)}
      strategy={verticalListSortingStrategy}
    >
      <div
        data-slot="kanban-column"
        className="flex w-72 shrink-0 flex-col gap-3 rounded-xl bg-muted/50 p-3"
        id={column.id}
      >
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-semibold">{column.title}</h3>
          <Badge variant="secondary" className="h-5 px-1.5 text-xs">
            {column.cards.length}
          </Badge>
        </div>
        <div className="flex flex-col gap-2">
          {column.cards.map((card) => (
            <KanbanCardWrapper
              key={card.id}
              card={card}
              column={column}
              renderCard={renderCard}
            />
          ))}
        </div>
      </div>
    </SortableContext>
  )
}

function KanbanCardWrapper({
  card,
  column,
  renderCard,
}: {
  card: KanbanCard
  column: KanbanColumn
  renderCard?: (card: KanbanCard, column: KanbanColumn) => React.ReactNode
}) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: card.id })

  const style: React.CSSProperties = {
    transform: CSS.Transform.toString(transform),
    transition,
  }

  return (
    <div
      ref={setNodeRef}
      style={style}
      data-slot="kanban-card"
      className={cn(isDragging && "opacity-40")}
    >
      {renderCard ? (
        <div {...attributes} {...listeners}>
          {renderCard(card, column)}
        </div>
      ) : (
        <DefaultCard card={card} dragHandleProps={{ ...attributes, ...listeners }} />
      )}
    </div>
  )
}

function DefaultCard({
  card,
  dragHandleProps,
  isDragging,
}: {
  card: KanbanCard
  dragHandleProps?: React.HTMLAttributes<HTMLButtonElement>
  isDragging?: boolean
}) {
  return (
    <Card
      className={cn(
        "flex items-start gap-2 p-3 text-sm shadow-sm",
        isDragging && "shadow-lg"
      )}
    >
      <button
        type="button"
        className="mt-0.5 shrink-0 cursor-grab text-muted-foreground hover:text-foreground active:cursor-grabbing"
        aria-label="Drag card"
        {...dragHandleProps}
      >
        <GripVerticalIcon className="size-3.5" />
      </button>
      <div className="min-w-0 flex-1">
        <p className="font-medium leading-snug">{card.title}</p>
        {card.description && (
          <p className="mt-1 text-xs text-muted-foreground">{card.description}</p>
        )}
        {card.badge && (
          <Badge
            variant="outline"
            className="mt-2 h-4 px-1 text-[10px]"
          >
            {card.badge}
          </Badge>
        )}
      </div>
    </Card>
  )
}

export { Kanban }
