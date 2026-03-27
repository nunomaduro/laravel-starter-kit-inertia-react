import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { StatusBadge } from '@/components/booking/status-badge';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import HostLayout from '@/layouts/host-layout';
import type { BreadcrumbItem, PaginatedData, PropertySummary } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Properties', href: '/host/properties' }];

type Props = {
    properties: PaginatedData<PropertySummary>;
};

export default function HostPropertiesIndex({ properties }: Props) {
    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title="My Properties" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">My Properties</h1>
                    <Link href="/host/properties/create">
                        <Button>
                            <Plus className="mr-1 size-4" />
                            Add Property
                        </Button>
                    </Link>
                </div>

                {properties.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16">
                        <p className="text-muted-foreground">You haven't added any properties yet.</p>
                        <Link href="/host/properties/create">
                            <Button variant="outline" className="mt-4">
                                Add Your First Property
                            </Button>
                        </Link>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {properties.data.map((property) => (
                            <Card key={property.id} className="gap-0 overflow-hidden py-0">
                                <div className="relative aspect-[4/3] overflow-hidden">
                                    {property.cover_image ? (
                                        <img
                                            src={property.cover_image}
                                            alt={property.name}
                                            className="size-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex size-full items-center justify-center bg-neutral-100 dark:bg-neutral-800">
                                            <span className="text-sm text-muted-foreground">No image</span>
                                        </div>
                                    )}
                                </div>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between gap-2">
                                        <h3 className="truncate font-semibold">{property.name}</h3>
                                        <StatusBadge status={property.status} />
                                    </div>
                                    <div className="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                        <Badge variant="outline" className="capitalize">
                                            {property.type}
                                        </Badge>
                                        <span>
                                            {property.city}, {property.country}
                                        </span>
                                    </div>
                                    <div className="mt-4 flex gap-2">
                                        <Link href={`/host/properties/${property.id}/edit`} className="flex-1">
                                            <Button variant="outline" size="sm" className="w-full">
                                                Edit
                                            </Button>
                                        </Link>
                                        <Link href={`/properties/${property.slug}`} className="flex-1">
                                            <Button variant="ghost" size="sm" className="w-full">
                                                View
                                            </Button>
                                        </Link>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}

                {(properties.prev_page_url || properties.next_page_url) && (
                    <div className="flex items-center justify-between">
                        {properties.prev_page_url ? (
                            <Link href={properties.prev_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Previous
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                        <span className="text-sm text-muted-foreground">
                            Page {properties.current_page} of {properties.last_page}
                        </span>
                        {properties.next_page_url ? (
                            <Link href={properties.next_page_url} preserveState>
                                <Button variant="outline" size="sm">
                                    Next
                                </Button>
                            </Link>
                        ) : (
                            <div />
                        )}
                    </div>
                )}
            </div>
        </HostLayout>
    );
}
