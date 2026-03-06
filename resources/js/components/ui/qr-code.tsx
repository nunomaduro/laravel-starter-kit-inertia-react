import * as React from "react"
import { DownloadIcon } from "lucide-react"
import { QRCodeCanvas, QRCodeSVG } from "qrcode.react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

type ErrorCorrectionLevel = "L" | "M" | "Q" | "H"

interface QrCodeProps {
  value: string
  size?: number
  level?: ErrorCorrectionLevel
  includeMargin?: boolean
  showDownload?: boolean
  fileName?: string
  className?: string
  fgColor?: string
  bgColor?: string
}

function QrCode({
  value,
  size = 200,
  level = "M",
  includeMargin = true,
  showDownload = true,
  fileName = "qrcode",
  className,
  fgColor,
  bgColor,
}: QrCodeProps) {
  const canvasRef = React.useRef<HTMLCanvasElement>(null)

  const handleDownload = () => {
    const canvas = canvasRef.current
    if (!canvas) return
    const link = document.createElement("a")
    link.href = canvas.toDataURL("image/png")
    link.download = `${fileName}.png`
    link.click()
  }

  return (
    <div
      data-slot="qr-code"
      className={cn("inline-flex flex-col items-center gap-3", className)}
    >
      <div className="rounded-lg border bg-white p-3 shadow-sm">
        <QRCodeCanvas
          ref={canvasRef}
          value={value}
          size={size}
          level={level}
          includeMargin={includeMargin}
          fgColor={fgColor ?? "#000000"}
          bgColor={bgColor ?? "#ffffff"}
        />
      </div>
      {showDownload && (
        <Button
          variant="outline"
          size="sm"
          className="gap-2"
          onClick={handleDownload}
        >
          <DownloadIcon className="size-3.5" />
          Download PNG
        </Button>
      )}
    </div>
  )
}

export { QrCode }
