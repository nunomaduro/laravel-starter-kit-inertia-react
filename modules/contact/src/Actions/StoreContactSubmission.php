<?php

declare(strict_types=1);

namespace Modules\Contact\Actions;

use Modules\Contact\Models\ContactSubmission;

final readonly class StoreContactSubmission
{
    /**
     * @param  array{name: string, email: string, subject: string, message: string}  $data
     */
    public function handle(array $data): ContactSubmission
    {
        return ContactSubmission::query()->create($data);
    }
}
