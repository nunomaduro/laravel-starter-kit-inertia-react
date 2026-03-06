import * as React from 'react';
import TextareaAutosize from 'react-textarea-autosize';

import { cn } from '@/lib/utils';

type TextareaVariant = 'outlined' | 'filled' | 'soft';

const textareaVariantClasses: Record<TextareaVariant, string> = {
  outlined: 'border border-input bg-transparent dark:bg-input/30',
  filled: 'border border-transparent bg-muted dark:bg-muted/50',
  soft: 'border border-transparent bg-primary/5 dark:bg-primary/10',
};

function Textarea({
  className,
  variant = 'outlined',
  autoSize = false,
  minRows,
  maxRows,
  ...props
}: React.ComponentProps<'textarea'> & {
  variant?: TextareaVariant;
  autoSize?: boolean;
  minRows?: number;
  maxRows?: number;
}) {
  const sharedClasses = cn(
    'placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground min-h-[80px] w-full min-w-0 rounded-md px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
    textareaVariantClasses[variant],
    className,
  );

  if (autoSize) {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { style: _style, ...restProps } = props
    return (
      // TextareaAutosize has an incompatible `style` type (only {height?:number}),
      // so we omit `style` from the native props and cast the rest.
      <TextareaAutosize
        data-slot="textarea"
        minRows={minRows}
        maxRows={maxRows}
        className={cn(sharedClasses, 'resize-none')}
        {...(restProps as unknown as React.ComponentPropsWithoutRef<typeof TextareaAutosize>)}
      />
    );
  }

  return (
    <textarea
      data-slot="textarea"
      className={sharedClasses}
      {...props}
    />
  );
}

export { Textarea };
