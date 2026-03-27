import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { AvailabilityCalendar } from '@/components/booking/availability-calendar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import HostLayout from '@/layouts/host-layout';
import type { BreadcrumbItem, PropertySummary, RoomType } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Calendar', href: '/host/calendar' }];

type RoomTypeWithAvailability = RoomType & {
    availability: Record<string, { available: number; total: number; blocked: boolean }>;
};

type Props = {
    properties: PropertySummary[];
    selectedProperty?: string;
    roomTypes: RoomTypeWithAvailability[];
};

export default function HostCalendarIndex({ properties = [], selectedProperty, roomTypes = [] }: Props) {
    const [selectedPropertyId, setSelectedPropertyId] = useState(selectedProperty ?? properties[0]?.id ?? '');
    const [selectedRoomIndex, setSelectedRoomIndex] = useState(0);

    const now = new Date();
    const [month, setMonth] = useState(`${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`);

    const selectedRoom = roomTypes[selectedRoomIndex];

    const handlePropertyChange = (propertyId: string) => {
        setSelectedPropertyId(propertyId);
        setSelectedRoomIndex(0);
        router.get('/host/calendar', { property: propertyId }, { preserveState: true });
    };

    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title="Availability Calendar" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Availability Calendar</h1>

                {properties.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16">
                        <p className="text-muted-foreground">No properties found. Add a property first.</p>
                    </div>
                ) : (
                    <>
                        <div className="flex flex-wrap gap-4">
                            <div>
                                <Label>Property</Label>
                                <select
                                    value={selectedPropertyId}
                                    onChange={(e) => handlePropertyChange(e.target.value)}
                                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex h-9 w-full min-w-[200px] rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                >
                                    {properties.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {roomTypes.length > 0 && (
                                <div>
                                    <Label>Room Type</Label>
                                    <select
                                        value={selectedRoomIndex}
                                        onChange={(e) => setSelectedRoomIndex(Number(e.target.value))}
                                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex h-9 w-full min-w-[200px] rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    >
                                        {roomTypes.map((rt, i) => (
                                            <option key={rt.id} value={i}>
                                                {rt.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}
                        </div>

                        {roomTypes.length === 0 ? (
                            <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-12">
                                <p className="text-muted-foreground">No room types for this property.</p>
                            </div>
                        ) : (
                            <Card>
                                <CardHeader>
                                    <CardTitle>{selectedRoom?.name}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {selectedRoom && (
                                        <AvailabilityCalendar
                                            dates={selectedRoom.availability}
                                            month={month}
                                            onMonthChange={setMonth}
                                        />
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        <div className="flex justify-end">
                            <Button variant="outline" disabled>
                                Block Dates (Coming Soon)
                            </Button>
                        </div>
                    </>
                )}
            </div>
        </HostLayout>
    );
}
