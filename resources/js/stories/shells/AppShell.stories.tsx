import type { Meta, StoryObj } from '@storybook/react';
import React from 'react';

// AppShell uses Inertia usePage + router — both mocked in preview.tsx.
// We render a simplified approximation with the same layout structure.
function ShellLayout({ children }: { children?: React.ReactNode }) {
    return (
        <div className="flex h-screen overflow-hidden bg-background text-foreground">
            {/* Sidebar placeholder */}
            <aside className="hidden w-64 flex-shrink-0 flex-col gap-2 border-r border-border bg-sidebar p-4 md:flex">
                <div className="mb-4 h-8 w-28 rounded bg-muted/60" />
                {[1, 2, 3, 4, 5].map((i) => (
                    <div
                        key={i}
                        className="flex items-center gap-2 rounded-md px-3 py-2 hover:bg-sidebar-accent"
                    >
                        <div className="size-4 rounded bg-muted/60" />
                        <div className="h-3 w-24 rounded bg-muted/60" />
                    </div>
                ))}
                <div className="flex-1" />
                <div className="flex items-center gap-2 rounded-md px-3 py-2">
                    <div className="size-8 rounded-full bg-primary/20" />
                    <div className="space-y-1">
                        <div className="h-2.5 w-20 rounded bg-muted/60" />
                        <div className="h-2 w-28 rounded bg-muted/40" />
                    </div>
                </div>
            </aside>

            {/* Main area */}
            <div className="flex flex-1 flex-col overflow-hidden">
                {/* Header placeholder */}
                <header className="flex h-14 items-center gap-3 border-b border-border bg-background px-4">
                    <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <span>App</span>
                        <span>/</span>
                        <span className="font-medium text-foreground">
                            Page
                        </span>
                    </div>
                    <div className="flex-1" />
                    <div className="size-8 rounded-full bg-primary/20" />
                </header>

                {/* Content */}
                <main className="flex-1 overflow-auto p-6">
                    {children ?? (
                        <div className="flex h-full items-center justify-center rounded-xl border border-dashed border-border p-8 text-sm text-muted-foreground">
                            Page content goes here
                        </div>
                    )}
                </main>
            </div>
        </div>
    );
}

const meta: Meta = {
    title: 'Shells/AppShell',
    component: ShellLayout,
    tags: ['autodocs'],
    parameters: { layout: 'fullscreen' },
};

export default meta;

export const Default: StoryObj = {
    render: () => <ShellLayout />,
};

export const WithContent: StoryObj = {
    render: () => (
        <ShellLayout>
            <div className="space-y-6">
                <h1 className="text-2xl font-bold">Dashboard</h1>
                <div className="grid grid-cols-3 gap-4">
                    {['Users', 'Revenue', 'Orders'].map((label) => (
                        <div
                            key={label}
                            className="rounded-xl border border-border bg-card p-6"
                        >
                            <p className="text-sm text-muted-foreground">
                                {label}
                            </p>
                            <p className="mt-1 text-3xl font-bold">—</p>
                        </div>
                    ))}
                </div>
            </div>
        </ShellLayout>
    ),
};
