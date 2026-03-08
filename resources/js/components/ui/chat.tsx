import * as React from "react"
import { SendIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"

export interface ChatMessage {
  id: string | number
  content: string
  role: "user" | "assistant" | "system"
  timestamp?: Date
  avatar?: string
  name?: string
  isStreaming?: boolean
}

interface ChatProps {
  messages?: ChatMessage[]
  onSend?: (message: string) => void
  placeholder?: string
  className?: string
  isLoading?: boolean
  currentUserId?: string
}

function Chat({
  messages = [],
  onSend,
  placeholder = "Type a message...",
  className,
  isLoading = false,
}: ChatProps) {
  const [input, setInput] = React.useState("")
  const bottomRef = React.useRef<HTMLDivElement>(null)

  React.useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: "smooth" })
  }, [messages])

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const trimmed = input.trim()
    if (!trimmed || isLoading) return
    onSend?.(trimmed)
    setInput("")
  }

  return (
    <div
      data-slot="chat"
      className={cn("flex h-full flex-col", className)}
    >
      <div className="flex-1 overflow-y-auto p-4 space-y-4">
        {messages.map((message) => (
          <ChatMessageBubble
            key={message.id}
            message={message}
            isOwn={message.role === "user"}
          />
        ))}
        {isLoading && (
          <div className="flex gap-3">
            <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium">
              AI
            </div>
            <div className="rounded-2xl rounded-tl-sm bg-muted px-4 py-2">
              <span className="inline-flex gap-1">
                <span className="size-1.5 animate-bounce rounded-full bg-muted-foreground [animation-delay:0ms]" />
                <span className="size-1.5 animate-bounce rounded-full bg-muted-foreground [animation-delay:150ms]" />
                <span className="size-1.5 animate-bounce rounded-full bg-muted-foreground [animation-delay:300ms]" />
              </span>
            </div>
          </div>
        )}
        <div ref={bottomRef} />
      </div>
      {onSend && (
        <form
          onSubmit={handleSubmit}
          className="border-t p-4"
        >
          <div className="flex gap-2">
            <input
              type="text"
              value={input}
              onChange={(e) => setInput(e.target.value)}
              placeholder={placeholder}
              disabled={isLoading}
              className="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            />
            <Button
              type="submit"
              size="icon"
              disabled={!input.trim() || isLoading}
            >
              <SendIcon className="size-4" />
              <span className="sr-only">Send</span>
            </Button>
          </div>
        </form>
      )}
    </div>
  )
}

function ChatMessageBubble({
  message,
  isOwn,
}: {
  message: ChatMessage
  isOwn: boolean
}) {
  const initials = message.name
    ? message.name.split(" ").map((n) => n[0]).join("").toUpperCase()
    : message.role === "user" ? "U" : "AI"

  return (
    <div
      data-slot="chat-message"
      className={cn("flex gap-3", isOwn && "flex-row-reverse")}
    >
      <Avatar className="size-8 shrink-0">
        {message.avatar && <AvatarImage src={message.avatar} />}
        <AvatarFallback className="text-xs">{initials}</AvatarFallback>
      </Avatar>
      <div
        className={cn(
          "max-w-[70%] rounded-2xl px-4 py-2 text-sm",
          isOwn
            ? "rounded-tr-sm bg-primary text-primary-foreground"
            : "rounded-tl-sm bg-muted"
        )}
      >
        {message.name && !isOwn && (
          <p className="mb-0.5 text-xs font-medium text-muted-foreground">
            {message.name}
          </p>
        )}
        <p className="whitespace-pre-wrap break-words">{message.content}</p>
        {message.timestamp && (
          <p
            className={cn(
              "mt-0.5 text-xs",
              isOwn ? "text-primary-foreground/60" : "text-muted-foreground"
            )}
          >
            {message.timestamp.toLocaleTimeString([], {
              hour: "2-digit",
              minute: "2-digit",
            })}
          </p>
        )}
      </div>
    </div>
  )
}

export { Chat, ChatMessageBubble }
