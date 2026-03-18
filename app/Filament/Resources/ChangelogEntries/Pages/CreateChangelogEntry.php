<?php

declare(strict_types=1);

namespace App\Filament\Resources\ChangelogEntries\Pages;

use App\Filament\Resources\ChangelogEntries\ChangelogEntryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateChangelogEntry extends CreateRecord
{
    protected static string $resource = ChangelogEntryResource::class;

    /**
     * @var list<string>
     */
    private array $pendingTagNames = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingTagNames = array_values(array_filter(
            is_array($data['tag_names'] ?? null) ? $data['tag_names'] : [],
            fn ($v): bool => is_string($v) && $v !== ''
        ));
        unset($data['tag_names']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncTags($this->pendingTagNames);
    }
}
