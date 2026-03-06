import * as React from 'react';

import { cn } from '@/lib/utils';

export interface KbdProps extends React.ComponentProps<'kbd'> {}

function Kbd({ className, ...props }: KbdProps) {
    return (
        <kbd
            className={cn(
                'inline-flex items-center rounded border border-border bg-muted px-1.5 py-0.5 font-mono text-xs font-medium text-muted-foreground',
                className,
            )}
            {...props}
        />
    );
}

export { Kbd };
