import * as React from "react"
import {
  MaximizeIcon,
  PauseIcon,
  PlayIcon,
  Volume2Icon,
  VolumeXIcon,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { Slider } from "@/components/ui/slider"
import { useReducedMotion } from "@/hooks/use-reduced-motion"

const SPEEDS = [0.5, 0.75, 1, 1.25, 1.5, 2]

function formatTime(seconds: number): string {
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  const s = Math.floor(seconds % 60)
  if (h > 0) return `${h}:${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`
  return `${m}:${String(s).padStart(2, "0")}`
}

interface VideoPlayerProps {
  src: string
  poster?: string
  autoPlay?: boolean
  loop?: boolean
  muted?: boolean
  className?: string
  onEnded?: () => void
}

function VideoPlayer({
  src,
  poster,
  autoPlay = false,
  loop = false,
  muted = false,
  className,
  onEnded,
}: VideoPlayerProps) {
  const videoRef = React.useRef<HTMLVideoElement>(null)
  const containerRef = React.useRef<HTMLDivElement>(null)
  const reducedMotion = useReducedMotion()

  const [isPlaying, setIsPlaying] = React.useState(false)
  const [currentTime, setCurrentTime] = React.useState(0)
  const [duration, setDuration] = React.useState(0)
  const [volume, setVolume] = React.useState(muted ? 0 : 1)
  const [isMuted, setIsMuted] = React.useState(muted)
  const [speedIndex, setSpeedIndex] = React.useState(2)
  const [showControls, setShowControls] = React.useState(true)
  const hideTimerRef = React.useRef<ReturnType<typeof setTimeout>>(null)

  const effectiveAutoPlay = autoPlay && !reducedMotion

  const resetHideTimer = () => {
    setShowControls(true)
    if (hideTimerRef.current) clearTimeout(hideTimerRef.current)
    hideTimerRef.current = setTimeout(() => {
      if (isPlaying) setShowControls(false)
    }, 2500)
  }

  const togglePlay = () => {
    const v = videoRef.current
    if (!v) return
    if (isPlaying) {
      v.pause()
      setIsPlaying(false)
      setShowControls(true)
    } else {
      void v.play()
      setIsPlaying(true)
      resetHideTimer()
    }
  }

  const toggleMute = () => {
    const v = videoRef.current
    if (!v) return
    const next = !isMuted
    v.muted = next
    setIsMuted(next)
  }

  const handleVolumeChange = ([v]: number[]) => {
    const val = v ?? 1
    setVolume(val)
    if (videoRef.current) videoRef.current.volume = val
    setIsMuted(val === 0)
  }

  const handleScrub = ([v]: number[]) => {
    const val = v ?? 0
    setCurrentTime(val)
    if (videoRef.current) videoRef.current.currentTime = val
  }

  const cycleSpeed = () => {
    const next = (speedIndex + 1) % SPEEDS.length
    setSpeedIndex(next)
    if (videoRef.current) videoRef.current.playbackRate = SPEEDS[next]!
  }

  const requestFullscreen = () => {
    containerRef.current?.requestFullscreen?.()
  }

  return (
    <div
      ref={containerRef}
      data-slot="video-player"
      className={cn(
        "group relative overflow-hidden rounded-lg bg-black",
        className
      )}
      onMouseMove={resetHideTimer}
      onMouseLeave={() => isPlaying && setShowControls(false)}
    >
      <video
        ref={videoRef}
        src={src}
        poster={poster}
        autoPlay={effectiveAutoPlay}
        loop={loop}
        muted={isMuted}
        className="aspect-video w-full"
        onClick={togglePlay}
        onTimeUpdate={() => setCurrentTime(videoRef.current?.currentTime ?? 0)}
        onDurationChange={() => setDuration(videoRef.current?.duration ?? 0)}
        onPlay={() => setIsPlaying(true)}
        onPause={() => setIsPlaying(false)}
        onEnded={() => {
          setIsPlaying(false)
          setShowControls(true)
          onEnded?.()
        }}
      />

      <div
        className={cn(
          "absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-4 transition-opacity duration-200",
          showControls ? "opacity-100" : "opacity-0"
        )}
      >
        <Slider
          value={[currentTime]}
          max={duration || 1}
          step={0.1}
          onValueChange={handleScrub}
          className="mb-3"
        />
        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={togglePlay}
            className="text-white hover:text-white/80"
          >
            {isPlaying ? (
              <PauseIcon className="size-5 fill-current" />
            ) : (
              <PlayIcon className="size-5 fill-current" />
            )}
          </button>

          <div className="flex items-center gap-1.5">
            <button
              type="button"
              onClick={toggleMute}
              className="text-white hover:text-white/80"
            >
              {isMuted ? (
                <VolumeXIcon className="size-4" />
              ) : (
                <Volume2Icon className="size-4" />
              )}
            </button>
            <Slider
              value={[isMuted ? 0 : volume]}
              max={1}
              step={0.02}
              onValueChange={handleVolumeChange}
              className="w-16"
            />
          </div>

          <span className="text-xs text-white/80">
            {formatTime(currentTime)} / {formatTime(duration)}
          </span>

          <div className="ml-auto flex items-center gap-2">
            <button
              type="button"
              onClick={cycleSpeed}
              className="text-xs font-medium text-white hover:text-white/80"
            >
              {SPEEDS[speedIndex]}×
            </button>
            <button
              type="button"
              onClick={requestFullscreen}
              className="text-white hover:text-white/80"
            >
              <MaximizeIcon className="size-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export { VideoPlayer }
