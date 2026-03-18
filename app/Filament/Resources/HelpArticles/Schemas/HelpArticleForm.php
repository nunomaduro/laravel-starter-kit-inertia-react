<?php

declare(strict_types=1);

namespace App\Filament\Resources\HelpArticles\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class HelpArticleForm
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
                            ->maxLength(255)
                            ->live(onBlur: true),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Auto-generated from title if left empty'),

                        Textarea::make('excerpt')
                            ->label('Excerpt')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Short summary for article listings'),

                        MarkdownEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('category')
                            ->label('Category')
                            ->maxLength(100)
                            ->helperText('Category name (e.g., Getting Started, Billing, Account)'),

                        TextInput::make('order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),

                        TagsInput::make('tag_names')
                            ->label('Tags')
                            ->placeholder('Add a tag')
                            ->suggestions(fn (): array => \Spatie\Tags\Tag::query()->pluck('name')->unique()->values()->all())
                            ->helperText('Tags for filtering and discoverability'),
                    ]),

                Section::make('Visibility')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),
                    ])
                    ->collapsible(),
            ]);
    }
}
