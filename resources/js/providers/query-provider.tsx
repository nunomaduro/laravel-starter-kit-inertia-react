'use client';

import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import type { ReactNode } from 'react';
import { useState } from 'react';

export function QueryProvider({
    children,
}: {
    children: ReactNode;
}): ReactNode {
    const [queryClient] = useState(
        () =>
            new QueryClient({
                defaultOptions: {
                    queries: {
                        staleTime: 60 * 1000,
                    },
                },
            }),
    );

    return (
        <QueryClientProvider client={queryClient}>
            {children}
            {import.meta.env.DEV && (
                <ReactQueryDevtools
                    buttonPosition="bottom-left"
                    initialIsOpen={false}
                />
            )}
        </QueryClientProvider>
    );
}
