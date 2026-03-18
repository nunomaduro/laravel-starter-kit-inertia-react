<?php

declare(strict_types=1);

namespace App\Filament\Resources\TermsVersions\Pages;

use App\Filament\Resources\TermsVersions\TermsVersionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditTermsVersion extends EditRecord
{
    protected static string $resource = TermsVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
