<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\AuthSettings;
use BackedEnum;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageAuth extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Auth';

    protected static ?int $navigationSort = 20;

    protected static string $settings = AuthSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registration & Verification')
                    ->schema([
                        Toggle::make('registration_enabled')
                            ->label('Registration enabled'),
                        Toggle::make('email_verification_required')
                            ->label('Email verification required'),
                    ]),
                Section::make('Two-Factor Authentication')
                    ->schema([
                        Select::make('two_factor_enforcement')
                            ->label('2FA enforcement')
                            ->options([
                                'optional' => 'Optional (users choose)',
                                'admins_only' => 'Required for admins only',
                                'required' => 'Required for all users',
                            ])
                            ->required()
                            ->helperText('Users will be prompted to enable 2FA on next login when enforcement is active.'),
                    ]),
                Section::make('Session')
                    ->schema([
                        TextInput::make('session_lifetime')
                            ->label('Session lifetime (minutes)')
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(43200)
                            ->required()
                            ->helperText('How long an idle session stays active. Default: 120 minutes.'),
                    ]),
                Section::make('Password Policy')
                    ->schema([
                        TextInput::make('password_min_length')
                            ->label('Minimum password length')
                            ->numeric()
                            ->minValue(6)
                            ->maxValue(128)
                            ->required(),
                        Toggle::make('password_require_uppercase')
                            ->label('Require uppercase letter'),
                        Toggle::make('password_require_numbers')
                            ->label('Require number'),
                        Toggle::make('password_require_symbols')
                            ->label('Require symbol'),
                    ]),
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
