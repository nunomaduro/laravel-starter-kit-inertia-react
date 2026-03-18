<?php

declare(strict_types=1);

namespace App\Filament\Resources\TermsVersions\Pages;

use App\Filament\Resources\TermsVersions\TermsVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListTermsVersions extends ListRecords
{
    protected static string $resource = TermsVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New version'),
        ];
    }
}
