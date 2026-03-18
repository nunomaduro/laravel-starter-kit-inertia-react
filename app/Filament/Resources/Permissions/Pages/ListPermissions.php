<?php

declare(strict_types=1);

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

final class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncFromRoutes')
                ->label('Sync from routes')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    Artisan::call('permission:sync-routes', ['--silent' => true]);

                    Notification::make()
                        ->title('Permissions synced from routes')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Sync permissions from routes')
                ->modalDescription('This will create or update permissions from named application routes. Existing permissions not matching any route may be kept unless you run with --prune.'),
        ];
    }
}
