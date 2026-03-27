<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PropertyStatus;
use App\Models\Property;

final readonly class RejectProperty
{
    public function handle(Property $property): Property
    {
        $property->update(['status' => PropertyStatus::Rejected]);

        return $property->refresh();
    }
}
