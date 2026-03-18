<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganizationInvitations\Pages;

use App\Filament\Resources\OrganizationInvitations\OrganizationInvitationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateOrganizationInvitation extends CreateRecord
{
    protected static string $resource = OrganizationInvitationResource::class;
}
