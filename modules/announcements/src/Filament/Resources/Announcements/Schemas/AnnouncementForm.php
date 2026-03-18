<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Announcements\Enums\AnnouncementLevel;
use Modules\Announcements\Enums\AnnouncementScope;

final class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user?->can('announcements.manage_global') ?? false;

        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('body')
                            ->label('Body')
                            ->required()
                            ->rows(5),

                        Select::make('level')
                            ->label('Level')
                            ->options(collect(AnnouncementLevel::cases())->mapWithKeys(fn (AnnouncementLevel $l): array => [$l->value => $l->name])->all())
                            ->default(AnnouncementLevel::Info)
                            ->required(),

                        Select::make('scope')
                            ->label('Scope')
                            ->options(collect(AnnouncementScope::cases())->mapWithKeys(fn (AnnouncementScope $s): array => [$s->value => $s->name])->all())
                            ->default(AnnouncementScope::Global)
                            ->required()
                            ->live()
                            ->visible($isSuperAdmin),

                        Select::make('organization_id')
                            ->label('Organization')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => $isSuperAdmin && $get('scope') === AnnouncementScope::Organization->value),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        DateTimePicker::make('starts_at')
                            ->label('Starts at')
                            ->nullable(),

                        DateTimePicker::make('ends_at')
                            ->label('Ends at')
                            ->nullable(),
                    ]),
            ]);
    }
}
