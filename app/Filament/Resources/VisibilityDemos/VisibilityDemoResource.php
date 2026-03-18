<?php

declare(strict_types=1);

namespace App\Filament\Resources\VisibilityDemos;

use App\Enums\VisibilityEnum;
use App\Filament\Resources\VisibilityDemos\Pages\CreateVisibilityDemo;
use App\Filament\Resources\VisibilityDemos\Pages\EditVisibilityDemo;
use App\Filament\Resources\VisibilityDemos\Pages\ListVisibilityDemos;
use App\Models\VisibilityDemo;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class VisibilityDemoResource extends Resource
{
    protected static ?string $model = VisibilityDemo::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Visibility demos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 50;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'admin';
    }

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $defaultShareToAll = config('tenancy.super_admin.default_share_new_to_all_orgs', true);

        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Visibility')
                    ->schema([
                        Toggle::make('share_to_all_orgs')
                            ->label(__('Share to all :terms', ['terms' => mb_strtolower(__((string) config('tenancy.term_plural', 'Organizations')))]))
                            ->helperText(__('When on, this item is visible to every :term (read-only for them).', ['term' => mb_strtolower(__((string) config('tenancy.term', 'Organization')))]))
                            ->default($defaultShareToAll)
                            ->visible(fn (): bool => $isSuperAdmin),
                        Select::make('visibility')
                            ->label(__('Visibility'))
                            ->options(VisibilityEnum::class)
                            ->visible(fn (): bool => ! $isSuperAdmin),
                    ])
                    ->visible(fn (): bool => $isSuperAdmin),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn (VisibilityEnum $state): string => $state->label()),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisibilityDemos::route('/'),
            'create' => CreateVisibilityDemo::route('/create'),
            'edit' => EditVisibilityDemo::route('/{record}/edit'),
        ];
    }
}
