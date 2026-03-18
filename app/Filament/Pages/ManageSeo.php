<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageSeo extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'SEO';

    protected static ?int $navigationSort = 40;

    protected static string $settings = SeoSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('meta_title')
                    ->label('Meta title')
                    ->required(),
                Textarea::make('meta_description')
                    ->label('Meta description')
                    ->required(),
                TextInput::make('og_image')
                    ->label('Open Graph image URL')
                    ->url(),
            ]);
    }
}
