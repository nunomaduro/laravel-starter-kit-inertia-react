<?php

declare(strict_types=1);

namespace App\Filament\Resources\HelpArticles\Pages;

use App\Filament\Resources\HelpArticles\HelpArticleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewHelpArticle extends ViewRecord
{
    #[Override]
    protected static string $resource = HelpArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
