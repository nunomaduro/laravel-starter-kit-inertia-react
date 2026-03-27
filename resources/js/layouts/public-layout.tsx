import PublicLayoutTemplate from '@/layouts/public/public-header-layout';
import type { ReactNode } from 'react';

type PublicLayoutProps = {
    children: ReactNode;
};

export default ({ children }: PublicLayoutProps) => (
    <PublicLayoutTemplate>{children}</PublicLayoutTemplate>
);
