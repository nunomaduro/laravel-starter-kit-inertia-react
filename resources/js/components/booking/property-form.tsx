import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import type { Property, PropertyType } from '@/types';
import { useState } from 'react';

type PropertyFormProps = {
    property?: Property;
    onSubmit: (data: FormData) => void;
    processing: boolean;
    errors: Record<string, string>;
};

const PROPERTY_TYPES: { value: PropertyType; label: string }[] = [
    { value: 'resort', label: 'Resort' },
    { value: 'hotel', label: 'Hotel' },
    { value: 'villa', label: 'Villa' },
];

const AVAILABLE_AMENITIES = [
    'WiFi',
    'Pool',
    'Parking',
    'Gym',
    'Spa',
    'TV',
    'Air Conditioning',
    'Kitchen',
    'Hot Tub',
    'Shower',
    'Balcony',
    'Garden',
    'Beach Access',
    'Room Service',
    'Restaurant',
    'Bar',
    'Laundry',
    'Pet Friendly',
];

export function PropertyForm({ property, onSubmit, processing, errors }: PropertyFormProps) {
    const [name, setName] = useState(property?.name ?? '');
    const [description, setDescription] = useState(property?.description ?? '');
    const [type, setType] = useState<PropertyType>(property?.type ?? 'hotel');
    const [address, setAddress] = useState(property?.address ?? '');
    const [city, setCity] = useState(property?.city ?? '');
    const [country, setCountry] = useState(property?.country ?? '');
    const [amenities, setAmenities] = useState<string[]>(property?.amenities ?? []);
    const [cancellationPolicy, setCancellationPolicy] = useState(property?.cancellation_policy ?? '');

    const toggleAmenity = (amenity: string) => {
        setAmenities((prev) => (prev.includes(amenity) ? prev.filter((a) => a !== amenity) : [...prev, amenity]));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('type', type);
        formData.append('address', address);
        formData.append('city', city);
        formData.append('country', country);
        amenities.forEach((a) => formData.append('amenities[]', a));
        formData.append('cancellation_policy', cancellationPolicy);
        onSubmit(formData);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-8">
            <section>
                <h3 className="text-lg font-semibold">Basic Information</h3>
                <Separator className="my-4" />
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                        <Label htmlFor="pf-name">Property Name</Label>
                        <Input
                            id="pf-name"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            className={cn('mt-1', errors.name && 'border-destructive')}
                        />
                        {errors.name && <p className="mt-1 text-xs text-destructive">{errors.name}</p>}
                    </div>
                    <div className="sm:col-span-2">
                        <Label htmlFor="pf-description">Description</Label>
                        <textarea
                            id="pf-description"
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                            rows={4}
                            className={cn(
                                'border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]',
                                errors.description && 'border-destructive',
                            )}
                        />
                        {errors.description && <p className="mt-1 text-xs text-destructive">{errors.description}</p>}
                    </div>
                    <div>
                        <Label htmlFor="pf-type">Property Type</Label>
                        <select
                            id="pf-type"
                            value={type}
                            onChange={(e) => setType(e.target.value as PropertyType)}
                            className="border-input focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        >
                            {PROPERTY_TYPES.map((pt) => (
                                <option key={pt.value} value={pt.value}>
                                    {pt.label}
                                </option>
                            ))}
                        </select>
                        {errors.type && <p className="mt-1 text-xs text-destructive">{errors.type}</p>}
                    </div>
                </div>
            </section>

            <section>
                <h3 className="text-lg font-semibold">Location</h3>
                <Separator className="my-4" />
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                        <Label htmlFor="pf-address">Address</Label>
                        <Input
                            id="pf-address"
                            value={address}
                            onChange={(e) => setAddress(e.target.value)}
                            className={cn('mt-1', errors.address && 'border-destructive')}
                        />
                        {errors.address && <p className="mt-1 text-xs text-destructive">{errors.address}</p>}
                    </div>
                    <div>
                        <Label htmlFor="pf-city">City</Label>
                        <Input
                            id="pf-city"
                            value={city}
                            onChange={(e) => setCity(e.target.value)}
                            className={cn('mt-1', errors.city && 'border-destructive')}
                        />
                        {errors.city && <p className="mt-1 text-xs text-destructive">{errors.city}</p>}
                    </div>
                    <div>
                        <Label htmlFor="pf-country">Country</Label>
                        <Input
                            id="pf-country"
                            value={country}
                            onChange={(e) => setCountry(e.target.value)}
                            className={cn('mt-1', errors.country && 'border-destructive')}
                        />
                        {errors.country && <p className="mt-1 text-xs text-destructive">{errors.country}</p>}
                    </div>
                </div>
            </section>

            <section>
                <h3 className="text-lg font-semibold">Amenities</h3>
                <Separator className="my-4" />
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    {AVAILABLE_AMENITIES.map((amenity) => (
                        <label key={amenity} className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={amenities.includes(amenity)}
                                onCheckedChange={() => toggleAmenity(amenity)}
                            />
                            {amenity}
                        </label>
                    ))}
                </div>
                {errors.amenities && <p className="mt-2 text-xs text-destructive">{errors.amenities}</p>}
            </section>

            <section>
                <h3 className="text-lg font-semibold">Cancellation Policy</h3>
                <Separator className="my-4" />
                <div>
                    <Label htmlFor="pf-cancellation">Policy Description</Label>
                    <textarea
                        id="pf-cancellation"
                        value={cancellationPolicy}
                        onChange={(e) => setCancellationPolicy(e.target.value)}
                        rows={3}
                        className="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 mt-1 flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                    />
                </div>
            </section>

            <div className="flex justify-end">
                <Button type="submit" disabled={processing}>
                    {processing && <Spinner />}
                    {property ? 'Update Property' : 'Create Property'}
                </Button>
            </div>
        </form>
    );
}
