import { Badge } from '@/components/ui/badge';
import type { BookingStatus, PropertyStatus } from '@/types';

type StatusBadgeProps = {
    status: BookingStatus | PropertyStatus;
};

const statusConfig: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; label: string }> = {
    pending: { variant: 'secondary', label: 'Pending' },
    approved: { variant: 'default', label: 'Approved' },
    rejected: { variant: 'destructive', label: 'Rejected' },
    declined: { variant: 'destructive', label: 'Declined' },
    completed: { variant: 'outline', label: 'Completed' },
    cancelled: { variant: 'destructive', label: 'Cancelled' },
};

export function StatusBadge({ status }: StatusBadgeProps) {
    const config = statusConfig[status] ?? { variant: 'secondary' as const, label: status };

    return <Badge variant={config.variant}>{config.label}</Badge>;
}
