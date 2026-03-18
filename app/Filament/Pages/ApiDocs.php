<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Features\ScrambleApiDocsFeature;
use App\Support\FeatureHelper;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

final class ApiDocs extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracketSquare;

    protected static ?string $navigationLabel = 'API docs';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'API documentation';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return filament()->getCurrentPanel()?->getId() === 'system' && FeatureHelper::isActiveForClass(ScrambleApiDocsFeature::class, $user);
    }

    public function content(Schema $schema): Schema
    {
        $url = Str::finish(config('app.url'), '/').'docs/api';

        return $schema
            ->components([
                Html::make(
                    '<p class="text-sm text-gray-600 dark:text-gray-400">OpenAPI documentation for the REST API.</p>'
                    .'<p class="mt-2"><a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline dark:text-primary-400">Open API docs in new tab</a></p>'
                ),
            ]);
    }
}
