import * as React from "react"
import {
  DownloadIcon,
  FileArchiveIcon,
  FileAudioIcon,
  FileCodeIcon,
  FileIcon,
  FileImageIcon,
  FileSpreadsheetIcon,
  FileTextIcon,
  FileVideoIcon,
  MoreHorizontalIcon,
  TrashIcon,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

export interface FileItemData {
  id: string | number
  name: string
  size?: number
  type?: string
  url?: string
  uploadedAt?: string
  uploadedBy?: string
  thumbnail?: string
}

function getFileIcon(type?: string): React.ReactNode {
  if (!type) return <FileIcon className="size-4" />
  if (type.startsWith("image/")) return <FileImageIcon className="size-4" />
  if (type.startsWith("video/")) return <FileVideoIcon className="size-4" />
  if (type.startsWith("audio/")) return <FileAudioIcon className="size-4" />
  if (type === "application/pdf") return <FileTextIcon className="size-4" />
  if (type.includes("spreadsheet") || type.includes("excel"))
    return <FileSpreadsheetIcon className="size-4" />
  if (type.includes("zip") || type.includes("archive"))
    return <FileArchiveIcon className="size-4" />
  if (type.includes("text/plain")) return <FileTextIcon className="size-4" />
  if (
    type.includes("javascript") ||
    type.includes("typescript") ||
    type.includes("html") ||
    type.includes("css") ||
    type.includes("json")
  )
    return <FileCodeIcon className="size-4" />
  return <FileIcon className="size-4" />
}

function formatFileSize(bytes?: number): string {
  if (!bytes) return ""
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

interface FileItemProps {
  file: FileItemData
  onDownload?: (file: FileItemData) => void
  onDelete?: (file: FileItemData) => void
  className?: string
}

function FileItem({ file, onDownload, onDelete, className }: FileItemProps) {
  const icon = getFileIcon(file.type)
  const size = formatFileSize(file.size)

  return (
    <div
      data-slot="file-item"
      className={cn(
        "flex items-center gap-3 rounded-lg border bg-card px-3 py-2.5 text-sm shadow-sm",
        className
      )}
    >
      <div className="shrink-0 rounded-md bg-muted p-2 text-muted-foreground">
        {icon}
      </div>
      <div className="min-w-0 flex-1">
        <p className="truncate font-medium">{file.name}</p>
        <p className="text-xs text-muted-foreground">
          {[size, file.uploadedAt, file.uploadedBy].filter(Boolean).join(" · ")}
        </p>
      </div>
      <div className="flex shrink-0 items-center gap-1">
        {(onDownload ?? file.url) && (
          <Button
            variant="ghost"
            size="icon"
            className="size-7"
            onClick={() => {
              if (onDownload) {
                onDownload(file)
              } else if (file.url) {
                window.open(file.url, "_blank")
              }
            }}
          >
            <DownloadIcon className="size-3.5" />
            <span className="sr-only">Download</span>
          </Button>
        )}
        {onDelete && (
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" size="icon" className="size-7">
                <MoreHorizontalIcon className="size-3.5" />
                <span className="sr-only">More options</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem
                className="text-destructive"
                onClick={() => onDelete(file)}
              >
                <TrashIcon className="size-4" />
                Delete
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        )}
      </div>
    </div>
  )
}

export { FileItem, getFileIcon, formatFileSize }
