import { AppShell } from '@/components/app-shell';
import PublicFooter from '@/components/public-footer';
import PublicHeader from '@/components/public-header';
import type { ReactNode } from 'react';

export default function PublicHeaderLayout({ children }: { children: ReactNode }) {
    return (
        <AppShell variant="header">
            <PublicHeader />
            <main className="flex-1">{children}</main>
            <PublicFooter />
        </AppShell>
    );
}
