import * as React from 'react';
import { CheckIcon, MinusIcon, ZapIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

export interface PricingFeature {
    label: string;
    included?: boolean;
    note?: string;
}

export interface PricingCardProps {
    name: string;
    description?: string;
    price: number | 'custom';
    currency?: string;
    billingPeriod?: 'month' | 'year' | 'one-time';
    yearlyPrice?: number;
    features: PricingFeature[];
    ctaLabel?: string;
    ctaVariant?: 'default' | 'outline';
    onSelect?: () => void;
    isPopular?: boolean;
    isCurrent?: boolean;
    badge?: string;
    footnote?: string;
    className?: string;
    disabled?: boolean;
    icon?: React.ReactNode;
}

function PricingCard({
    name,
    description,
    price,
    currency = '$',
    billingPeriod = 'month',
    yearlyPrice,
    features,
    ctaLabel = 'Get started',
    ctaVariant = 'default',
    onSelect,
    isPopular = false,
    isCurrent = false,
    badge,
    footnote,
    className,
    disabled = false,
    icon,
}: PricingCardProps) {
    const billingLabel: Record<string, string> = {
        month: '/mo',
        year: '/yr',
        'one-time': ' one-time',
    };

    return (
        <Card
            data-slot="pricing-card"
            className={cn(
                'relative flex flex-col',
                isPopular && 'border-primary shadow-lg ring-1 ring-primary',
                isCurrent && 'border-success',
                className,
            )}
        >
            {isPopular && !badge && (
                <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                    <Badge className="gap-1 px-3 py-0.5 text-xs">
                        <ZapIcon className="size-3" />
                        Most popular
                    </Badge>
                </div>
            )}
            {badge && (
                <div className="absolute -top-3 left-1/2 -translate-x-1/2">
                    <Badge variant="secondary" className="px-3 py-0.5 text-xs">
                        {badge}
                    </Badge>
                </div>
            )}
            {isCurrent && (
                <div className="absolute -top-3 right-4">
                    <Badge variant="outline" className="border-success text-success px-3 py-0.5 text-xs">
                        Current plan
                    </Badge>
                </div>
            )}

            <CardHeader className="pb-4">
                <div className="flex items-center gap-2">
                    {icon && (
                        <div className="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            {icon}
                        </div>
                    )}
                    <div>
                        <h3 className="text-base font-semibold">{name}</h3>
                        {description && (
                            <p className="text-xs text-muted-foreground">{description}</p>
                        )}
                    </div>
                </div>

                <div className="mt-3">
                    {price === 'custom' ? (
                        <div>
                            <span className="text-3xl font-bold">Custom</span>
                        </div>
                    ) : (
                        <div className="flex items-end gap-1">
                            <span className="text-sm font-medium text-muted-foreground">{currency}</span>
                            <span className="text-3xl font-bold leading-none">{price}</span>
                            <span className="mb-0.5 text-sm text-muted-foreground">
                                {billingLabel[billingPeriod]}
                            </span>
                        </div>
                    )}
                    {yearlyPrice && billingPeriod === 'month' && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            or{' '}
                            <span className="font-medium text-foreground">
                                {currency}{yearlyPrice}/yr
                            </span>{' '}
                            billed annually
                        </p>
                    )}
                </div>
            </CardHeader>

            <Separator />

            <CardContent className="flex-1 pt-4">
                <ul className="space-y-2.5">
                    {features.map((feature, i) => (
                        <li key={i} className="flex items-start gap-2.5">
                            {feature.included !== false ? (
                                <CheckIcon className="mt-px size-4 shrink-0 text-success" />
                            ) : (
                                <MinusIcon className="mt-px size-4 shrink-0 text-muted-foreground/40" />
                            )}
                            <span
                                className={cn(
                                    'text-sm leading-tight',
                                    feature.included === false && 'text-muted-foreground',
                                )}
                            >
                                {feature.label}
                                {feature.note && (
                                    <span className="ml-1 text-xs text-muted-foreground">
                                        ({feature.note})
                                    </span>
                                )}
                            </span>
                        </li>
                    ))}
                </ul>
            </CardContent>

            <CardFooter className="flex-col gap-2 pt-0">
                <Button
                    variant={ctaVariant}
                    className="w-full"
                    onClick={onSelect}
                    disabled={disabled || isCurrent}
                >
                    {isCurrent ? 'Current plan' : ctaLabel}
                </Button>
                {footnote && (
                    <p className="text-center text-[10px] text-muted-foreground">{footnote}</p>
                )}
            </CardFooter>
        </Card>
    );
}

export { PricingCard };
