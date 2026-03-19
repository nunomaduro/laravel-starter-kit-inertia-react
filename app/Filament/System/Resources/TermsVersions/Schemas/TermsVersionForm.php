<?php

declare(strict_types=1);

namespace App\Filament\System\Resources\TermsVersions\Schemas;

use App\Enums\TermsType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class TermsVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier (e.g. terms-v1)'),
                        Select::make('type')
                            ->options(TermsType::class)
                            ->required()
                            ->native(false),
                        DatePicker::make('effective_at')
                            ->required()
                            ->native(false),
                        Textarea::make('summary')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Optional short "what changed" for users'),
                        Textarea::make('body')
                            ->required()
                            ->rows(12)
                            ->columnSpanFull()
                            ->helperText('Full content (Markdown supported on the acceptance page)'),
                        Toggle::make('is_required')
                            ->label('Block app until accepted')
                            ->helperText('Users must accept this version before using the app')
                            ->default(false),
                    ])->columns(2),
            ]);
    }
}
