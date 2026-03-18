<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static ?int $navigationSort = 40;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Audit Log';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return filament()->getCurrentPanel()?->getId() === 'system' && $user !== null && $user->isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->default('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organization')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject Type')
                    ->default('—'),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('Subject')
                    ->default('—'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->options([
                        'theme.saved' => 'Theme Saved',
                        'theme.reset' => 'Theme Reset',
                        'logo.uploaded' => 'Logo Uploaded',
                        'branding.user_controls.changed' => 'Branding Controls Changed',
                        'feature.toggled' => 'Feature Toggled',
                        'member.invited' => 'Member Invited',
                        'member.removed' => 'Member Removed',
                        'role.created' => 'Role Created',
                        'role.deleted' => 'Role Deleted',
                        'system.setting.changed' => 'System Setting Changed',
                        'slug.changed' => 'Workspace URL Changed',
                        'domain.added' => 'Custom Domain Added',
                        'domain.removed' => 'Custom Domain Removed',
                    ]),
                SelectFilter::make('organization')
                    ->relationship('organization', 'name'),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
        ];
    }
}
