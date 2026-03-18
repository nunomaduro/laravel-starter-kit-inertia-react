<?php

declare(strict_types=1);

namespace App\Filament\Resources\CreditPacks\Pages;

use App\Filament\Resources\CreditPacks\CreditPackResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCreditPack extends CreateRecord
{
    protected static string $resource = CreditPackResource::class;
}
