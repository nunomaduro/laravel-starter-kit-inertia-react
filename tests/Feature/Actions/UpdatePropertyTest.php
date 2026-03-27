<?php

declare(strict_types=1);

use App\Actions\UpdateProperty;
use App\Enums\PropertyStatus;
use App\Models\Property;

it('updates property attributes', function (): void {
    $property = Property::factory()->create(['status' => 'approved']);

    $result = app(UpdateProperty::class)->handle($property, [
        'description' => 'Updated description',
    ]);

    expect($result->description)->toBe('Updated description')
        ->and($result->status)->toBe(PropertyStatus::Approved);
});

it('resets status to pending when key fields change', function (): void {
    $property = Property::factory()->create([
        'status' => 'approved',
        'name' => 'Original Name',
    ]);

    $result = app(UpdateProperty::class)->handle($property, [
        'name' => 'New Name',
    ]);

    expect($result->status)->toBe(PropertyStatus::Pending)
        ->and($result->slug)->toStartWith('new-name');
});

it('does not reset status when non-key fields change', function (): void {
    $property = Property::factory()->create(['status' => 'approved']);

    $result = app(UpdateProperty::class)->handle($property, [
        'description' => 'New description',
        'cancellation_policy' => 'Flexible',
    ]);

    expect($result->status)->toBe(PropertyStatus::Approved);
});
