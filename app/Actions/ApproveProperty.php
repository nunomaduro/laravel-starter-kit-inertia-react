<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PropertyStatus;
use App\Models\Property;

final readonly class ApproveProperty
{
    public function handle(Property $property): Property
    {
        $property->update(['status' => PropertyStatus::Approved]);

        return $property->refresh();
    }
}
