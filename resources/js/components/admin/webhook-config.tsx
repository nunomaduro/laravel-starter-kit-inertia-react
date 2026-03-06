import * as React from "react"
import {
  CheckCircleIcon,
  Loader2Icon,
  PlusIcon,
  SendIcon,
  XCircleIcon,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"

export interface WebhookEvent {
  id: string
  label: string
  resource: string
  description?: string
}

export interface WebhookDeliveryStatus {
  success: boolean
  statusCode?: number
  message?: string
  timestamp?: string
}

export interface WebhookValue {
  url: string
  events: string[]
  secret?: string
}

interface WebhookConfigProps {
  events: WebhookEvent[]
  value?: WebhookValue
  onChange?: (value: WebhookValue) => void
  onTest?: (url: string, events: string[]) => Promise<WebhookDeliveryStatus>
  lastDelivery?: WebhookDeliveryStatus | null
  className?: string
}

function groupEvents(events: WebhookEvent[]): Record<string, WebhookEvent[]> {
  return events.reduce<Record<string, WebhookEvent[]>>((groups, event) => {
    const key = event.resource
    if (!groups[key]) {
      groups[key] = []
    }
    groups[key].push(event)
    return groups
  }, {})
}

function WebhookConfig({
  events,
  value,
  onChange,
  onTest,
  lastDelivery,
  className,
}: WebhookConfigProps) {
  const [localValue, setLocalValue] = React.useState<WebhookValue>(
    value ?? { url: "", events: [] }
  )
  const [testing, setTesting] = React.useState(false)
  const [testResult, setTestResult] = React.useState<WebhookDeliveryStatus | null>(
    lastDelivery ?? null
  )

  const grouped = groupEvents(events)
  const resources = Object.keys(grouped)

  const update = (patch: Partial<WebhookValue>) => {
    const next = { ...localValue, ...patch }
    setLocalValue(next)
    onChange?.(next)
  }

  const toggleEvent = (eventId: string) => {
    const selected = localValue.events
    const next = selected.includes(eventId)
      ? selected.filter((e) => e !== eventId)
      : [...selected, eventId]
    update({ events: next })
  }

  const toggleResource = (resource: string) => {
    const resourceEvents = (grouped[resource] ?? []).map((e) => e.id)
    const allSelected = resourceEvents.every((id) =>
      localValue.events.includes(id)
    )
    const currentEvents = localValue.events
    const next = allSelected
      ? currentEvents.filter((id) => !resourceEvents.includes(id))
      : [...new Set([...currentEvents, ...resourceEvents])]
    update({ events: next })
  }

  const handleSelectAll = () => {
    update({ events: events.map((e) => e.id) })
  }

  const handleDeselectAll = () => {
    update({ events: [] })
  }

  const handleTest = async () => {
    if (!onTest || !localValue.url) {
      return
    }
    setTesting(true)
    setTestResult(null)
    try {
      const result = await onTest(localValue.url, localValue.events)
      setTestResult(result)
    } finally {
      setTesting(false)
    }
  }

  const allSelected = events.every((e) => localValue.events.includes(e.id))
  const noneSelected = localValue.events.length === 0

  return (
    <div className={cn("space-y-4", className)}>
      {/* Endpoint URL */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Webhook Endpoint</CardTitle>
          <CardDescription>
            We will send POST requests to this URL when selected events occur.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="webhook-url">Endpoint URL</Label>
            <div className="flex gap-2">
              <Input
                id="webhook-url"
                type="url"
                placeholder="https://example.com/webhooks"
                value={localValue.url}
                onChange={(e) => update({ url: e.target.value })}
                className="flex-1 font-mono text-sm"
              />
              {onTest && (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => void handleTest()}
                  disabled={testing || !localValue.url}
                >
                  {testing ? (
                    <Loader2Icon className="mr-1.5 size-3.5 animate-spin" />
                  ) : (
                    <SendIcon className="mr-1.5 size-3.5" />
                  )}
                  Test Webhook
                </Button>
              )}
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="webhook-secret">
              Secret{" "}
              <span className="font-normal text-muted-foreground">(optional)</span>
            </Label>
            <Input
              id="webhook-secret"
              type="password"
              placeholder="Used to sign webhook payloads"
              value={localValue.secret ?? ""}
              onChange={(e) => update({ secret: e.target.value })}
              className="font-mono text-sm"
            />
            <p className="text-xs text-muted-foreground">
              We include a{" "}
              <code className="rounded bg-muted px-1 text-xs">X-Signature</code>{" "}
              header with each request when a secret is set.
            </p>
          </div>

          {/* Test result */}
          {testResult && (
            <div
              className={cn(
                "flex items-start gap-2 rounded-lg border p-3",
                testResult.success
                  ? "border-success/30 bg-success/5"
                  : "border-destructive/30 bg-destructive/5"
              )}
            >
              {testResult.success ? (
                <CheckCircleIcon className="mt-0.5 size-4 shrink-0 text-success" />
              ) : (
                <XCircleIcon className="mt-0.5 size-4 shrink-0 text-destructive" />
              )}
              <div className="text-sm">
                <p className={cn("font-medium", testResult.success ? "text-success" : "text-destructive")}>
                  {testResult.success ? "Webhook delivered successfully" : "Webhook delivery failed"}
                </p>
                {testResult.statusCode && (
                  <p className="text-xs text-muted-foreground">
                    HTTP {testResult.statusCode}
                    {testResult.message ? ` — ${testResult.message}` : ""}
                  </p>
                )}
                {testResult.timestamp && (
                  <p className="text-xs text-muted-foreground">
                    {testResult.timestamp}
                  </p>
                )}
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Event selection */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle className="text-base">Events to Send</CardTitle>
              <CardDescription>
                Choose which events trigger this webhook.
              </CardDescription>
            </div>
            <div className="flex items-center gap-2">
              <Badge variant="secondary" className="text-xs">
                {localValue.events.length}/{events.length} selected
              </Badge>
              <Button
                variant="ghost"
                size="sm"
                className="text-xs h-7"
                onClick={allSelected ? handleDeselectAll : handleSelectAll}
              >
                {allSelected ? "Deselect All" : "Select All"}
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent className="space-y-4 pt-0">
          {resources.map((resource, i) => {
            const resourceEvents = grouped[resource] ?? []
            const allResourceSelected = resourceEvents.every((e) =>
              localValue.events.includes(e.id)
            )
            const someResourceSelected = resourceEvents.some((e) =>
              localValue.events.includes(e.id)
            )

            return (
              <React.Fragment key={resource}>
                {i > 0 && <Separator />}
                <div className="space-y-2">
                  <div className="flex items-center gap-2">
                    <Checkbox
                      id={`resource-${resource}`}
                      checked={allResourceSelected}
                      className={cn(!allResourceSelected && someResourceSelected && "opacity-60")}
                      onCheckedChange={() => toggleResource(resource)}
                    />
                    <Label
                      htmlFor={`resource-${resource}`}
                      className="cursor-pointer text-sm font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                      {resource}
                    </Label>
                  </div>
                  <div className="ml-6 space-y-2">
                    {resourceEvents.map((event) => (
                      <div key={event.id} className="flex items-start gap-2">
                        <Checkbox
                          id={event.id}
                          checked={localValue.events.includes(event.id)}
                          onCheckedChange={() => toggleEvent(event.id)}
                          className="mt-0.5"
                        />
                        <div>
                          <Label
                            htmlFor={event.id}
                            className="cursor-pointer text-sm font-medium"
                          >
                            {event.label}
                          </Label>
                          {event.description && (
                            <p className="text-xs text-muted-foreground">
                              {event.description}
                            </p>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </React.Fragment>
            )
          })}

          {noneSelected && (
            <p className="text-sm text-muted-foreground text-center py-2">
              Select at least one event to activate this webhook.
            </p>
          )}
        </CardContent>
      </Card>
    </div>
  )
}

export { WebhookConfig }
