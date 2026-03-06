<?php

declare(strict_types=1);

namespace App\Filament\Resources\CreditPacks\Pages;

use App\Filament\Resources\CreditPacks\CreditPackResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditCreditPack extends EditRecord
{
    #[Override]
    protected static string $resource = CreditPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
