<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\AuthSettings;
use BackedEnum;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class ManageAuth extends SettingsPage
{
    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    #[Override]
    protected static ?string $navigationLabel = 'Auth';

    #[Override]
    protected static ?int $navigationSort = 20;

    #[Override]
    protected static string $settings = AuthSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('registration_enabled')
                    ->label('Registration enabled'),
                Toggle::make('email_verification_required')
                    ->label('Email verification required'),
                Section::make('Social Login Providers')
                    ->description('Configure OAuth login for Google and GitHub.')
                    ->schema([
                        Toggle::make('google_oauth_enabled')
                            ->label('Enable Google OAuth'),
                        TextInput::make('google_client_id')
                            ->label('Google Client ID'),
                        TextInput::make('google_client_secret')
                            ->label('Google Client Secret')
                            ->password()
                            ->revealable(),
                        Toggle::make('github_oauth_enabled')
                            ->label('Enable GitHub OAuth'),
                        TextInput::make('github_client_id')
                            ->label('GitHub Client ID'),
                        TextInput::make('github_client_secret')
                            ->label('GitHub Client Secret')
                            ->password()
                            ->revealable(),
                    ]),
            ]);
    }
}
