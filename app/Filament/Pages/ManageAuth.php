<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\AuthSettings;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageAuth extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Auth';

    protected static ?int $navigationSort = 20;

    protected static string $settings = AuthSettings::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('registration_enabled')
                    ->label('Registration enabled'),
                Toggle::make('email_verification_required')
                    ->label('Email verification required'),
            ]);
    }
}
