<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\FeatureFlagSettings;
use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageFeatureFlags extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'Feature Flags';

    protected static string $settings = FeatureFlagSettings::class;

    public static function getNavigationLabel(): string
    {
        return 'Feature Flags';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TagsInput::make('globally_disabled_modules')
                    ->label('Globally disabled modules'),
            ]);
    }
}
