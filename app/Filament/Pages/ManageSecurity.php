<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\SecuritySettings;
use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageSecurity extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $navigationLabel = 'Security';

    protected static ?int $navigationSort = 50;

    protected static string $settings = SecuritySettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content Security Policy')
                    ->schema([
                        Toggle::make('csp_enabled')
                            ->label('CSP enabled'),
                        Toggle::make('csp_nonce_enabled')
                            ->label('CSP nonce enabled'),
                        TextInput::make('csp_report_uri')
                            ->label('CSP report URI'),
                    ]),
                Section::make('Honeypot')
                    ->schema([
                        Toggle::make('honeypot_enabled')
                            ->label('Honeypot enabled'),
                        TextInput::make('honeypot_seconds')
                            ->label('Honeypot seconds')
                            ->numeric(),
                    ]),
                Section::make('IP Whitelist')
                    ->schema([
                        TagsInput::make('ip_whitelist')
                            ->label('IP whitelist'),
                    ]),
            ]);
    }
}
