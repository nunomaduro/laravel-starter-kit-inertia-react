import * as React from "react"
import {
  PauseIcon,
  PlayIcon,
  SkipBackIcon,
  SkipForwardIcon,
  Volume2Icon,
  VolumeXIcon,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Slider } from "@/components/ui/slider"

export interface AudioTrack {
  id: string | number
  title: string
  artist?: string
  src: string
  duration?: number
  cover?: string
}

interface AudioPlayerProps {
  track?: AudioTrack
  tracks?: AudioTrack[]
  className?: string
  autoPlay?: boolean
  showPlaylist?: boolean
}

function formatTime(seconds: number): string {
  const m = Math.floor(seconds / 60)
  const s = Math.floor(seconds % 60)
  return `${m}:${s.toString().padStart(2, "0")}`
}

function AudioPlayer({
  track,
  tracks = [],
  className,
  autoPlay = false,
  showPlaylist = false,
}: AudioPlayerProps) {
  const audioRef = React.useRef<HTMLAudioElement>(null)
  const allTracks = track ? [track, ...tracks.filter((t) => t.id !== track.id)] : tracks
  const [currentIndex, setCurrentIndex] = React.useState(0)
  const [isPlaying, setIsPlaying] = React.useState(false)
  const [currentTime, setCurrentTime] = React.useState(0)
  const [duration, setDuration] = React.useState(0)
  const [volume, setVolume] = React.useState(1)
  const [isMuted, setIsMuted] = React.useState(false)

  const currentTrack = allTracks[currentIndex]

  const togglePlay = () => {
    const audio = audioRef.current
    if (!audio) return
    if (isPlaying) {
      audio.pause()
    } else {
      void audio.play()
    }
    setIsPlaying(!isPlaying)
  }

  const skipTo = (index: number) => {
    const clamped = Math.max(0, Math.min(allTracks.length - 1, index))
    setCurrentIndex(clamped)
    setIsPlaying(autoPlay)
  }

  const handleVolumeChange = ([v]: number[]) => {
    const newVolume = v ?? 1
    setVolume(newVolume)
    if (audioRef.current) audioRef.current.volume = newVolume
    setIsMuted(newVolume === 0)
  }

  const toggleMute = () => {
    const audio = audioRef.current
    if (!audio) return
    const newMuted = !isMuted
    audio.muted = newMuted
    setIsMuted(newMuted)
  }

  return (
    <div
      data-slot="audio-player"
      className={cn(
        "flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm",
        className
      )}
    >
      {currentTrack && (
        <>
          {currentTrack.cover && (
            <img
              src={currentTrack.cover}
              alt={currentTrack.title}
              className="aspect-square w-full rounded-lg object-cover"
            />
          )}
          <div>
            <p className="font-semibold">{currentTrack.title}</p>
            {currentTrack.artist && (
              <p className="text-sm text-muted-foreground">{currentTrack.artist}</p>
            )}
          </div>
        </>
      )}
      <audio
        ref={audioRef}
        src={currentTrack?.src}
        onTimeUpdate={() => setCurrentTime(audioRef.current?.currentTime ?? 0)}
        onDurationChange={() => setDuration(audioRef.current?.duration ?? 0)}
        onEnded={() => {
          if (currentIndex < allTracks.length - 1) {
            skipTo(currentIndex + 1)
          } else {
            setIsPlaying(false)
          }
        }}
        autoPlay={autoPlay}
      />
      <div className="flex flex-col gap-1.5">
        <Slider
          value={[currentTime]}
          max={duration || 1}
          step={0.5}
          onValueChange={([v]) => {
            if (v === undefined) return
            setCurrentTime(v)
            if (audioRef.current) audioRef.current.currentTime = v
          }}
          className="w-full"
        />
        <div className="flex justify-between text-xs text-muted-foreground">
          <span>{formatTime(currentTime)}</span>
          <span>{formatTime(duration)}</span>
        </div>
      </div>
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-1">
          <Button variant="ghost" size="icon-sm" onClick={toggleMute}>
            {isMuted ? (
              <VolumeXIcon className="size-4" />
            ) : (
              <Volume2Icon className="size-4" />
            )}
          </Button>
          <Slider
            value={[isMuted ? 0 : volume]}
            max={1}
            step={0.02}
            onValueChange={handleVolumeChange}
            className="w-20"
          />
        </div>
        <div className="flex items-center gap-1">
          <Button
            variant="ghost"
            size="icon-sm"
            onClick={() => skipTo(currentIndex - 1)}
            disabled={currentIndex === 0}
            aria-label="Previous track"
          >
            <SkipBackIcon className="size-4" />
          </Button>
          <Button variant="default" size="icon" onClick={togglePlay} aria-label={isPlaying ? 'Pause' : 'Play'}>
            {isPlaying ? (
              <PauseIcon className="size-5" />
            ) : (
              <PlayIcon className="size-5" />
            )}
          </Button>
          <Button
            variant="ghost"
            size="icon-sm"
            onClick={() => skipTo(currentIndex + 1)}
            disabled={currentIndex >= allTracks.length - 1}
            aria-label="Next track"
          >
            <SkipForwardIcon className="size-4" />
          </Button>
        </div>
        <div className="w-28" />
      </div>
      {showPlaylist && allTracks.length > 1 && (
        <div className="space-y-1 border-t pt-3">
          {allTracks.map((t, i) => (
            <button
              key={t.id}
              type="button"
              className={cn(
                "flex w-full items-center gap-3 rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-muted",
                i === currentIndex && "bg-muted font-medium"
              )}
              onClick={() => skipTo(i)}
            >
              <span className="w-4 shrink-0 text-xs text-muted-foreground">
                {i + 1}
              </span>
              <span className="truncate">{t.title}</span>
              {t.artist && (
                <span className="ml-auto shrink-0 text-xs text-muted-foreground">
                  {t.artist}
                </span>
              )}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}

export { AudioPlayer }
