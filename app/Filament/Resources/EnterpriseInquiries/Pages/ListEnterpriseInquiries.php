<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnterpriseInquiries\Pages;

use App\Filament\Resources\EnterpriseInquiries\EnterpriseInquiryResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListEnterpriseInquiries extends ListRecords
{
    #[Override]
    protected static string $resource = EnterpriseInquiryResource::class;
}
