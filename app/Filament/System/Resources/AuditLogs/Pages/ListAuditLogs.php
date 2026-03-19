<?php

declare(strict_types=1);

namespace App\Filament\System\Resources\AuditLogs\Pages;

use App\Filament\System\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
