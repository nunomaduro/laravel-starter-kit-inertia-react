<?php

declare(strict_types=1);

namespace App\Filament\Resources\Billing\Affiliates\Tables;

use App\Filament\Concerns\HasStandardExports;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Billing\Models\Affiliate;

final class AffiliatesTable
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
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('affiliate_code')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \Spatie\ModelStates\State ? $state->getValue() : $state)
                    ->color(fn ($state): string => match ($state instanceof \Spatie\ModelStates\State ? $state->getValue() : $state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('commission_rate')->suffix('%'),
                TextColumn::make('total_earnings')->money(config('billing.currency', 'usd')),
                TextColumn::make('pending_earnings')->money(config('billing.currency', 'usd')),
                TextColumn::make('total_referrals'),
                TextColumn::make('successful_conversions'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->headerActions([
                self::makeExportHeaderAction('affiliates'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->action(fn (Affiliate $record) => $record->approve())
                    ->requiresConfirmation()
                    ->visible(fn (Affiliate $record): bool => $record->isPending())
                    ->color('success'),
                Action::make('suspend')
                    ->action(fn (Affiliate $record) => $record->suspend())
                    ->requiresConfirmation()
                    ->visible(fn (Affiliate $record): bool => $record->isActive())
                    ->color('warning'),
                Action::make('reject')
                    ->action(fn (Affiliate $record) => $record->reject())
                    ->requiresConfirmation()
                    ->visible(fn (Affiliate $record): bool => $record->isPending())
                    ->color('danger'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::makeExportBulkAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
