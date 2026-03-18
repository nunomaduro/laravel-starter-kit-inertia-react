<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\LoggingSettings;
use BackedEnum;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageLogging extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Logging';

    protected static ?int $navigationSort = 50;

    protected static string $settings = LoggingSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Log Channel')
                    ->description('Configure where application logs are written. Changes take effect on next request.')
                    ->schema([
                        Select::make('default_channel')
                            ->label('Default channel')
                            ->options([
                                'stack' => 'Stack (default)',
                                'single' => 'Single file',
                                'daily' => 'Daily files',
                                'slack' => 'Slack',
                                'stderr' => 'Stderr',
                                'papertrail' => 'Papertrail',
                                'null' => 'Null (discard all)',
                            ])
                            ->required(),
                        Select::make('log_level')
                            ->label('Log level')
                            ->options([
                                'debug' => 'Debug (verbose)',
                                'info' => 'Info',
                                'notice' => 'Notice',
                                'warning' => 'Warning',
                                'error' => 'Error',
                                'critical' => 'Critical',
                                'alert' => 'Alert',
                                'emergency' => 'Emergency',
                            ])
                            ->required(),
                    ]),
                Section::make('Slack Log Channel')
                    ->description('Send log messages to a Slack webhook. The Slack channel must be configured separately in the Integrations settings for app notifications.')
                    ->schema([
                        TextInput::make('slack_webhook_url')
                            ->label('Slack webhook URL (for logs)')
                            ->url()
                            ->password()
                            ->revealable()
                            ->helperText('Dedicated webhook for log alerts — separate from the Integrations slack_webhook_url.'),
                        Select::make('slack_log_level')
                            ->label('Minimum level to send to Slack')
                            ->options([
                                'debug' => 'Debug',
                                'info' => 'Info',
                                'notice' => 'Notice',
                                'warning' => 'Warning',
                                'error' => 'Error',
                                'critical' => 'Critical',
                                'alert' => 'Alert',
                                'emergency' => 'Emergency',
                            ]),
                    ]),
            ]);
    }
}
