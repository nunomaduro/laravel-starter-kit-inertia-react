<?php

declare(strict_types=1);

namespace App\Filament\Resources\VisibilityDemos\Pages;

use App\Filament\Resources\VisibilityDemos\VisibilityDemoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListVisibilityDemos extends ListRecords
{
    #[Override]
    protected static string $resource = VisibilityDemoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
