<?php

declare(strict_types=1);

namespace Modules\Changelog\Filament\Resources\ChangelogEntries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Changelog\Enums\ChangelogType;

final class ChangelogEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(5)
                            ->helperText('Detailed description of the change'),

                        TextInput::make('version')
                            ->label('Version')
                            ->maxLength(50)
                            ->helperText('Version number (e.g., 1.2.3)'),

                        Select::make('type')
                            ->label('Type')
                            ->options(collect(ChangelogType::cases())->mapWithKeys(fn (ChangelogType $t): array => [$t->value => $t->name])->all())
                            ->default(ChangelogType::Added)
                            ->required(),

                        TagsInput::make('tag_names')
                            ->label('Tags')
                            ->placeholder('Add a tag')
                            ->suggestions(fn (): array => \Spatie\Tags\Tag::query()->pluck('name')->unique()->values()->all())
                            ->helperText('Tags for this changelog entry'),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),

                        DateTimePicker::make('released_at')
                            ->label('Release Date')
                            ->default(now())
                            ->visible(fn ($get) => $get('is_published')),
                    ]),
            ]);
    }
}
