<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Providers\SettingsOverlayServiceProvider;
use App\Settings\AppSettings;
use App\Settings\SetupWizardSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class ManageApp extends SettingsPage
{
    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    #[Override]
    protected static ?string $navigationLabel = 'App';

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static string $settings = AppSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('site_name')
                    ->label('Site name')
                    ->required(),
                TextInput::make('url')
                    ->label('Application URL')
                    ->url()
                    ->required(),
                Toggle::make('maintenance_mode')
                    ->label('Maintenance mode'),
                TextInput::make('timezone')
                    ->label('Timezone')
                    ->required(),
                Select::make('locale')
                    ->label('Locale')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'pt' => 'Portuguese',
                        'it' => 'Italian',
                        'nl' => 'Dutch',
                        'ja' => 'Japanese',
                        'ko' => 'Korean',
                        'zh' => 'Chinese',
                        'ar' => 'Arabic',
                    ])
                    ->required()
                    ->searchable(),
                Select::make('fallback_locale')
                    ->label('Fallback locale')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'pt' => 'Portuguese',
                    ])
                    ->required()
                    ->searchable(),
            ]);
    }

    protected function afterSave(): void
    {
        $wizard = resolve(SetupWizardSettings::class);
        if (! $wizard->setup_completed) {
            $wizard->setup_completed = true;
            $wizard->completed_steps = ['app'];
            $wizard->save();
            SettingsOverlayServiceProvider::applyOverlay();
        }
    }
}
