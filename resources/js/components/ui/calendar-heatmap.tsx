import * as React from "react"
import {
  eachDayOfInterval,
  eachWeekOfInterval,
  endOfWeek,
  format,
  subYears,
} from "date-fns"

import { cn } from "@/lib/utils"

export interface HeatmapDay {
  date: Date
  count: number
}

interface CalendarHeatmapProps {
  data?: HeatmapDay[]
  startDate?: Date
  endDate?: Date
  colorScheme?: string[]
  className?: string
  showMonthLabels?: boolean
  showWeekdayLabels?: boolean
  cellSize?: number
  cellGap?: number
  tooltip?: (day: HeatmapDay | undefined, date: Date) => string
  onClick?: (date: Date, day: HeatmapDay | undefined) => void
}

const DEFAULT_COLORS = [
  "bg-muted",
  "bg-primary/20",
  "bg-primary/40",
  "bg-primary/70",
  "bg-primary",
]

const WEEKDAYS = ["", "Mon", "", "Wed", "", "Fri", ""]

function CalendarHeatmap({
  data = [],
  endDate = new Date(),
  startDate,
  colorScheme = DEFAULT_COLORS,
  className,
  showMonthLabels = true,
  showWeekdayLabels = true,
  onClick,
}: CalendarHeatmapProps) {
  const end = endDate
  const start = startDate ?? subYears(end, 1)

  const dataMap = new Map<string, number>()
  data.forEach((d) => {
    dataMap.set(format(d.date, "yyyy-MM-dd"), d.count)
  })

  const maxCount = Math.max(0, ...data.map((d) => d.count))

  const getColor = (count: number | undefined) => {
    if (!count || count === 0) return colorScheme[0] ?? "bg-muted"
    const ratio = count / (maxCount || 1)
    const idx = Math.min(
      colorScheme.length - 1,
      Math.floor(ratio * (colorScheme.length - 1)) + 1
    )
    return colorScheme[idx] ?? "bg-muted"
  }

  const weeks = eachWeekOfInterval(
    { start, end },
    { weekStartsOn: 0 }
  )

  const monthLabels: { label: string; weekIndex: number }[] = []
  let lastMonth = -1
  weeks.forEach((weekStart, i) => {
    const month = weekStart.getMonth()
    if (month !== lastMonth) {
      monthLabels.push({ label: format(weekStart, "MMM"), weekIndex: i })
      lastMonth = month
    }
  })

  return (
    <div data-slot="calendar-heatmap" className={cn("inline-flex gap-1", className)}>
      {showWeekdayLabels && (
        <div className="flex flex-col gap-px pt-5">
          {WEEKDAYS.map((day, i) => (
            <div
              key={i}
              className="flex h-3 items-center justify-end pr-1 text-xs text-muted-foreground"
            >
              {day}
            </div>
          ))}
        </div>
      )}
      <div className="flex flex-col gap-1">
        {showMonthLabels && (
          <div className="relative h-5">
            {monthLabels.map(({ label, weekIndex }) => (
              <span
                key={label + weekIndex}
                className="absolute text-xs text-muted-foreground"
                style={{ left: weekIndex * 14 }}
              >
                {label}
              </span>
            ))}
          </div>
        )}
        <div className="flex gap-px">
          {weeks.map((weekStart, wIdx) => {
            const days = eachDayOfInterval({
              start: weekStart,
              end: endOfWeek(weekStart, { weekStartsOn: 0 }),
            })
            return (
              <div key={wIdx} className="flex flex-col gap-px">
                {days.map((d) => {
                  const key = format(d, "yyyy-MM-dd")
                  const count = dataMap.get(key)
                  const isOutOfRange = d < start || d > end
                  return (
                    <div
                      key={key}
                      title={`${format(d, "PP")}: ${count ?? 0}`}
                      onClick={() => !isOutOfRange && onClick?.(d, count !== undefined ? { date: d, count } : undefined)}
                      className={cn(
                        "size-3 rounded-sm",
                        isOutOfRange ? "opacity-0 pointer-events-none" : "",
                        isOutOfRange ? "" : getColor(count),
                        onClick && !isOutOfRange ? "cursor-pointer" : ""
                      )}
                    />
                  )
                })}
              </div>
            )
          })}
        </div>
      </div>
    </div>
  )
}

export { CalendarHeatmap }
