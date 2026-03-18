<?php

declare(strict_types=1);

use App\Models\EnterpriseInquiry;

/**
 * Valid honeypot fields for enterprise-inquiries.store.
 *
 * @return array<string, mixed>
 */
function enterpriseHoneypotFields(): array
{
    return [
        config('honeypot.name_field_name', 'my_name') => '',
        config('honeypot.valid_from_field_name', 'valid_from') => encrypt(now()->subSeconds(2)->timestamp),
    ];
}

it('renders enterprise inquiry page', function (): void {
    $response = $this->get(route('enterprise-inquiries.create'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('enterprise-inquiries/create'));
});

it('stores an enterprise inquiry', function (): void {
    $response = $this->fromRoute('enterprise-inquiries.create')
        ->post(route('enterprise-inquiries.store'), [
            'name' => 'Acme Corp',
            'email' => 'sales@acme.com',
            'company' => 'Acme Inc',
            'phone' => '+32 2 123 45 67',
            'message' => 'We need an enterprise plan.',
            ...enterpriseHoneypotFields(),
        ]);

    $response->assertRedirectToRoute('enterprise-inquiries.create')
        ->assertSessionHas('status');

    $inquiry = EnterpriseInquiry::query()->where('email', 'sales@acme.com')->first();

    expect($inquiry)->not->toBeNull()
        ->and($inquiry->name)->toBe('Acme Corp')
        ->and($inquiry->company)->toBe('Acme Inc')
        ->and($inquiry->phone)->toBe('+32 2 123 45 67')
        ->and($inquiry->message)->toBe('We need an enterprise plan.')
        ->and($inquiry->status)->toBe('new');
});

it('rejects invalid phone number', function (): void {
    $response = $this->fromRoute('enterprise-inquiries.create')
        ->post(route('enterprise-inquiries.store'), [
            'name' => 'Acme Corp',
            'email' => 'sales@acme.com',
            'phone' => 'not-a-phone',
            'message' => 'Message',
            ...enterpriseHoneypotFields(),
        ]);

    $response->assertRedirectToRoute('enterprise-inquiries.create')
        ->assertSessionHasErrors('phone');
});

it('requires name', function (): void {
    $response = $this->fromRoute('enterprise-inquiries.create')
        ->post(route('enterprise-inquiries.store'), [
            'email' => 'sales@acme.com',
            'message' => 'Message',
            ...enterpriseHoneypotFields(),
        ]);

    $response->assertRedirectToRoute('enterprise-inquiries.create')
        ->assertSessionHasErrors('name');
});
