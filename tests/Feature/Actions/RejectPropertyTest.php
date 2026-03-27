<?php

declare(strict_types=1);

use App\Actions\RejectProperty;
use App\Enums\PropertyStatus;
use App\Models\Property;

it('rejects a property', function (): void {
    $property = Property::factory()->pending()->create();

    $result = app(RejectProperty::class)->handle($property);

    expect($result->status)->toBe(PropertyStatus::Rejected);
});
