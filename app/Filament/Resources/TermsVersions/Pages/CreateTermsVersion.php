<?php

declare(strict_types=1);

namespace App\Filament\Resources\TermsVersions\Pages;

use App\Filament\Resources\TermsVersions\TermsVersionResource;
use App\Jobs\NotifyUsersOfNewTermsVersion;
use App\Models\TermsVersion;
use Filament\Resources\Pages\CreateRecord;

final class CreateTermsVersion extends CreateRecord
{
    protected static string $resource = TermsVersionResource::class;

    protected function afterCreate(): void
    {
        /** @var TermsVersion $record */
        $record = $this->record;
        if ($record->is_required) {
            dispatch(new NotifyUsersOfNewTermsVersion($record));
        }
    }
}
