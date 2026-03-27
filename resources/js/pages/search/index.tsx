import { Head, Link, router } from '@inertiajs/react';
import { SlidersHorizontal } from 'lucide-react';
import { useState } from 'react';
import { PropertyCard } from '@/components/booking/property-card';
import { SearchBar } from '@/components/booking/search-bar';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import PublicLayout from '@/layouts/public-layout';
import type { PaginatedData, PropertySummary, PropertyType, SearchFilters } from '@/types';

type SearchPageProps = {
    properties: PaginatedData<PropertySummary>;
    filters: SearchFilters;
    amenities: { key: string; name: string }[];
};

export default function SearchIndex({ properties, filters = {}, amenities = [] }: SearchPageProps) {
    const [filterOpen, setFilterOpen] = useState(false);
    const [propertyType, setPropertyType] = useState<PropertyType | ''>(filters.type ?? '');
    const [minPrice, setMinPrice] = useState(filters.min_price?.toString() ?? '');
    const [maxPrice, setMaxPrice] = useState(filters.max_price?.toString() ?? '');
    const [selectedAmenities, setSelectedAmenities] = useState<string[]>(filters.amenities ?? []);

    const applyFilters = () => {
        const params: Record<string, string | number | string[]> = {};
        if (filters.location) {
            params.location = filters.location;
        }
        if (filters.check_in) {
            params.check_in = filters.check_in;
        }
        if (filters.check_out) {
            params.check_out = filters.check_out;
        }
        if (filters.guests) {
            params.guests = filters.guests;
        }
        if (propertyType) {
            params.type = propertyType;
        }
        if (minPrice) {
            params.min_price = Number(minPrice);
        }
        if (maxPrice) {
            params.max_price = Number(maxPrice);
        }
        if (selectedAmenities.length > 0) {
            params.amenities = selectedAmenities;
        }
        router.get('/search', params);
    };

    const toggleAmenity = (amenity: string) => {
        setSelectedAmenities((prev) =>
            prev.includes(amenity) ? prev.filter((a) => a !== amenity) : [...prev, amenity],
        );
    };

    const filterSidebar = (
        <div className="space-y-6">
            <SearchBar variant="compact" defaultValues={filters} />

            <Separator />

            <div>
                <Label className="mb-2 block text-sm font-semibold">Property Type</Label>
                <select
                    value={propertyType}
                    onChange={(e) => setPropertyType(e.target.value as PropertyType | '')}
                    className="border-input focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                >
                    <option value="">All types</option>
                    <option value="resort">Resort</option>
                    <option value="hotel">Hotel</option>
                    <option value="villa">Villa</option>
                </select>
            </div>

            <div>
                <Label className="mb-2 block text-sm font-semibold">Price Range (LYD)</Label>
                <div className="grid grid-cols-2 gap-2">
                    <Input
                        type="number"
                        placeholder="Min"
                        value={minPrice}
                        onChange={(e) => setMinPrice(e.target.value)}
                    />
                    <Input
                        type="number"
                        placeholder="Max"
                        value={maxPrice}
                        onChange={(e) => setMaxPrice(e.target.value)}
                    />
                </div>
            </div>

            {amenities.length > 0 && (
                <div>
                    <Label className="mb-2 block text-sm font-semibold">Amenities</Label>
                    <div className="space-y-2">
                        {amenities.map((amenity) => (
                            <label key={amenity.key} className="flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={selectedAmenities.includes(amenity.key)}
                                    onCheckedChange={() => toggleAmenity(amenity.key)}
                                />
                                {amenity.name}
                            </label>
                        ))}
                    </div>
                </div>
            )}

            <Button onClick={applyFilters} className="w-full">
                Apply Filters
            </Button>
        </div>
    );

    return (
        <PublicLayout>
            <Head title="Search Properties" />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Search Results</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {properties.total} {properties.total === 1 ? 'property' : 'properties'} found
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        className="lg:hidden"
                        onClick={() => setFilterOpen(true)}
                    >
                        <SlidersHorizontal className="size-4" />
                        Filters
                    </Button>
                </div>

                <div className="mt-8 flex gap-8">
                    <aside className="hidden w-72 shrink-0 lg:block">{filterSidebar}</aside>

                    <Sheet open={filterOpen} onOpenChange={setFilterOpen}>
                        <SheetContent side="left">
                            <SheetHeader>
                                <SheetTitle>Filters</SheetTitle>
                            </SheetHeader>
                            <div className="px-4 pt-4">{filterSidebar}</div>
                        </SheetContent>
                    </Sheet>

                    <div className="flex-1">
                        {properties.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-24 text-center">
                                <p className="text-lg font-medium">No properties found</p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Try adjusting your search or filters.
                                </p>
                            </div>
                        ) : (
                            <>
                                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    {properties.data.map((property) => (
                                        <PropertyCard key={property.id} property={property} />
                                    ))}
                                </div>

                                {properties.last_page > 1 && (
                                    <nav className="mt-8 flex items-center justify-center gap-2">
                                        {properties.prev_page_url && (
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={properties.prev_page_url}>Previous</Link>
                                            </Button>
                                        )}
                                        <span className="px-3 text-sm text-muted-foreground">
                                            Page {properties.current_page} of {properties.last_page}
                                        </span>
                                        {properties.next_page_url && (
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={properties.next_page_url}>Next</Link>
                                            </Button>
                                        )}
                                    </nav>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
