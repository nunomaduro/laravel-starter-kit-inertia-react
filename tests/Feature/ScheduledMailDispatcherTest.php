<?php

declare(strict_types=1);

use App\Models\MailTriggerSchedule;
use App\Models\Organization;
use App\Services\ScheduledMailDispatcher;
use Laravel\Pennant\Feature;

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->dispatcher = new ScheduledMailDispatcher;
});

it('returns null when no schedule exists', function (): void {
    $result = $this->dispatcher->getScheduleForEvent(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    );

    expect($result)->toBeNull();
});

it('returns schedule when active', function (): void {
    $schedule = MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'is_active' => true,
    ]);

    $result = $this->dispatcher->getScheduleForEvent(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    );

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($schedule->id);
});

it('returns null when schedule is inactive', function (): void {
    MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'is_active' => false,
    ]);

    $result = $this->dispatcher->getScheduleForEvent(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    );

    expect($result)->toBeNull();
});

it('checks feature flag via Pennant and returns null when flag is inactive', function (): void {
    Feature::define('test-mail-feature', fn () => false);

    MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'is_active' => true,
        'feature_flag' => 'test-mail-feature',
    ]);

    $result = $this->dispatcher->getScheduleForEvent(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    );

    expect($result)->toBeNull();
});

it('returns schedule when feature flag is active', function (): void {
    Feature::define('test-mail-feature', fn () => true);

    $schedule = MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'is_active' => true,
        'feature_flag' => 'test-mail-feature',
    ]);

    $result = $this->dispatcher->getScheduleForEvent(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    );

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($schedule->id);
});

it('returns schedule when no feature flag is set', function (): void {
    $schedule = MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'is_active' => true,
        'feature_flag' => null,
    ]);

    $result = $this->dispatcher->getScheduleForEvent(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    );

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($schedule->id);
});

it('detects when a schedule should delay', function (): void {
    $schedule = MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'delay_minutes' => 30,
    ]);

    expect($this->dispatcher->shouldDelay($schedule))->toBeTrue();
});

it('detects when a schedule should not delay', function (): void {
    $schedule = MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'delay_minutes' => null,
    ]);

    expect($this->dispatcher->shouldDelay($schedule))->toBeFalse();
});

it('suppresses events when schedule is inactive', function (): void {
    MailTriggerSchedule::factory()->create([
        'organization_id' => $this->organization->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'is_active' => false,
    ]);

    expect($this->dispatcher->shouldSuppress(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    ))->toBeTrue();
});

it('does not suppress events when no schedule exists', function (): void {
    expect($this->dispatcher->shouldSuppress(
        'App\\Events\\User\\UserCreated',
        $this->organization->id,
    ))->toBeFalse();
});
