<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\IntegrationsSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageIntegrations extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $navigationLabel = 'Integrations';

    protected static ?int $navigationSort = 80;

    protected static string $settings = IntegrationsSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slack')
                    ->schema([
                        TextInput::make('slack_webhook_url')
                            ->label('Slack webhook URL')
                            ->password()
                            ->revealable(),
                        TextInput::make('slack_bot_token')
                            ->label('Slack bot token')
                            ->password()
                            ->revealable(),
                        TextInput::make('slack_channel')
                            ->label('Slack channel'),
                    ]),
                Section::make('Email Services')
                    ->schema([
                        TextInput::make('postmark_token')
                            ->label('Postmark token')
                            ->password()
                            ->revealable(),
                        TextInput::make('resend_key')
                            ->label('Resend key')
                            ->password()
                            ->revealable(),
                    ]),
            ]);
    }
}
