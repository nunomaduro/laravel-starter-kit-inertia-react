<?php

declare(strict_types=1);

namespace App\Filament\Resources\Announcements\Tables;

use App\Enums\AnnouncementLevel;
use App\Enums\AnnouncementScope;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
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
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
