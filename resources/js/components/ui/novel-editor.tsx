import * as React from "react"
import { EditorRoot, EditorContent as NovelEditorContent, type EditorContentProps } from "novel"

import { cn } from "@/lib/utils"

interface NovelEditorProps {
  value?: string
  defaultValue?: string
  onChange?: (html: string) => void
  aiEndpoint?: string
  className?: string
  editorClassName?: string
  placeholder?: string
}

/**
 * NovelEditorWrapper - a minimal Novel (Tiptap-based) editor with AI completion support.
 * Connect AI suggestions to the Laravel AI SDK via the `aiEndpoint` prop (default: /ai/complete).
 */
function NovelEditorWrapper({
  defaultValue,
  onChange,
  aiEndpoint = "/ai/complete",
  className,
  editorClassName,
  placeholder = "Start writing...",
}: NovelEditorProps) {
  const contentProps: Partial<EditorContentProps> = {}

  return (
    <EditorRoot>
      <div
        data-slot="novel-editor"
        data-completion-api={aiEndpoint}
        className={cn(
          "border-input bg-background rounded-md border shadow-xs",
          "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
          className
        )}
      >
        <NovelEditorContent
          {...contentProps}
          initialContent={
            defaultValue
              ? {
                  type: "doc",
                  content: [{ type: "paragraph", content: [{ type: "text", text: defaultValue }] }],
                }
              : undefined
          }
          onUpdate={({ editor }) => {
            onChange?.(editor.getHTML())
          }}
          editorProps={{
            attributes: {
              class: cn(
                "prose prose-sm dark:prose-invert max-w-none min-h-[200px] p-4 focus:outline-none",
                editorClassName
              ),
            },
          }}
        />
      </div>
    </EditorRoot>
  )
}

export { NovelEditorWrapper as NovelEditor }
