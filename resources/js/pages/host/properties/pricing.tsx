import { Head, router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import HostLayout from '@/layouts/host-layout';
import type { BreadcrumbItem, PropertySummary, RoomType } from '@/types';

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

type DayPrice = { day_of_week: number; price_per_night: number };
type SeasonalPrice = { id: string; name: string; start_date: string; end_date: string; price_per_night: number };
type SpecialDatePrice = { id: string; date: string; price_per_night: number; label: string | null };

type RoomTypeWithPricing = RoomType & {
    day_prices: DayPrice[];
    seasonal_prices: SeasonalPrice[];
    special_date_prices: SpecialDatePrice[];
};

type Props = {
    property: PropertySummary;
    roomTypes: RoomTypeWithPricing[];
};

export default function HostPropertyPricing({ property, roomTypes }: Props) {
    const [selectedRoomIndex, setSelectedRoomIndex] = useState(0);
    const [processing, setProcessing] = useState(false);

    const selectedRoom = roomTypes[selectedRoomIndex];

    const [dayPrices, setDayPrices] = useState<number[]>(() => {
        const prices = Array(7).fill(selectedRoom?.base_price_per_night ?? 0) as number[];
        selectedRoom?.day_prices.forEach((dp) => {
            prices[dp.day_of_week] = dp.price_per_night;
        });
        return prices;
    });

    const [seasonalPrices, setSeasonalPrices] = useState<SeasonalPrice[]>(selectedRoom?.seasonal_prices ?? []);
    const [specialDates, setSpecialDates] = useState<SpecialDatePrice[]>(selectedRoom?.special_date_prices ?? []);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Properties', href: '/host/properties' },
        { title: property.name, href: `/host/properties/${property.id}/edit` },
        { title: 'Pricing', href: `/host/properties/${property.id}/pricing` },
    ];

    const handleRoomChange = (index: number) => {
        setSelectedRoomIndex(index);
        const room = roomTypes[index];
        const prices = Array(7).fill(room.base_price_per_night) as number[];
        room.day_prices.forEach((dp) => {
            prices[dp.day_of_week] = dp.price_per_night;
        });
        setDayPrices(prices);
        setSeasonalPrices(room.seasonal_prices);
        setSpecialDates(room.special_date_prices);
    };

    const addSeasonalPrice = () => {
        setSeasonalPrices((prev) => [
            ...prev,
            { id: `new-${Date.now()}`, name: '', start_date: '', end_date: '', price_per_night: 0 },
        ]);
    };

    const removeSeasonalPrice = (index: number) => {
        setSeasonalPrices((prev) => prev.filter((_, i) => i !== index));
    };

    const updateSeasonalPrice = (index: number, field: keyof SeasonalPrice, value: string | number) => {
        setSeasonalPrices((prev) => prev.map((sp, i) => (i === index ? { ...sp, [field]: value } : sp)));
    };

    const addSpecialDate = () => {
        setSpecialDates((prev) => [
            ...prev,
            { id: `new-${Date.now()}`, date: '', price_per_night: 0, label: '' },
        ]);
    };

    const removeSpecialDate = (index: number) => {
        setSpecialDates((prev) => prev.filter((_, i) => i !== index));
    };

    const updateSpecialDate = (index: number, field: keyof SpecialDatePrice, value: string | number | null) => {
        setSpecialDates((prev) => prev.map((sd, i) => (i === index ? { ...sd, [field]: value } : sd)));
    };

    const handleSave = () => {
        setProcessing(true);
        router.post(
            `/host/properties/${property.id}/pricing`,
            {
                room_type_id: selectedRoom.id,
                day_prices: dayPrices.map((price, day) => ({ day_of_week: day, price_per_night: price })),
                seasonal_prices: seasonalPrices,
                special_date_prices: specialDates,
            },
            { onFinish: () => setProcessing(false) },
        );
    };

    if (roomTypes.length === 0) {
        return (
            <HostLayout breadcrumbs={breadcrumbs}>
                <Head title={`Pricing - ${property.name}`} />
                <div className="flex flex-col items-center justify-center p-8">
                    <p className="text-muted-foreground">No room types defined for this property.</p>
                </div>
            </HostLayout>
        );
    }

    return (
        <HostLayout breadcrumbs={breadcrumbs}>
            <Head title={`Pricing - ${property.name}`} />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Pricing - {property.name}</h1>

                <div>
                    <Label>Room Type</Label>
                    <select
                        value={selectedRoomIndex}
                        onChange={(e) => handleRoomChange(Number(e.target.value))}
                        className="border-input focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex h-9 w-full max-w-sm rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                    >
                        {roomTypes.map((rt, i) => (
                            <option key={rt.id} value={i}>
                                {rt.name} (Base: {rt.base_price_per_night.toLocaleString()} LYD)
                            </option>
                        ))}
                    </select>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Day-of-Week Pricing</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {DAY_NAMES.map((day, i) => (
                                <div key={day}>
                                    <Label htmlFor={`day-${i}`}>{day}</Label>
                                    <Input
                                        id={`day-${i}`}
                                        type="number"
                                        min={0}
                                        value={dayPrices[i]}
                                        onChange={(e) => {
                                            const newPrices = [...dayPrices];
                                            newPrices[i] = Number(e.target.value);
                                            setDayPrices(newPrices);
                                        }}
                                        className="mt-1"
                                    />
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Seasonal Pricing</CardTitle>
                        <Button variant="outline" size="sm" onClick={addSeasonalPrice}>
                            <Plus className="mr-1 size-4" />
                            Add Season
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {seasonalPrices.length === 0 ? (
                            <p className="text-sm text-muted-foreground">No seasonal pricing configured.</p>
                        ) : (
                            <div className="space-y-4">
                                {seasonalPrices.map((sp, i) => (
                                    <div key={sp.id} className="flex flex-wrap items-end gap-3 rounded-lg border p-3">
                                        <div className="flex-1">
                                            <Label>Name</Label>
                                            <Input
                                                value={sp.name}
                                                onChange={(e) => updateSeasonalPrice(i, 'name', e.target.value)}
                                                placeholder="e.g. Summer"
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label>Start Date</Label>
                                            <Input
                                                type="date"
                                                value={sp.start_date}
                                                onChange={(e) => updateSeasonalPrice(i, 'start_date', e.target.value)}
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label>End Date</Label>
                                            <Input
                                                type="date"
                                                value={sp.end_date}
                                                onChange={(e) => updateSeasonalPrice(i, 'end_date', e.target.value)}
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label>Price/Night</Label>
                                            <Input
                                                type="number"
                                                min={0}
                                                value={sp.price_per_night}
                                                onChange={(e) => updateSeasonalPrice(i, 'price_per_night', Number(e.target.value))}
                                                className="mt-1"
                                            />
                                        </div>
                                        <Button variant="ghost" size="icon" onClick={() => removeSeasonalPrice(i)}>
                                            <Trash2 className="size-4 text-destructive" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Special Date Pricing</CardTitle>
                        <Button variant="outline" size="sm" onClick={addSpecialDate}>
                            <Plus className="mr-1 size-4" />
                            Add Date
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {specialDates.length === 0 ? (
                            <p className="text-sm text-muted-foreground">No special date pricing configured.</p>
                        ) : (
                            <div className="space-y-4">
                                {specialDates.map((sd, i) => (
                                    <div key={sd.id} className="flex flex-wrap items-end gap-3 rounded-lg border p-3">
                                        <div>
                                            <Label>Date</Label>
                                            <Input
                                                type="date"
                                                value={sd.date}
                                                onChange={(e) => updateSpecialDate(i, 'date', e.target.value)}
                                                className="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label>Price/Night</Label>
                                            <Input
                                                type="number"
                                                min={0}
                                                value={sd.price_per_night}
                                                onChange={(e) => updateSpecialDate(i, 'price_per_night', Number(e.target.value))}
                                                className="mt-1"
                                            />
                                        </div>
                                        <div className="flex-1">
                                            <Label>Label (optional)</Label>
                                            <Input
                                                value={sd.label ?? ''}
                                                onChange={(e) => updateSpecialDate(i, 'label', e.target.value || null)}
                                                placeholder="e.g. New Year's Eve"
                                                className="mt-1"
                                            />
                                        </div>
                                        <Button variant="ghost" size="icon" onClick={() => removeSpecialDate(i)}>
                                            <Trash2 className="size-4 text-destructive" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <div className="flex justify-end">
                    <Button onClick={handleSave} disabled={processing}>
                        {processing && <Spinner />}
                        Save Pricing
                    </Button>
                </div>
            </div>
        </HostLayout>
    );
}
