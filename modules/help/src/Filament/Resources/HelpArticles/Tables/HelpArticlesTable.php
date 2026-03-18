<?php

declare(strict_types=1);

namespace Modules\Help\Filament\Resources\HelpArticles\Tables;

use App\Filament\Concerns\HasStandardExports;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class HelpArticlesTable
{
    use HasStandardExports;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tags.name')
                    ->label('Tags')
                    ->toggleable(),
                TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('helpful_count')
                    ->label('Helpful')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                IconColumn::make('featured_flag')
                    ->label('Featured')
                    ->getStateUsing(fn ($record) => $record->hasFlag('featured'))
                    ->boolean(),
                IconColumn::make('pinned_flag')
                    ->label('Pinned')
                    ->getStateUsing(fn ($record) => $record->hasFlag('pinned'))
                    ->boolean(),
                TextColumn::make('order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                self::makeExportHeaderAction('help-articles'),
            ])
            ->recordActions([
                Action::make('feature')
                    ->label('Feature')
                    ->action(fn ($record) => $record->flag('featured'))
                    ->visible(fn ($record) => ! $record->hasFlag('featured'))
                    ->successNotificationTitle('Article featured'),
                Action::make('unfeature')
                    ->label('Unfeature')
                    ->action(fn ($record) => $record->unflag('featured'))
                    ->visible(fn ($record) => $record->hasFlag('featured'))
                    ->successNotificationTitle('Article unfeatured'),
                Action::make('pin')
                    ->label('Pin')
                    ->action(fn ($record) => $record->flag('pinned'))
                    ->visible(fn ($record) => ! $record->hasFlag('pinned'))
                    ->successNotificationTitle('Article pinned'),
                Action::make('unpin')
                    ->label('Unpin')
                    ->action(fn ($record) => $record->unflag('pinned'))
                    ->visible(fn ($record) => $record->hasFlag('pinned'))
                    ->successNotificationTitle('Article unpinned'),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::makeExportBulkAction(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->searchDebounce('300ms');
    }
}
