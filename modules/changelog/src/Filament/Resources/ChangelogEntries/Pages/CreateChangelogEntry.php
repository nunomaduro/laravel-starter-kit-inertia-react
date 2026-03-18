<?php

declare(strict_types=1);

namespace Modules\Changelog\Filament\Resources\ChangelogEntries\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Changelog\Filament\Resources\ChangelogEntries\ChangelogEntryResource;

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
