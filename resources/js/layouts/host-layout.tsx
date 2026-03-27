import HostLayoutTemplate from '@/layouts/host/host-sidebar-layout';
import type { AppLayoutProps } from '@/types';

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <HostLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
        {children}
    </HostLayoutTemplate>
);
