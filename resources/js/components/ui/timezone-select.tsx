import * as React from "react"
import { CheckIcon, ChevronsUpDownIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/components/ui/command"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface TimezoneOption {
  value: string
  label: string
  offset: string
  region: string
}

function getUTCOffset(tz: string): string {
  try {
    const now = new Date()
    const formatter = new Intl.DateTimeFormat("en", {
      timeZone: tz,
      timeZoneName: "shortOffset",
    })
    const parts = formatter.formatToParts(now)
    const offsetPart = parts.find((p) => p.type === "timeZoneName")
    return offsetPart?.value ?? "UTC"
  } catch {
    return "UTC"
  }
}

function buildTimezones(): TimezoneOption[] {
  const allZones = Intl.supportedValuesOf?.("timeZone") ?? []
  return allZones.map((tz) => {
    const parts = tz.split("/")
    const region = parts[0] ?? "Other"
    const city = parts.slice(1).join("/").replace(/_/g, " ") || tz
    const offset = getUTCOffset(tz)
    return {
      value: tz,
      label: `${city} (${offset})`,
      offset,
      region,
    }
  })
}

const TIMEZONES = buildTimezones()

function groupByRegion(timezones: TimezoneOption[]): Record<string, TimezoneOption[]> {
  return timezones.reduce<Record<string, TimezoneOption[]>>((acc, tz) => {
    if (!acc[tz.region]) acc[tz.region] = []
    acc[tz.region].push(tz)
    return acc
  }, {})
}

interface TimezoneSelectProps {
  value?: string
  defaultValue?: string
  onChange?: (value: string) => void
  placeholder?: string
  className?: string
  disabled?: boolean
}

function TimezoneSelect({
  value,
  defaultValue,
  onChange,
  placeholder = "Select timezone...",
  className,
  disabled = false,
}: TimezoneSelectProps) {
  const [open, setOpen] = React.useState(false)
  const [internalValue, setInternalValue] = React.useState(defaultValue ?? "")
  const [search, setSearch] = React.useState("")

  const isControlled = value !== undefined
  const current = isControlled ? value : internalValue

  const selected = TIMEZONES.find((tz) => tz.value === current)

  const filtered = React.useMemo(() => {
    if (!search) return TIMEZONES
    const q = search.toLowerCase()
    return TIMEZONES.filter(
      (tz) =>
        tz.label.toLowerCase().includes(q) ||
        tz.value.toLowerCase().includes(q) ||
        tz.offset.toLowerCase().includes(q)
    )
  }, [search])

  const grouped = React.useMemo(() => groupByRegion(filtered), [filtered])

  const handleSelect = (tzValue: string) => {
    if (!isControlled) setInternalValue(tzValue)
    onChange?.(tzValue)
    setOpen(false)
    setSearch("")
  }

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          disabled={disabled}
          className={cn("w-full justify-between font-normal", !current && "text-muted-foreground", className)}
        >
          <span className="truncate">
            {selected ? selected.label : placeholder}
          </span>
          <ChevronsUpDownIcon className="ml-2 size-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-[380px] p-0" align="start">
        <Command shouldFilter={false}>
          <CommandInput
            placeholder="Search timezone..."
            value={search}
            onValueChange={setSearch}
          />
          <CommandList className="max-h-60">
            <CommandEmpty>No timezone found.</CommandEmpty>
            {Object.entries(grouped).map(([region, zones]) => (
              <CommandGroup key={region} heading={region}>
                {zones.map((tz) => (
                  <CommandItem
                    key={tz.value}
                    value={tz.value}
                    onSelect={() => handleSelect(tz.value)}
                    className="gap-2"
                  >
                    <CheckIcon
                      className={cn("size-4 shrink-0", current === tz.value ? "opacity-100" : "opacity-0")}
                    />
                    <span className="flex-1 truncate">{tz.label}</span>
                    <span className="text-muted-foreground text-xs shrink-0">{tz.offset}</span>
                  </CommandItem>
                ))}
              </CommandGroup>
            ))}
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  )
}

export { TimezoneSelect }
