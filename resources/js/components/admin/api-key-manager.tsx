import * as React from "react"
import {
  CheckIcon,
  CopyIcon,
  EyeIcon,
  EyeOffIcon,
  KeyRoundIcon,
  PlusIcon,
  Trash2Icon,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { ConfirmDialog } from "@/components/ui/confirm-dialog"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

export interface ApiKey {
  id: string
  name: string
  key?: string
  lastUsed?: string | null
  createdAt: string
  scopes?: string[]
}

interface ApiKeyManagerProps {
  keys: ApiKey[]
  onCreate: (name: string) => Promise<ApiKey | void> | ApiKey | void
  onRevoke: (id: string) => Promise<void> | void
  className?: string
}

function maskKey(key: string): string {
  if (key.length <= 8) {
    return "*".repeat(key.length)
  }
  return key.slice(0, 4) + "•".repeat(8) + key.slice(-4)
}

function CopyableKey({ value }: { value: string }) {
  const [copied, setCopied] = React.useState(false)
  const [visible, setVisible] = React.useState(true)

  const handleCopy = async () => {
    await navigator.clipboard.writeText(value)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <div className="flex items-center gap-2">
      <code className="rounded bg-muted px-2 py-0.5 font-mono text-xs">
        {visible ? value : maskKey(value)}
      </code>
      <Button
        variant="ghost"
        size="icon"
        className="size-6"
        onClick={() => setVisible((v) => !v)}
        title={visible ? "Hide key" : "Show key"}
      >
        {visible ? (
          <EyeOffIcon className="size-3" />
        ) : (
          <EyeIcon className="size-3" />
        )}
      </Button>
      <Button
        variant="ghost"
        size="icon"
        className="size-6"
        onClick={handleCopy}
        title="Copy key"
      >
        {copied ? (
          <CheckIcon className="size-3 text-success" />
        ) : (
          <CopyIcon className="size-3" />
        )}
      </Button>
    </div>
  )
}

function ApiKeyManager({
  keys,
  onCreate,
  onRevoke,
  className,
}: ApiKeyManagerProps) {
  const [createOpen, setCreateOpen] = React.useState(false)
  const [newKeyName, setNewKeyName] = React.useState("")
  const [creating, setCreating] = React.useState(false)
  const [newlyCreated, setNewlyCreated] = React.useState<ApiKey | null>(null)
  const [revokeId, setRevokeId] = React.useState<string | null>(null)

  const handleCreate = async () => {
    if (!newKeyName.trim()) {
      return
    }
    setCreating(true)
    try {
      const result = await onCreate(newKeyName.trim())
      if (result) {
        setNewlyCreated(result)
      }
      setNewKeyName("")
      setCreateOpen(false)
    } finally {
      setCreating(false)
    }
  }

  const handleRevoke = async (id: string) => {
    await onRevoke(id)
    setRevokeId(null)
  }

  return (
    <div className={cn("space-y-4", className)}>
      {newlyCreated?.key && (
        <Alert className="border-success/50 bg-success/5">
          <KeyRoundIcon className="size-4 text-success" />
          <AlertDescription className="space-y-2">
            <p className="font-medium text-success">API key created!</p>
            <p className="text-sm text-muted-foreground">
              Copy this key now — it will not be shown again.
            </p>
            <CopyableKey value={newlyCreated.key} />
          </AlertDescription>
        </Alert>
      )}

      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="text-base">API Keys</CardTitle>
            <CardDescription>
              Manage API keys for programmatic access.
            </CardDescription>
          </div>
          <Button size="sm" onClick={() => setCreateOpen(true)}>
            <PlusIcon className="mr-1.5 size-4" />
            Create New Key
          </Button>
        </CardHeader>
        <CardContent className="p-0">
          {keys.length === 0 ? (
            <div className="flex flex-col items-center gap-2 py-12 text-center text-muted-foreground">
              <KeyRoundIcon className="size-8 opacity-40" />
              <p className="text-sm">No API keys yet.</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Key</TableHead>
                  <TableHead>Last Used</TableHead>
                  <TableHead>Created</TableHead>
                  <TableHead className="w-24" />
                </TableRow>
              </TableHeader>
              <TableBody>
                {keys.map((apiKey) => (
                  <TableRow key={apiKey.id}>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        <Avatar className="size-6">
                          <AvatarFallback className="text-xs">
                            {apiKey.name.slice(0, 1).toUpperCase()}
                          </AvatarFallback>
                        </Avatar>
                        <span className="font-medium">{apiKey.name}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      {apiKey.key ? (
                        <CopyableKey value={apiKey.key} />
                      ) : (
                        <code className="rounded bg-muted px-2 py-0.5 font-mono text-xs text-muted-foreground">
                          ••••••••••••
                        </code>
                      )}
                    </TableCell>
                    <TableCell>
                      {apiKey.lastUsed ? (
                        <span className="text-sm">{apiKey.lastUsed}</span>
                      ) : (
                        <Badge variant="secondary" className="text-xs">
                          Never
                        </Badge>
                      )}
                    </TableCell>
                    <TableCell>
                      <span className="text-sm text-muted-foreground">
                        {apiKey.createdAt}
                      </span>
                    </TableCell>
                    <TableCell>
                      <Button
                        variant="ghost"
                        size="sm"
                        className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        onClick={() => setRevokeId(apiKey.id)}
                      >
                        <Trash2Icon className="mr-1 size-3.5" />
                        Revoke
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>

      {/* Create dialog */}
      <Dialog open={createOpen} onOpenChange={setCreateOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Create API Key</DialogTitle>
            <DialogDescription>
              Give this key a descriptive name to identify its purpose.
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-2">
            <Label htmlFor="key-name">Key Name</Label>
            <Input
              id="key-name"
              placeholder="e.g. Production Server"
              value={newKeyName}
              onChange={(e) => setNewKeyName(e.target.value)}
              onKeyDown={(e) => {
                if (e.key === "Enter") {
                  void handleCreate()
                }
              }}
            />
          </div>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => setCreateOpen(false)}
              disabled={creating}
            >
              Cancel
            </Button>
            <Button
              onClick={() => void handleCreate()}
              disabled={creating || !newKeyName.trim()}
            >
              {creating ? "Creating..." : "Create Key"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Revoke confirm */}
      <ConfirmDialog
        open={revokeId !== null}
        onOpenChange={(open) => {
          if (!open) {
            setRevokeId(null)
          }
        }}
        title="Revoke API Key"
        description="This key will be permanently deleted and any applications using it will lose access immediately. This action cannot be undone."
        confirmLabel="Revoke Key"
        variant="destructive"
        onConfirm={() => {
          if (revokeId) {
            void handleRevoke(revokeId)
          }
        }}
      />
    </div>
  )
}

export { ApiKeyManager }
