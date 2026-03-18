<?php

declare(strict_types=1);

namespace Modules\Help\Filament\Resources\HelpArticles\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Help\Filament\Resources\HelpArticles\HelpArticleResource;

final class ViewHelpArticle extends ViewRecord
{
    protected static string $resource = HelpArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
