<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnterpriseInquiries\Pages;

use App\Filament\Resources\EnterpriseInquiries\EnterpriseInquiryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewEnterpriseInquiry extends ViewRecord
{
    protected static string $resource = EnterpriseInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
