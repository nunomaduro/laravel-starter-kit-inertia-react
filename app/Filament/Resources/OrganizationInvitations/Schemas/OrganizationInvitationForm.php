<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganizationInvitations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class OrganizationInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('role')
                    ->required()
                    ->default('member'),
                TextInput::make('token')
                    ->required(),
                TextInput::make('invited_by')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('expires_at')
                    ->required(),
                DateTimePicker::make('accepted_at'),
            ]);
    }
}
