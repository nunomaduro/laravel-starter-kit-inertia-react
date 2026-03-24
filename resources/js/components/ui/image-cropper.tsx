import * as React from "react"
import { CropIcon, RotateCcwIcon, ZoomInIcon, ZoomOutIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Slider } from "@/components/ui/slider"

interface CropArea {
  x: number
  y: number
  width: number
  height: number
}

interface ImageCropperProps {
  src: string
  aspect?: number
  onCrop?: (croppedDataUrl: string, cropArea: CropArea) => void
  onCancel?: () => void
  className?: string
  cropShape?: "rect" | "round"
}

function ImageCropper({
  src,
  aspect = 1,
  onCrop,
  onCancel,
  className,
  cropShape = "rect",
}: ImageCropperProps) {
  const [zoom, setZoom] = React.useState(1)
  const [rotation, setRotation] = React.useState(0)
  const [offset, setOffset] = React.useState({ x: 0, y: 0 })
  const isDraggingRef = React.useRef(false)
  const lastPosRef = React.useRef({ x: 0, y: 0 })
  const canvasRef = React.useRef<HTMLCanvasElement>(null)
  const imgRef = React.useRef<HTMLImageElement | null>(null)

  React.useEffect(() => {
    const img = new Image()
    img.crossOrigin = "anonymous"
    img.onload = () => {
      imgRef.current = img
      drawCanvas()
    }
    img.src = src
  }, [src])

  const drawCanvas = React.useCallback(() => {
    const canvas = canvasRef.current
    const img = imgRef.current
    if (!canvas || !img) return
    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const w = canvas.width
    const h = canvas.height
    ctx.clearRect(0, 0, w, h)
    ctx.save()
    ctx.translate(w / 2 + offset.x, h / 2 + offset.y)
    ctx.rotate((rotation * Math.PI) / 180)
    ctx.scale(zoom, zoom)
    ctx.drawImage(img, -img.width / 2, -img.height / 2)
    ctx.restore()
  }, [zoom, rotation, offset])

  React.useEffect(() => {
    drawCanvas()
  }, [drawCanvas])

  const handleMouseDown = (e: React.MouseEvent<HTMLCanvasElement>) => {
    isDraggingRef.current = true
    lastPosRef.current = { x: e.clientX, y: e.clientY }
  }

  const handleMouseMove = (e: React.MouseEvent<HTMLCanvasElement>) => {
    if (!isDraggingRef.current) return
    const dx = e.clientX - lastPosRef.current.x
    const dy = e.clientY - lastPosRef.current.y
    lastPosRef.current = { x: e.clientX, y: e.clientY }
    setOffset((prev) => ({ x: prev.x + dx, y: prev.y + dy }))
  }

  const handleMouseUp = () => {
    isDraggingRef.current = false
  }

  const handleCrop = () => {
    const canvas = canvasRef.current
    if (!canvas) return
    const w = canvas.width
    const h = canvas.height
    const cropSize = Math.min(w, h) * 0.7
    const cropX = (w - cropSize) / 2
    const cropY = (h - cropSize) / 2

    const outputCanvas = document.createElement("canvas")
    const outputSize = cropSize * aspect
    outputCanvas.width = outputSize
    outputCanvas.height = cropSize
    const ctx = outputCanvas.getContext("2d")
    if (!ctx) return
    ctx.drawImage(canvas, cropX, cropY, cropSize * aspect, cropSize, 0, 0, outputSize, cropSize)
    const dataUrl = outputCanvas.toDataURL("image/png")
    onCrop?.(dataUrl, { x: cropX, y: cropY, width: cropSize * aspect, height: cropSize })
  }

  return (
    <div
      data-slot="image-cropper"
      className={cn("flex flex-col gap-4", className)}
    >
      <div className="relative overflow-hidden rounded-lg border bg-muted">
        <canvas
          ref={canvasRef}
          width={400}
          height={400}
          className="w-full cursor-move"
          onMouseDown={handleMouseDown}
          onMouseMove={handleMouseMove}
          onMouseUp={handleMouseUp}
          onMouseLeave={handleMouseUp}
        />
        <div
          className={cn(
            "pointer-events-none absolute inset-0 m-auto border-2 border-white shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]",
            cropShape === "round" ? "rounded-full" : "rounded-sm"
          )}
          style={{ width: "70%", height: "70%" }}
        />
      </div>
      <div className="flex items-center gap-2">
        <ZoomOutIcon className="size-4 shrink-0 text-muted-foreground" />
        <Slider
          value={[zoom]}
          onValueChange={([v]) => v !== undefined && setZoom(v)}
          min={0.5}
          max={3}
          step={0.05}
          className="flex-1"
        />
        <ZoomInIcon className="size-4 shrink-0 text-muted-foreground" />
      </div>
      <div className="flex gap-2">
        {onCancel && (
          <Button variant="outline" onClick={onCancel} className="flex-1">
            Cancel
          </Button>
        )}
        <Button
          onClick={handleCrop}
          className="flex-1"
        >
          <CropIcon className="mr-2 size-4" />
          Crop
        </Button>
        <Button
          variant="outline"
          size="icon"
          onClick={() => setRotation((r) => r - 90)}
          aria-label="Rotate image"
        >
          <RotateCcwIcon className="size-4" />
        </Button>
      </div>
    </div>
  )
}

export { ImageCropper }
export type { CropArea }
