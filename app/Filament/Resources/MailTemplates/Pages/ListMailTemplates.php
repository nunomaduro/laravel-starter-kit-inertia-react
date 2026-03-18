<?php

declare(strict_types=1);

namespace App\Filament\Resources\MailTemplates\Pages;

use App\Filament\Resources\MailTemplates\MailTemplateResource;
use Filament\Resources\Pages\ListRecords;

final class ListMailTemplates extends ListRecords
{
    protected static string $resource = MailTemplateResource::class;
}
