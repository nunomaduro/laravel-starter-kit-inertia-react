<?php

declare(strict_types=1);

namespace Modules\Help\Filament\Resources\HelpArticles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Help\Filament\Resources\HelpArticles\HelpArticleResource;

final class ListHelpArticles extends ListRecords
{
    protected static string $resource = HelpArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
