<?php

declare(strict_types=1);

namespace App\Filament\Resources\MailTemplates\Pages;

use App\Filament\Resources\MailTemplates\MailTemplateResource;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditMailTemplate extends EditRecord
{
    #[Override]
    protected static string $resource = MailTemplateResource::class;
}
