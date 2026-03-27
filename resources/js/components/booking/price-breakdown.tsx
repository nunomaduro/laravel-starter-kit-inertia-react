import { Separator } from '@/components/ui/separator';

type PriceBreakdownProps = {
    priceBreakdown: Record<string, number>;
    totalPrice: number;
    commissionAmount?: number;
    hostPayout?: number;
};

export function PriceBreakdown({ priceBreakdown, totalPrice, commissionAmount, hostPayout }: PriceBreakdownProps) {
    const entries = Object.entries(priceBreakdown);

    return (
        <div className="space-y-2 text-sm">
            {entries.map(([date, price]) => (
                <div key={date} className="flex justify-between">
                    <span className="text-muted-foreground">{date}</span>
                    <span>{price.toLocaleString()} LYD</span>
                </div>
            ))}

            <Separator />

            {commissionAmount !== undefined && hostPayout !== undefined ? (
                <>
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Subtotal</span>
                        <span>{totalPrice.toLocaleString()} LYD</span>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Commission</span>
                        <span className="text-destructive">-{commissionAmount.toLocaleString()} LYD</span>
                    </div>
                    <Separator />
                    <div className="flex justify-between font-semibold">
                        <span>Host Payout</span>
                        <span>{hostPayout.toLocaleString()} LYD</span>
                    </div>
                </>
            ) : (
                <div className="flex justify-between font-semibold">
                    <span>Total</span>
                    <span>{totalPrice.toLocaleString()} LYD</span>
                </div>
            )}
        </div>
    );
}
