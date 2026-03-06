import * as React from "react"
import { ChevronLeftIcon, ChevronRightIcon, ZoomInIcon, ZoomOutIcon } from "lucide-react"
import { Document, Page, pdfjs } from "react-pdf"
import "react-pdf/dist/Page/AnnotationLayer.css"
import "react-pdf/dist/Page/TextLayer.css"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Skeleton } from "@/components/ui/skeleton"

pdfjs.GlobalWorkerOptions.workerSrc = new URL(
  "pdfjs-dist/build/pdf.worker.min.mjs",
  import.meta.url
).toString()

interface PdfViewerProps {
  url?: string
  file?: File | Blob
  className?: string
  defaultPage?: number
  defaultScale?: number
}

function PdfViewer({
  url,
  file,
  className,
  defaultPage = 1,
  defaultScale = 1,
}: PdfViewerProps) {
  const [numPages, setNumPages] = React.useState<number>(0)
  const [currentPage, setCurrentPage] = React.useState(defaultPage)
  const [scale, setScale] = React.useState(defaultScale)
  const [isLoading, setIsLoading] = React.useState(true)

  const source = url ?? file

  if (!source) return null

  const goToPrev = () => setCurrentPage((p) => Math.max(1, p - 1))
  const goToNext = () => setCurrentPage((p) => Math.min(numPages, p + 1))
  const zoomIn = () => setScale((s) => Math.min(3, s + 0.25))
  const zoomOut = () => setScale((s) => Math.max(0.5, s - 0.25))

  return (
    <div
      data-slot="pdf-viewer"
      className={cn("flex flex-col items-center gap-3", className)}
    >
      <div className="flex items-center gap-2 rounded-lg border bg-card px-3 py-1.5 shadow-sm">
        <Button
          variant="ghost"
          size="icon"
          className="size-7"
          onClick={goToPrev}
          disabled={currentPage <= 1}
        >
          <ChevronLeftIcon className="size-4" />
        </Button>
        <span className="text-sm">
          {currentPage} / {numPages || "—"}
        </span>
        <Button
          variant="ghost"
          size="icon"
          className="size-7"
          onClick={goToNext}
          disabled={currentPage >= numPages}
        >
          <ChevronRightIcon className="size-4" />
        </Button>
        <div className="mx-1 h-4 w-px bg-border" />
        <Button variant="ghost" size="icon" className="size-7" onClick={zoomOut}>
          <ZoomOutIcon className="size-4" />
        </Button>
        <span className="text-xs text-muted-foreground">{Math.round(scale * 100)}%</span>
        <Button variant="ghost" size="icon" className="size-7" onClick={zoomIn}>
          <ZoomInIcon className="size-4" />
        </Button>
      </div>

      <div className="relative overflow-auto rounded-lg border shadow">
        {isLoading && (
          <div className="absolute inset-0 flex items-center justify-center bg-background">
            <div className="space-y-3 p-8">
              <Skeleton className="h-6 w-3/4" />
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-2/3" />
            </div>
          </div>
        )}
        <Document
          file={source}
          onLoadSuccess={({ numPages: n }) => {
            setNumPages(n)
            setIsLoading(false)
          }}
          loading={null}
        >
          <Page
            pageNumber={currentPage}
            scale={scale}
            renderAnnotationLayer
            renderTextLayer
          />
        </Document>
      </div>
    </div>
  )
}

export { PdfViewer }
