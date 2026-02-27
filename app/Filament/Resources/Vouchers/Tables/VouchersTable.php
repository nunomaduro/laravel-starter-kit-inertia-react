<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vouchers\Tables;

use App\Filament\Concerns\HasStandardExports;
use BeyondCode\Vouchers\Models\Voucher;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class VouchersTable
{
    use HasStandardExports;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->searchDebounce('300ms')
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('data.discount_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => $state === 'fixed' ? 'Fixed' : 'Percentage'),

                TextColumn::make('data.discount_amount')
                    ->label('Amount')
                    ->suffix(fn (Voucher $record): string => ($record->data['discount_type'] ?? '') === 'percentage' ? '%' : ''),

                TextColumn::make('expires_at')
                    ->date()
                    ->sortable()
                    ->placeholder('Never'),

                TextColumn::make('users_count')
                    ->label('Redeemed')
                    ->counts('users'),
            ])
            ->headerActions([
                self::makeExportHeaderAction('vouchers'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::makeExportBulkAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
