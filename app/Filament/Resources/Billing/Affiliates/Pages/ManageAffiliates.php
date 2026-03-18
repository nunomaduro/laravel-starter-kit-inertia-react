<?php

declare(strict_types=1);

namespace App\Filament\Resources\Billing\Affiliates\Pages;

use App\Filament\Resources\Billing\Affiliates\AffiliateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManageAffiliates extends ManageRecords
{
    protected static string $resource = AffiliateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
