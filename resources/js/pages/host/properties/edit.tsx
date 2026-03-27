import { Head, router } from '@inertiajs/react';
import { PropertyForm } from '@/components/booking/property-form';
import HostLayout from '@/layouts/host-layout';
import type { BreadcrumbItem, Property } from '@/types';

type Props = {
    property: Property;
    errors?: Record<string, string>;
};

export default function HostPropertiesEdit({ property, errors = {} }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Properties', href: '/host/properties' },
        { title: property.name, href: `/host/properties/${property.id}/edit` },
    ];

    const handleSubmit = (data: FormData) => {
        data.append('_method', 'PUT');
        router.post(`/host/properties/${property.id}`, data);
    };

    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit - ${property.name}`} />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Edit Property</h1>
                <PropertyForm property={property} onSubmit={handleSubmit} processing={false} errors={errors} />
            </div>
        </HostLayout>
    );
}
