<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnterpriseInquiries\Pages;

use App\Filament\Resources\EnterpriseInquiries\EnterpriseInquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditEnterpriseInquiry extends EditRecord
{
    protected static string $resource = EnterpriseInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
