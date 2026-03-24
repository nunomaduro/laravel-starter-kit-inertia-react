<?php

declare(strict_types=1);

use App\Actions\StoreEnterpriseInquiryAction;
use App\Models\EnterpriseInquiry;

it('creates an enterprise inquiry with required fields', function (): void {
    $action = resolve(StoreEnterpriseInquiryAction::class);

    $inquiry = $action->handle([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'We need enterprise features.',
    ]);

    expect($inquiry)->toBeInstanceOf(EnterpriseInquiry::class)
        ->and($inquiry->name)->toBe('Jane Doe')
        ->and($inquiry->email)->toBe('jane@example.com')
        ->and($inquiry->message)->toBe('We need enterprise features.');
});

it('creates an enterprise inquiry with optional fields', function (): void {
    $action = resolve(StoreEnterpriseInquiryAction::class);

    $inquiry = $action->handle([
        'name' => 'John Smith',
        'email' => 'john@corp.com',
        'company' => 'Corp Inc',
        'phone' => '+1234567890',
        'message' => 'Interested in plans.',
    ]);

    expect($inquiry->company)->toBe('Corp Inc')
        ->and($inquiry->phone)->toBe('+1234567890');
});
