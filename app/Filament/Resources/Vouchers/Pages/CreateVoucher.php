<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Vouchers\VoucherResource;
use App\Models\VoucherScope;
use Filament\Resources\Pages\CreateRecord;

final class CreateVoucher extends CreateRecord
{
    protected static string $resource = VoucherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $scope = VoucherScope::query()->first();
        if ($scope instanceof VoucherScope) {
            $data['model_type'] = VoucherScope::class;
            $data['model_id'] = $scope->id;
        }

        return $data;
    }
}
