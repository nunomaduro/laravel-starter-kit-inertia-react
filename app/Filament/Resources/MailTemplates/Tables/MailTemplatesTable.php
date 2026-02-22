<?php

declare(strict_types=1);

namespace App\Filament\Resources\MailTemplates\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

final class MailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->fromTable()->withFilename('mail-templates-'.now()->format('Y-m-d')),
                        ExcelExport::make()->fromTable()
                            ->withFilename('mail-templates-'.now()->format('Y-m-d').'-csv')
                            ->withWriterType(Excel::CSV),
                    ]),
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
