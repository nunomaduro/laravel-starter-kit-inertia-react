import * as React from "react"
import { Dialog as DialogPrimitive } from "radix-ui"
import { XIcon } from "lucide-react"
import * as SheetPrimitive from "@radix-ui/react-dialog"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"

function useIsMobile(): boolean {
  const [isMobile, setIsMobile] = React.useState(false)
  React.useEffect(() => {
    const mq = window.matchMedia("(max-width: 768px)")
    setIsMobile(mq.matches)
    const handler = (e: MediaQueryListEvent) => setIsMobile(e.matches)
    mq.addEventListener("change", handler)
    return () => mq.removeEventListener("change", handler)
  }, [])
  return isMobile
}

type CredenzaProps = React.ComponentProps<typeof DialogPrimitive.Root>

function Credenza({ children, ...props }: CredenzaProps) {
  return (
    <DialogPrimitive.Root data-slot="credenza" {...props}>
      {children}
    </DialogPrimitive.Root>
  )
}

function CredenzaTrigger({
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Trigger>) {
  return <DialogPrimitive.Trigger data-slot="credenza-trigger" {...props} />
}

function CredenzaClose({
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Close>) {
  return <DialogPrimitive.Close data-slot="credenza-close" {...props} />
}

function CredenzaContent({
  className,
  children,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Content>) {
  const isMobile = useIsMobile()

  if (isMobile) {
    return (
      <SheetPrimitive.Portal>
        <SheetPrimitive.Overlay className="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-50 bg-black/80" />
        <SheetPrimitive.Content
          data-slot="credenza-content"
          className={cn(
            "bg-background data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom fixed inset-x-0 bottom-0 z-50 flex flex-col gap-4 rounded-t-xl border-t p-6 shadow-lg",
            className
          )}
          {...(props as React.ComponentProps<typeof SheetPrimitive.Content>)}
        >
          <div className="mx-auto mb-2 h-1.5 w-12 rounded-full bg-muted" />
          {children}
          <SheetPrimitive.Close className="absolute right-4 top-4 rounded-xs opacity-70 hover:opacity-100">
            <XIcon className="size-4" />
            <span className="sr-only">Close</span>
          </SheetPrimitive.Close>
        </SheetPrimitive.Content>
      </SheetPrimitive.Portal>
    )
  }

  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 fixed inset-0 z-50 bg-black/50" />
      <DialogPrimitive.Content
        data-slot="credenza-content"
        className={cn(
          "bg-background data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed top-[50%] left-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 rounded-xl border p-6 shadow-lg duration-200",
          className
        )}
        {...props}
      >
        {children}
        <DialogPrimitive.Close className="absolute right-4 top-4 rounded-xs opacity-70 hover:opacity-100">
          <XIcon className="size-4" />
          <span className="sr-only">Close</span>
        </DialogPrimitive.Close>
      </DialogPrimitive.Content>
    </DialogPrimitive.Portal>
  )
}

function CredenzaHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="credenza-header"
      className={cn("flex flex-col gap-1.5", className)}
      {...props}
    />
  )
}

function CredenzaFooter({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="credenza-footer"
      className={cn("flex flex-col-reverse gap-2 sm:flex-row sm:justify-end", className)}
      {...props}
    />
  )
}

function CredenzaTitle({
  className,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Title>) {
  return (
    <DialogPrimitive.Title
      data-slot="credenza-title"
      className={cn("text-lg font-semibold leading-none", className)}
      {...props}
    />
  )
}

function CredenzaDescription({
  className,
  ...props
}: React.ComponentProps<typeof DialogPrimitive.Description>) {
  return (
    <DialogPrimitive.Description
      data-slot="credenza-description"
      className={cn("text-sm text-muted-foreground", className)}
      {...props}
    />
  )
}

export {
  Credenza,
  CredenzaClose,
  CredenzaContent,
  CredenzaDescription,
  CredenzaFooter,
  CredenzaHeader,
  CredenzaTitle,
  CredenzaTrigger,
}
