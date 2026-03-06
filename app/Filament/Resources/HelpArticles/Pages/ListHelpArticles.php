<?php

declare(strict_types=1);

namespace App\Filament\Resources\HelpArticles\Pages;

use App\Filament\Resources\HelpArticles\HelpArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListHelpArticles extends ListRecords
{
    #[Override]
    protected static string $resource = HelpArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
