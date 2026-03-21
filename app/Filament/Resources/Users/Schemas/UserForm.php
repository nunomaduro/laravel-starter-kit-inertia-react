<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->rules(['nullable', 'phone:INTERNATIONAL']),
                Select::make('roles')
                    ->relationship(titleAttribute: 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TagsInput::make('tag_names')
                    ->label('Tags')
                    ->placeholder('Add a tag')
                    ->suggestions(
                        fn (): array => \Spatie\Tags\Tag::query()->pluck('name')->unique()->values()->all()
                    ),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (Get $get): bool => $get('id') === null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (Get $get): ?string => $get('id') !== null ? 'Leave blank to keep current password.' : null),
                Fieldset::make('Two-Factor Authentication')
                    ->schema([
                        Textarea::make('two_factor_secret')
                            ->columnSpanFull(),
                        Textarea::make('two_factor_recovery_codes')
                            ->columnSpanFull(),
                        DateTimePicker::make('two_factor_confirmed_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
