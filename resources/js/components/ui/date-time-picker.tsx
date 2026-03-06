import * as React from "react"
import { format } from "date-fns"
import { CalendarIcon, ClockIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface DateTimePickerProps {
  value?: Date
  onChange?: (date: Date | undefined) => void
  placeholder?: string
  className?: string
  disabled?: boolean
}

function DateTimePicker({
  value,
  onChange,
  placeholder = "Pick date & time",
  className,
  disabled,
}: DateTimePickerProps) {
  const [open, setOpen] = React.useState(false)
  const [selectedDate, setSelectedDate] = React.useState<Date | undefined>(value)
  const [time, setTime] = React.useState(() => {
    if (value) return format(value, "HH:mm")
    return "00:00"
  })

  React.useEffect(() => {
    setSelectedDate(value)
    if (value) setTime(format(value, "HH:mm"))
  }, [value])

  const handleDateSelect = (date: Date | undefined) => {
    if (!date) {
      onChange?.(undefined)
      setSelectedDate(undefined)
      return
    }
    const [hours, minutes] = time.split(":").map(Number)
    date.setHours(hours ?? 0, minutes ?? 0, 0, 0)
    setSelectedDate(date)
    onChange?.(date)
  }

  const handleTimeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newTime = e.target.value
    setTime(newTime)
    if (selectedDate) {
      const [hours, minutes] = newTime.split(":").map(Number)
      const updated = new Date(selectedDate)
      updated.setHours(hours ?? 0, minutes ?? 0, 0, 0)
      onChange?.(updated)
    }
  }

  const displayValue = selectedDate
    ? format(selectedDate, "PPP HH:mm")
    : placeholder

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          disabled={disabled}
          className={cn(
            "w-full justify-start text-left font-normal",
            !selectedDate && "text-muted-foreground",
            className
          )}
        >
          <CalendarIcon className="mr-2 size-4" />
          {displayValue}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <Calendar
          mode="single"
          selected={selectedDate}
          onSelect={handleDateSelect}
          initialFocus
        />
        <div className="flex items-center gap-2 border-t p-3">
          <ClockIcon className="size-4 text-muted-foreground" />
          <input
            type="time"
            value={time}
            onChange={handleTimeChange}
            className="flex-1 rounded-md border border-input bg-background px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-ring"
          />
        </div>
      </PopoverContent>
    </Popover>
  )
}

export { DateTimePicker }
