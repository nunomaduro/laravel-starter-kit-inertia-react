<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\MailSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageMail extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Mail';

    protected static ?int $navigationSort = 10;

    protected static string $settings = MailSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mailer')
                    ->schema([
                        Select::make('mailer')
                            ->label('Mailer')
                            ->options([
                                'smtp' => 'SMTP',
                                'ses' => 'SES',
                                'postmark' => 'Postmark',
                                'resend' => 'Resend',
                                'sendmail' => 'Sendmail',
                                'log' => 'Log',
                                'array' => 'Array',
                                'failover' => 'Failover',
                                'roundrobin' => 'Round Robin',
                            ])
                            ->required(),
                    ]),
                Section::make('SMTP Configuration')
                    ->schema([
                        TextInput::make('smtp_host')
                            ->label('SMTP host'),
                        TextInput::make('smtp_port')
                            ->label('SMTP port')
                            ->numeric(),
                        TextInput::make('smtp_username')
                            ->label('SMTP username'),
                        TextInput::make('smtp_password')
                            ->label('SMTP password')
                            ->password()
                            ->revealable(),
                        Select::make('smtp_encryption')
                            ->label('SMTP encryption')
                            ->options([
                                '' => 'None',
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ]),
                    ]),
                Section::make('From Address')
                    ->schema([
                        TextInput::make('from_address')
                            ->label('From address')
                            ->email(),
                        TextInput::make('from_name')
                            ->label('From name'),
                    ]),
            ]);
    }
}
