<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Features\ImpersonationFeature;
use App\Support\FeatureHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use STS\FilamentImpersonate\Actions\Impersonate;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('tags'))
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->searchDebounce('300ms')
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name='.urlencode((string) $record->name).'&size=48'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(','),
                TextColumn::make('tags.name')
                    ->label('Tags')
                    ->badge()
                    ->separator(', '),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()->fromTable()->withFilename('users-'.now()->format('Y-m-d')),
                        ExcelExport::make()->fromTable()
                            ->withFilename('users-'.now()->format('Y-m-d').'-csv')
                            ->withWriterType(Excel::CSV),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Impersonate::make()
                    ->visible(fn (): bool => auth()->user()?->hasRole('super-admin') === true
                        && FeatureHelper::isActiveForClass(ImpersonationFeature::class, auth()->user())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
