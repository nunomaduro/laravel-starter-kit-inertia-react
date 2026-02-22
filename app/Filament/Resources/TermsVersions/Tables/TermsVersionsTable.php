<?php

declare(strict_types=1);

namespace App\Filament\Resources\TermsVersions\Tables;

use App\Enums\TermsType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

final class TermsVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (TermsType $state): string => $state->label()),
                TextColumn::make('effective_at')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_required')
                    ->boolean()
                    ->label('Required'),
                TextColumn::make('acceptances_count')
                    ->counts('acceptances')
                    ->label('Acceptances')
                    ->sortable(),
            ])
            ->defaultSort('effective_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->fromTable()->withFilename('terms-versions-'.now()->format('Y-m-d')),
                        ExcelExport::make()->fromTable()
                            ->withFilename('terms-versions-'.now()->format('Y-m-d').'-csv')
                            ->withWriterType(Excel::CSV),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
