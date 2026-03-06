<?php

declare(strict_types=1);

namespace App\Filament\Resources\CreditPacks\Pages;

use App\Filament\Resources\CreditPacks\CreditPackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListCreditPacks extends ListRecords
{
    #[Override]
    protected static string $resource = CreditPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
