<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\AppSettings;
use App\Settings\SetupWizardSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageApp extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'App';

    protected static ?int $navigationSort = 10;

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

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('reRunWizard')
                ->label('Re-run Setup Wizard')
                ->icon(Heroicon::OutlinedRocketLaunch)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Re-run Setup Wizard?')
                ->modalDescription(
                    'This will mark setup as incomplete and take you to the wizard. '.
                    'All current settings are preserved — the wizard simply lets you review and update them.'
                )
                ->modalSubmitActionLabel('Yes, re-run wizard')
                ->action(function (): void {
                    $wizard = resolve(SetupWizardSettings::class);
                    $wizard->setup_completed = false;
                    $wizard->completed_steps = [];
                    $wizard->save();

                    Notification::make()
                        ->title('Setup wizard reset')
                        ->body('Redirecting you to the setup wizard.')
                        ->warning()
                        ->send();

                    $this->redirect(route('filament.system.pages.setup-wizard'));
                }),
        ];
    }
}
