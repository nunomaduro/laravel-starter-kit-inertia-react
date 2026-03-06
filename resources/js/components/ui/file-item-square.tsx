import * as React from "react"
import { DownloadIcon, TrashIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { type FileItemData, getFileIcon } from "@/components/ui/file-item"

interface FileItemSquareProps {
  file: FileItemData
  onDownload?: (file: FileItemData) => void
  onDelete?: (file: FileItemData) => void
  className?: string
}

function FileItemSquare({ file, onDownload, onDelete, className }: FileItemSquareProps) {
  const isImage = file.type?.startsWith("image/")

  return (
    <div
      data-slot="file-item-square"
      className={cn(
        "group relative flex aspect-square flex-col items-center justify-center gap-2 overflow-hidden rounded-lg border bg-card p-3 text-center shadow-sm",
        className
      )}
    >
      {isImage && file.thumbnail ? (
        <img
          src={file.thumbnail}
          alt={file.name}
          className="absolute inset-0 h-full w-full object-cover"
        />
      ) : (
        <div className="rounded-xl bg-muted p-3 text-muted-foreground">
          {getFileIcon(file.type)}
        </div>
      )}

      <div className="relative z-10 mt-auto w-full">
        {isImage && file.thumbnail && (
          <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-2">
            <p className="truncate text-xs font-medium text-white">{file.name}</p>
          </div>
        )}
        {(!isImage || !file.thumbnail) && (
          <p className="truncate text-xs font-medium">{file.name}</p>
        )}
      </div>

      <div className="absolute right-1 top-1 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
        {(onDownload ?? file.url) && (
          <Button
            variant="secondary"
            size="icon"
            className="size-6"
            onClick={() => {
              if (onDownload) {
                onDownload(file)
              } else if (file.url) {
                window.open(file.url, "_blank")
              }
            }}
          >
            <DownloadIcon className="size-3" />
            <span className="sr-only">Download</span>
          </Button>
        )}
        {onDelete && (
          <Button
            variant="destructive"
            size="icon"
            className="size-6"
            onClick={() => onDelete(file)}
          >
            <XIcon className="size-3" />
            <span className="sr-only">Delete</span>
          </Button>
        )}
      </div>
    </div>
  )
}

export { FileItemSquare }
