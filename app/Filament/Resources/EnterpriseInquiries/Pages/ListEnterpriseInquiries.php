<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnterpriseInquiries\Pages;

use App\Filament\Resources\EnterpriseInquiries\EnterpriseInquiryResource;
use Filament\Resources\Pages\ListRecords;

final class ListEnterpriseInquiries extends ListRecords
{
    protected static string $resource = EnterpriseInquiryResource::class;
}
