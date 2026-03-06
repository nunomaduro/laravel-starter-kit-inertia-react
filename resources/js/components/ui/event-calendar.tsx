import * as React from "react"
import {
  addDays,
  addMonths,
  endOfMonth,
  endOfWeek,
  format,
  isSameDay,
  isSameMonth,
  startOfMonth,
  startOfWeek,
  subMonths,
} from "date-fns"
import { ChevronLeftIcon, ChevronRightIcon, PlusIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"

export interface CalendarEvent {
  id: string | number
  title: string
  date: Date
  endDate?: Date
  color?: string
  variant?: "default" | "success" | "warning" | "error" | "info"
}

interface EventCalendarProps {
  events?: CalendarEvent[]
  defaultDate?: Date
  onDateClick?: (date: Date) => void
  onEventClick?: (event: CalendarEvent) => void
  onAddEvent?: (date: Date) => void
  className?: string
}

function EventCalendar({
  events = [],
  defaultDate = new Date(),
  onDateClick,
  onEventClick,
  onAddEvent,
  className,
}: EventCalendarProps) {
  const [currentDate, setCurrentDate] = React.useState(defaultDate)

  const monthStart = startOfMonth(currentDate)
  const monthEnd = endOfMonth(currentDate)
  const calStart = startOfWeek(monthStart, { weekStartsOn: 0 })
  const calEnd = endOfWeek(monthEnd, { weekStartsOn: 0 })

  const days: Date[] = []
  let day = calStart
  while (day <= calEnd) {
    days.push(day)
    day = addDays(day, 1)
  }

  const getEventsForDay = (date: Date) =>
    events.filter((e) => isSameDay(e.date, date))

  const variantToColor: Record<string, string> = {
    default: "bg-primary text-primary-foreground",
    success: "bg-green-500 text-white",
    warning: "bg-amber-500 text-white",
    error: "bg-red-500 text-white",
    info: "bg-blue-500 text-white",
  }

  return (
    <div data-slot="event-calendar" className={cn("flex flex-col gap-2", className)}>
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">
          {format(currentDate, "MMMM yyyy")}
        </h2>
        <div className="flex items-center gap-1">
          <Button
            variant="ghost"
            size="icon-sm"
            onClick={() => setCurrentDate((d) => subMonths(d, 1))}
          >
            <ChevronLeftIcon className="size-4" />
          </Button>
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setCurrentDate(new Date())}
          >
            Today
          </Button>
          <Button
            variant="ghost"
            size="icon-sm"
            onClick={() => setCurrentDate((d) => addMonths(d, 1))}
          >
            <ChevronRightIcon className="size-4" />
          </Button>
        </div>
      </div>
      <div className="grid grid-cols-7 gap-px rounded-lg border bg-border">
        {["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"].map((weekday) => (
          <div
            key={weekday}
            className="bg-muted/50 p-2 text-center text-xs font-medium text-muted-foreground"
          >
            {weekday}
          </div>
        ))}
        {days.map((d, i) => {
          const isCurrentMonth = isSameMonth(d, currentDate)
          const isToday = isSameDay(d, new Date())
          const dayEvents = getEventsForDay(d)

          return (
            <div
              key={i}
              className={cn(
                "group relative min-h-24 bg-background p-1 transition-colors hover:bg-muted/50",
                !isCurrentMonth && "opacity-40"
              )}
              onClick={() => onDateClick?.(d)}
            >
              <div className="flex items-start justify-between">
                <span
                  className={cn(
                    "flex size-6 items-center justify-center rounded-full text-xs",
                    isToday && "bg-primary font-semibold text-primary-foreground"
                  )}
                >
                  {format(d, "d")}
                </span>
                {onAddEvent && (
                  <Button
                    variant="ghost"
                    size="icon-xs"
                    className="opacity-0 group-hover:opacity-100"
                    onClick={(e) => {
                      e.stopPropagation()
                      onAddEvent(d)
                    }}
                  >
                    <PlusIcon className="size-3" />
                  </Button>
                )}
              </div>
              <div className="mt-0.5 space-y-0.5">
                {dayEvents.slice(0, 3).map((event) => (
                  <button
                    key={event.id}
                    type="button"
                    className={cn(
                      "w-full truncate rounded px-1 text-left text-xs",
                      variantToColor[event.variant ?? "default"]
                    )}
                    onClick={(e) => {
                      e.stopPropagation()
                      onEventClick?.(event)
                    }}
                  >
                    {event.title}
                  </button>
                ))}
                {dayEvents.length > 3 && (
                  <Badge variant="secondary" className="text-xs">
                    +{dayEvents.length - 3} more
                  </Badge>
                )}
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}

export { EventCalendar }
