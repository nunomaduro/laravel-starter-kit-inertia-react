import { AnnouncementsBanner } from '@/components/announcements-banner';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { CommandPalette } from '@/components/command-dialog';
import { GlobalChatWidget } from '@/components/global-chat/global-chat-widget';
import { ThemeCustomizer } from '@/components/ui/theme-customizer';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    const { setup_complete, auth } = usePage<SharedData>().props;

    if (!setup_complete && !auth?.can_bypass) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-background">
                <div className="max-w-md space-y-3 p-8 text-center">
                    <h1 className="font-mono text-2xl font-semibold tracking-tight">
                        Setup in Progress
                    </h1>
                    <p className="text-muted-foreground">
                        This application is being configured by an
                        administrator. Please check back shortly.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <AppShell variant="sidebar">
            <CommandPalette />
            <ThemeCustomizer />
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <AnnouncementsBanner />
                {children}
            </AppContent>
            <GlobalChatWidget />
        </AppShell>
    );
}
