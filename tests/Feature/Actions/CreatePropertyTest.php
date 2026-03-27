<?php

declare(strict_types=1);

use App\Actions\CreateProperty;
use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;

it('creates a property with pending status', function (): void {
    $host = User::factory()->host()->create();

    $property = app(CreateProperty::class)->handle($host, [
        'name' => 'Sunset Beach Resort',
        'description' => 'A beautiful resort',
        'type' => 'resort',
        'address' => '123 Beach Rd',
        'city' => 'Malibu',
        'country' => 'United States',
    ]);

    expect($property)->toBeInstanceOf(Property::class)
        ->and($property->host_id)->toBe($host->id)
        ->and($property->status)->toBe(PropertyStatus::Pending)
        ->and($property->slug)->toStartWith('sunset-beach-resort');
});

it('generates unique slug when name already exists', function (): void {
    $host = User::factory()->host()->create();

    Property::factory()->create(['slug' => 'sunset-resort']);

    $property = app(CreateProperty::class)->handle($host, [
        'name' => 'Sunset Resort',
        'description' => 'A resort',
        'type' => 'resort',
        'address' => '456 Beach Rd',
        'city' => 'Malibu',
        'country' => 'United States',
    ]);

    expect($property->slug)->not->toBe('sunset-resort')
        ->and($property->slug)->toStartWith('sunset-resort-');
});
