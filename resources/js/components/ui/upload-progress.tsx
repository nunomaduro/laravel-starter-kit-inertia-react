import * as React from "react"
import { FileIcon, CheckCircle2Icon, XCircleIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Progress } from "@/components/ui/progress"
import { Button } from "@/components/ui/button"

type UploadStatus = "pending" | "uploading" | "success" | "error"

interface UploadFile {
  id: string
  name: string
  size?: number
  progress?: number
  status: UploadStatus
  error?: string
}

interface UploadProgressItemProps extends React.HTMLAttributes<HTMLDivElement> {
  file: UploadFile
  onRemove?: (id: string) => void
  onRetry?: (id: string) => void
}

function formatBytes(bytes: number, decimals = 1): string {
  if (bytes === 0) return "0 B"
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ["B", "KB", "MB", "GB"]
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
}

function UploadProgressItem({ file, onRemove, onRetry, className, ...props }: UploadProgressItemProps) {
  const progress = file.progress ?? (file.status === "success" ? 100 : 0)

  return (
    <div
      data-slot="upload-progress-item"
      className={cn("flex items-start gap-3 rounded-lg border p-3", className)}
      {...props}
    >
      <div className="mt-0.5 shrink-0 text-muted-foreground">
        <FileIcon className="size-5" />
      </div>

      <div className="min-w-0 flex-1 space-y-1">
        <div className="flex items-center justify-between gap-2">
          <p className="truncate text-sm font-medium">{file.name}</p>
          <div className="flex shrink-0 items-center gap-1">
            {file.status === "success" && (
              <CheckCircle2Icon className="size-4 text-success" />
            )}
            {file.status === "error" && (
              <XCircleIcon className="size-4 text-destructive" />
            )}
            {onRemove && (
              <Button
                variant="ghost"
                size="icon"
                className="size-6"
                onClick={() => onRemove(file.id)}
                aria-label="Remove file"
              >
                <XIcon className="size-3" />
              </Button>
            )}
          </div>
        </div>

        <div className="flex items-center gap-2 text-xs text-muted-foreground">
          {file.size !== undefined && <span>{formatBytes(file.size)}</span>}
          {file.status === "uploading" && <span>{progress}%</span>}
          {file.status === "error" && file.error && (
            <span className="text-destructive">{file.error}</span>
          )}
        </div>

        {(file.status === "uploading" || file.status === "pending") && (
          <Progress value={progress} className="h-1.5" />
        )}

        {file.status === "error" && onRetry && (
          <button
            className="text-xs text-primary hover:underline"
            onClick={() => onRetry(file.id)}
          >
            Retry
          </button>
        )}
      </div>
    </div>
  )
}

interface UploadProgressProps extends React.HTMLAttributes<HTMLDivElement> {
  files: UploadFile[]
  onRemove?: (id: string) => void
  onRetry?: (id: string) => void
  title?: string
}

function UploadProgress({ files, onRemove, onRetry, title = "Uploads", className, ...props }: UploadProgressProps) {
  if (files.length === 0) return null

  const successCount = files.filter((f) => f.status === "success").length
  const totalProgress =
    files.reduce((sum, f) => sum + (f.progress ?? (f.status === "success" ? 100 : 0)), 0) / files.length

  return (
    <div
      data-slot="upload-progress"
      className={cn("space-y-2 rounded-lg border p-4", className)}
      {...props}
    >
      <div className="flex items-center justify-between gap-2">
        <p className="text-sm font-medium">{title}</p>
        <p className="text-xs text-muted-foreground">
          {successCount}/{files.length} uploaded
        </p>
      </div>

      {files.some((f) => f.status === "uploading") && (
        <Progress value={totalProgress} className="h-1" />
      )}

      <div className="space-y-2">
        {files.map((file) => (
          <UploadProgressItem
            key={file.id}
            file={file}
            onRemove={onRemove}
            onRetry={onRetry}
          />
        ))}
      </div>
    </div>
  )
}

export { UploadProgress, UploadProgressItem, formatBytes }
export type { UploadFile, UploadStatus, UploadProgressProps, UploadProgressItemProps }
