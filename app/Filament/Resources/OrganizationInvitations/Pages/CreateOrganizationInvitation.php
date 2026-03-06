<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganizationInvitations\Pages;

use App\Filament\Resources\OrganizationInvitations\OrganizationInvitationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOrganizationInvitation extends CreateRecord
{
    #[Override]
    protected static string $resource = OrganizationInvitationResource::class;
}
