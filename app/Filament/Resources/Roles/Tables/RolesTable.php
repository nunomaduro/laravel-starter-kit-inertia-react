<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Tables;

use App\Filament\Concerns\HasStandardExports;
use App\Filament\Resources\Roles\RoleResource;
use App\Services\ActivityLogRbac;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

final class RolesTable
{
    use HasStandardExports;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->searchDebounce('300ms')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->badge()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->sortable(),
            ])
            ->headerActions([
                self::makeExportHeaderAction('roles'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Role $record): \Illuminate\Http\RedirectResponse {
                        $baseName = 'Copy of '.$record->name;
                        $name = $baseName;
                        $counter = 1;
                        while (Role::query()->where('name', $name)->where('guard_name', $record->guard_name)->exists()) {
                            $name = $baseName.' ('.$counter.')';
                            $counter++;
                        }

                        $newRole = Role::query()->create([
                            'name' => $name,
                            'guard_name' => $record->guard_name,
                        ]);
                        $permissionNames = $record->permissions->pluck('name')->all();
                        $newRole->syncPermissions($permissionNames);
                        resolve(ActivityLogRbac::class)->logPermissionsAssigned($newRole, $permissionNames);
                        Notification::make()
                            ->title('Role duplicated')
                            ->body(sprintf('Created "%s" with same permissions.', $name))
                            ->success()
                            ->send();

                        return redirect(RoleResource::getUrl('edit', ['record' => $newRole]));
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::makeExportBulkAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
