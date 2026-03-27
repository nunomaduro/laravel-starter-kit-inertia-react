<?php

declare(strict_types=1);

use App\Actions\ApproveProperty;
use App\Enums\PropertyStatus;
use App\Models\Property;

it('approves a property', function (): void {
    $property = Property::factory()->pending()->create();

    $result = app(ApproveProperty::class)->handle($property);

    expect($result->status)->toBe(PropertyStatus::Approved);
});
