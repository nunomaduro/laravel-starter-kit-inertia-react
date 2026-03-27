import AdminLayoutTemplate from '@/layouts/admin/admin-sidebar-layout';
import type { AppLayoutProps } from '@/types';

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <AdminLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
        {children}
    </AdminLayoutTemplate>
);
