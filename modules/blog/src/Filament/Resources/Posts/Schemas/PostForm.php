<?php

declare(strict_types=1);

namespace Modules\Blog\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->required()
                            ->default(fn () => auth()->id())
                            ->searchable()
                            ->preload(),

                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('meta_title', $state)),

                        Textarea::make('excerpt')
                            ->label('Excerpt')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Short summary of the post (optional)'),

                        MarkdownEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull(),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->default(now())
                            ->visible(fn ($get) => $get('is_published')),
                    ]),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->helperText('SEO title (defaults to post title if empty)'),

                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('SEO description for search engines'),

                        TextInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->maxLength(255)
                            ->helperText('Comma-separated keywords'),
                    ])
                    ->collapsible(),

                Section::make('Categorization')
                    ->schema([
                        TagsInput::make('tag_names')
                            ->label('Tags')
                            ->placeholder('Add a tag')
                            ->suggestions(fn (): array => \Spatie\Tags\Tag::query()->pluck('name')->unique()->values()->all())
                            ->helperText('Add tags for better organization and discoverability'),
                    ])
                    ->collapsible(),
            ]);
    }
}
