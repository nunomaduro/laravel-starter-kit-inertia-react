import * as React from "react"
import { CalendarIcon, FilterIcon, SearchIcon, UserIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { VirtualList } from "@/components/ui/virtual-list"

export interface AuditLogActor {
  id: string
  name: string
  email?: string
  avatar?: string
}

export interface AuditLogEntry {
  id: string
  actor?: AuditLogActor | null
  action: string
  target?: string
  targetType?: string
  metadata?: Record<string, unknown>
  timestamp: string
  variant?: "default" | "success" | "warning" | "error" | "info"
}

export interface AuditLogFilters {
  search?: string
  dateFrom?: string
  dateTo?: string
  type?: string
  userId?: string
}

interface AuditLogViewerProps {
  entries: AuditLogEntry[]
  filters?: AuditLogFilters
  onFilterChange?: (filters: AuditLogFilters) => void
  actionTypes?: string[]
  className?: string
  virtualized?: boolean
}

const variantBadgeMap: Record<string, string> = {
  default: "secondary",
  success: "default",
  warning: "outline",
  error: "destructive",
  info: "secondary",
}

const variantDotMap: Record<string, string> = {
  default: "bg-muted-foreground",
  success: "bg-success",
  warning: "bg-warning",
  error: "bg-destructive",
  info: "bg-info",
}

function AuditLogEntryRow({ entry }: { entry: AuditLogEntry }) {
  const variant = entry.variant ?? "default"

  return (
    <div className="flex items-start gap-3 border-b px-4 py-3 last:border-0 hover:bg-muted/30">
      <div className="relative mt-1">
        <Avatar className="size-8">
          {entry.actor?.avatar && (
            <AvatarImage src={entry.actor.avatar} alt={entry.actor.name} />
          )}
          <AvatarFallback className="text-xs">
            {entry.actor ? (
              entry.actor.name
                .split(" ")
                .map((n) => n[0])
                .join("")
                .slice(0, 2)
                .toUpperCase()
            ) : (
              <UserIcon className="size-3.5" />
            )}
          </AvatarFallback>
        </Avatar>
        <span
          className={cn(
            "absolute -right-0.5 -top-0.5 size-2.5 rounded-full border-2 border-background",
            variantDotMap[variant]
          )}
        />
      </div>

      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-baseline gap-1.5">
          <span className="font-medium text-sm">
            {entry.actor?.name ?? "System"}
          </span>
          <span className="text-sm text-muted-foreground">{entry.action}</span>
          {entry.target && (
            <>
              <span className="text-sm text-muted-foreground">on</span>
              <span className="text-sm font-medium">{entry.target}</span>
            </>
          )}
        </div>
        <div className="mt-0.5 flex flex-wrap items-center gap-2">
          {entry.actor?.email && (
            <span className="text-xs text-muted-foreground">
              {entry.actor.email}
            </span>
          )}
          {entry.targetType && (
            <Badge
              variant={(variantBadgeMap[variant] as "secondary" | "default" | "outline" | "destructive") ?? "secondary"}
              className="text-xs"
            >
              {entry.targetType}
            </Badge>
          )}
        </div>
      </div>

      <span className="shrink-0 text-xs text-muted-foreground">
        {entry.timestamp}
      </span>
    </div>
  )
}

function AuditLogViewer({
  entries,
  filters = {},
  onFilterChange,
  actionTypes = [],
  className,
  virtualized = false,
}: AuditLogViewerProps) {
  const [localFilters, setLocalFilters] = React.useState<AuditLogFilters>(filters)

  const updateFilter = (key: keyof AuditLogFilters, value: string) => {
    const next = { ...localFilters, [key]: value || undefined }
    setLocalFilters(next)
    onFilterChange?.(next)
  }

  return (
    <Card className={cn("flex flex-col overflow-hidden", className)}>
      <CardHeader className="shrink-0">
        <div className="flex items-center justify-between">
          <div>
            <CardTitle className="text-base">Audit Log</CardTitle>
            <CardDescription>
              Track all significant actions and changes in your system.
            </CardDescription>
          </div>
          <Badge variant="secondary" className="text-xs">
            {entries.length} events
          </Badge>
        </div>

        <div className="flex flex-wrap gap-2 pt-2">
          <div className="relative min-w-48 flex-1">
            <SearchIcon className="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted-foreground" />
            <Input
              className="pl-8 text-sm"
              placeholder="Search events..."
              value={localFilters.search ?? ""}
              onChange={(e) => updateFilter("search", e.target.value)}
            />
          </div>

          {actionTypes.length > 0 && (
            <Select
              value={localFilters.type ?? ""}
              onValueChange={(v) => updateFilter("type", v === "all" ? "" : v)}
            >
              <SelectTrigger className="w-36">
                <FilterIcon className="mr-1.5 size-3.5 text-muted-foreground" />
                <SelectValue placeholder="All types" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All types</SelectItem>
                {actionTypes.map((type) => (
                  <SelectItem key={type} value={type}>
                    {type}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          )}

          <div className="flex items-center gap-1">
            <CalendarIcon className="size-3.5 text-muted-foreground" />
            <Input
              type="date"
              className="w-36 text-xs"
              value={localFilters.dateFrom ?? ""}
              onChange={(e) => updateFilter("dateFrom", e.target.value)}
            />
            <span className="text-xs text-muted-foreground">–</span>
            <Input
              type="date"
              className="w-36 text-xs"
              value={localFilters.dateTo ?? ""}
              onChange={(e) => updateFilter("dateTo", e.target.value)}
            />
          </div>

          {(localFilters.search ||
            localFilters.type ||
            localFilters.dateFrom ||
            localFilters.dateTo ||
            localFilters.userId) && (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => {
                setLocalFilters({})
                onFilterChange?.({})
              }}
            >
              Clear
            </Button>
          )}
        </div>
      </CardHeader>

      <CardContent className="min-h-0 flex-1 p-0">
        {entries.length === 0 ? (
          <div className="flex flex-col items-center gap-2 py-16 text-center text-muted-foreground">
            <FilterIcon className="size-8 opacity-40" />
            <p className="text-sm">No events match your filters.</p>
          </div>
        ) : virtualized ? (
          <VirtualList
            items={entries}
            estimateSize={72}
            className="h-[480px]"
            renderItem={(entry) => (
              <AuditLogEntryRow key={entry.id} entry={entry} />
            )}
          />
        ) : (
          <div className="divide-y">
            {entries.map((entry) => (
              <AuditLogEntryRow key={entry.id} entry={entry} />
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  )
}

export { AuditLogViewer }
