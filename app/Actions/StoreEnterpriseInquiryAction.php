<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\EnterpriseInquiry;

final readonly class StoreEnterpriseInquiryAction
{
    /**
     * @param  array{name: string, email: string, company?: string, phone?: string, message: string}  $data
     */
    public function handle(array $data): EnterpriseInquiry
    {
        return EnterpriseInquiry::query()->create($data);
    }
}
