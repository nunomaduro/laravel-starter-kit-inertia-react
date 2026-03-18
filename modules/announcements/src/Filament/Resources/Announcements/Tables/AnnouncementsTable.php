<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources\Announcements\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Announcements\Enums\AnnouncementLevel;
use Modules\Announcements\Enums\AnnouncementScope;

final class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position', 'asc')
            ->reorderable('position')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(fn (AnnouncementLevel $state): string => $state->name)
                    ->color(fn (AnnouncementLevel $state): string => match ($state) {
                        AnnouncementLevel::Info => 'info',
                        AnnouncementLevel::Warning => 'warning',
                        AnnouncementLevel::Maintenance => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('scope')
                    ->badge()
                    ->formatStateUsing(fn (AnnouncementScope $state): string => $state->name),
                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->placeholder('Global')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                IconColumn::make('featured_flag')
                    ->label('Featured')
                    ->getStateUsing(fn ($record) => $record->hasFlag('featured'))
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('feature')
                    ->label('Feature')
                    ->action(fn ($record) => $record->flag('featured'))
                    ->visible(fn ($record) => ! $record->hasFlag('featured'))
                    ->successNotificationTitle('Announcement featured'),
                Action::make('unfeature')
                    ->label('Unfeature')
                    ->action(fn ($record) => $record->unflag('featured'))
                    ->visible(fn ($record) => $record->hasFlag('featured'))
                    ->successNotificationTitle('Announcement unfeatured'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
