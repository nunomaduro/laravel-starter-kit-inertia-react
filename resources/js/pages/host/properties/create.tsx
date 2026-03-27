import { Head, router } from '@inertiajs/react';
import { PropertyForm } from '@/components/booking/property-form';
import HostLayout from '@/layouts/host-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Properties', href: '/host/properties' },
    { title: 'Create', href: '/host/properties/create' },
];

type Props = {
    errors?: Record<string, string>;
};

export default function HostPropertiesCreate({ errors = {} }: Props) {
    const handleSubmit = (data: FormData) => {
        router.post('/host/properties', data);
    };

    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Property" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Create Property</h1>
                <PropertyForm onSubmit={handleSubmit} processing={false} errors={errors} />
            </div>
        </HostLayout>
    );
}
