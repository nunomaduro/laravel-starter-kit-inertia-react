<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class BookingPriceBreakdown
{
    /**
     * @param  array<string, int>  $nightlyBreakdown  date => price in cents
     */
    public function __construct(
        public int $totalPrice,
        public int $commissionAmount,
        public int $hostPayout,
        public array $nightlyBreakdown,
    ) {}
}
