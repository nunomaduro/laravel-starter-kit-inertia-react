<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

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

final class PostsTable
{
    use HasStandardExports;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('author'))
            ->defaultSort('published_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->searchDebounce('300ms')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                IconColumn::make('featured_flag')
                    ->label('Featured')
                    ->getStateUsing(fn ($record) => $record->hasFlag('featured'))
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('views')
                    ->label('Views')
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
                self::makeExportHeaderAction('posts'),
            ])
            ->recordActions([
                Action::make('feature')
                    ->label('Feature')
                    ->action(fn ($record) => $record->flag('featured'))
                    ->visible(fn ($record) => ! $record->hasFlag('featured'))
                    ->successNotificationTitle('Post featured'),
                Action::make('unfeature')
                    ->label('Unfeature')
                    ->action(fn ($record) => $record->unflag('featured'))
                    ->visible(fn ($record) => $record->hasFlag('featured'))
                    ->successNotificationTitle('Post unfeatured'),
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
            ]);
    }
}
