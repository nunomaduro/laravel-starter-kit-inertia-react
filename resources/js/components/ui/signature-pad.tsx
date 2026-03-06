import * as React from "react"
import { RotateCcwIcon, SaveIcon } from "lucide-react"
import SignatureCanvas from "react-signature-canvas"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

interface SignaturePadProps {
  onSave?: (dataUrl: string) => void
  onClear?: () => void
  width?: number
  height?: number
  penColor?: string
  backgroundColor?: string
  className?: string
  label?: string
}

function SignaturePad({
  onSave,
  onClear,
  width,
  height = 200,
  penColor = "#000000",
  backgroundColor = "rgba(255,255,255,0)",
  className,
  label = "Sign here",
}: SignaturePadProps) {
  const sigRef = React.useRef<SignatureCanvas>(null)
  const containerRef = React.useRef<HTMLDivElement>(null)
  const [isEmpty, setIsEmpty] = React.useState(true)
  const [canvasWidth, setCanvasWidth] = React.useState(width ?? 500)

  React.useEffect(() => {
    if (width || !containerRef.current) return
    const observer = new ResizeObserver(() => {
      if (containerRef.current) {
        setCanvasWidth(containerRef.current.offsetWidth)
      }
    })
    observer.observe(containerRef.current)
    setCanvasWidth(containerRef.current.offsetWidth)
    return () => observer.disconnect()
  }, [width])

  const handleClear = () => {
    sigRef.current?.clear()
    setIsEmpty(true)
    onClear?.()
  }

  const handleSave = () => {
    if (!sigRef.current || sigRef.current.isEmpty()) return
    const dataUrl = sigRef.current.toDataURL("image/png")
    onSave?.(dataUrl)
  }

  return (
    <div
      data-slot="signature-pad"
      className={cn("flex flex-col gap-3", className)}
    >
      <div
        ref={containerRef}
        className="relative overflow-hidden rounded-lg border bg-white dark:bg-white"
        style={{ height }}
      >
        <SignatureCanvas
          ref={sigRef}
          penColor={penColor}
          backgroundColor={backgroundColor}
          canvasProps={{
            width: canvasWidth,
            height,
            className: "block",
          }}
          onBegin={() => setIsEmpty(false)}
        />
        {isEmpty && (
          <div className="pointer-events-none absolute inset-0 flex items-end justify-center pb-4">
            <p className="text-sm text-gray-300">{label}</p>
          </div>
        )}
        <div className="pointer-events-none absolute inset-x-4 bottom-2 border-t border-dashed border-gray-300" />
      </div>
      <div className="flex justify-end gap-2">
        <Button
          variant="outline"
          size="sm"
          onClick={handleClear}
          disabled={isEmpty}
        >
          <RotateCcwIcon className="size-3.5" />
          Clear
        </Button>
        <Button
          size="sm"
          onClick={handleSave}
          disabled={isEmpty}
        >
          <SaveIcon className="size-3.5" />
          Save
        </Button>
      </div>
    </div>
  )
}

export { SignaturePad }
