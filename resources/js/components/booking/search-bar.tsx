import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { SearchFilters } from '@/types';

type SearchBarProps = {
    defaultValues?: Partial<SearchFilters>;
    variant?: 'hero' | 'compact';
    className?: string;
};

export function SearchBar({ defaultValues = {}, variant = 'hero', className }: SearchBarProps) {
    const [location, setLocation] = useState(defaultValues.location ?? '');
    const [checkIn, setCheckIn] = useState(defaultValues.check_in ?? '');
    const [checkOut, setCheckOut] = useState(defaultValues.check_out ?? '');
    const [guests, setGuests] = useState(defaultValues.guests ?? 1);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const params: Record<string, string | number> = {};
        if (location) {
            params.location = location;
        }
        if (checkIn) {
            params.check_in = checkIn;
        }
        if (checkOut) {
            params.check_out = checkOut;
        }
        if (guests > 1) {
            params.guests = guests;
        }

        router.get('/search', params);
    };

    if (variant === 'hero') {
        return (
            <form
                onSubmit={handleSubmit}
                className={cn(
                    'mx-auto w-full max-w-4xl rounded-2xl bg-white p-6 shadow-xl dark:bg-neutral-900',
                    className,
                )}
            >
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div className="lg:col-span-2">
                        <Label htmlFor="hero-location" className="mb-1.5 block text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Location
                        </Label>
                        <Input
                            id="hero-location"
                            placeholder="Where are you going?"
                            value={location}
                            onChange={(e) => setLocation(e.target.value)}
                            className="h-11"
                        />
                    </div>
                    <div>
                        <Label htmlFor="hero-checkin" className="mb-1.5 block text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Check in
                        </Label>
                        <Input
                            id="hero-checkin"
                            type="date"
                            value={checkIn}
                            onChange={(e) => setCheckIn(e.target.value)}
                            className="h-11"
                        />
                    </div>
                    <div>
                        <Label htmlFor="hero-checkout" className="mb-1.5 block text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Check out
                        </Label>
                        <Input
                            id="hero-checkout"
                            type="date"
                            value={checkOut}
                            min={checkIn || undefined}
                            onChange={(e) => setCheckOut(e.target.value)}
                            className="h-11"
                        />
                    </div>
                    <div>
                        <Label htmlFor="hero-guests" className="mb-1.5 block text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Guests
                        </Label>
                        <div className="flex gap-2">
                            <Input
                                id="hero-guests"
                                type="number"
                                min={1}
                                value={guests}
                                onChange={(e) => setGuests(Number(e.target.value))}
                                className="h-11"
                            />
                            <Button type="submit" size="lg" className="h-11 shrink-0">
                                <Search className="size-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </form>
        );
    }

    return (
        <form onSubmit={handleSubmit} className={cn('flex flex-col gap-3', className)}>
            <div>
                <Label htmlFor="compact-location" className="mb-1 block text-xs font-medium text-muted-foreground">
                    Location
                </Label>
                <Input
                    id="compact-location"
                    placeholder="City or country"
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                />
            </div>
            <div>
                <Label htmlFor="compact-checkin" className="mb-1 block text-xs font-medium text-muted-foreground">
                    Check in
                </Label>
                <Input
                    id="compact-checkin"
                    type="date"
                    value={checkIn}
                    onChange={(e) => setCheckIn(e.target.value)}
                />
            </div>
            <div>
                <Label htmlFor="compact-checkout" className="mb-1 block text-xs font-medium text-muted-foreground">
                    Check out
                </Label>
                <Input
                    id="compact-checkout"
                    type="date"
                    value={checkOut}
                    min={checkIn || undefined}
                    onChange={(e) => setCheckOut(e.target.value)}
                />
            </div>
            <div>
                <Label htmlFor="compact-guests" className="mb-1 block text-xs font-medium text-muted-foreground">
                    Guests
                </Label>
                <Input
                    id="compact-guests"
                    type="number"
                    min={1}
                    value={guests}
                    onChange={(e) => setGuests(Number(e.target.value))}
                />
            </div>
            <Button type="submit" className="w-full">
                <Search className="size-4" />
                Search
            </Button>
        </form>
    );
}
