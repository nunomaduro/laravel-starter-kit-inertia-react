import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type DateRangePickerProps = {
    checkIn?: string;
    checkOut?: string;
    onCheckInChange: (date: string) => void;
    onCheckOutChange: (date: string) => void;
    className?: string;
};

export function DateRangePicker({ checkIn, checkOut, onCheckInChange, onCheckOutChange, className }: DateRangePickerProps) {
    return (
        <div className={cn('grid grid-cols-2 gap-3', className)}>
            <div>
                <Label htmlFor="dr-checkin" className="mb-1 block text-xs font-medium text-muted-foreground">
                    Check in
                </Label>
                <Input
                    id="dr-checkin"
                    type="date"
                    value={checkIn ?? ''}
                    onChange={(e) => onCheckInChange(e.target.value)}
                />
            </div>
            <div>
                <Label htmlFor="dr-checkout" className="mb-1 block text-xs font-medium text-muted-foreground">
                    Check out
                </Label>
                <Input
                    id="dr-checkout"
                    type="date"
                    value={checkOut ?? ''}
                    min={checkIn || undefined}
                    onChange={(e) => onCheckOutChange(e.target.value)}
                />
            </div>
        </div>
    );
}
