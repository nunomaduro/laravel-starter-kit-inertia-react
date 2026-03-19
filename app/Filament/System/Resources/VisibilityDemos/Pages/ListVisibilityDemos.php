<?php

declare(strict_types=1);

namespace App\Filament\System\Resources\VisibilityDemos\Pages;

use App\Filament\System\Resources\VisibilityDemos\VisibilityDemoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListVisibilityDemos extends ListRecords
{
    protected static string $resource = VisibilityDemoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
