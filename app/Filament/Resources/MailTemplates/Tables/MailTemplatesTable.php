<?php

declare(strict_types=1);

namespace App\Filament\Resources\MailTemplates\Tables;

use App\Filament\Concerns\HasStandardExports;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class MailTemplatesTable
{
    use HasStandardExports;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->searchDebounce('300ms')
            ->headerActions([
                self::makeExportHeaderAction('mail-templates'),
            ])
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('event')
                    ->label('Event')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')->limit(50)->searchable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
