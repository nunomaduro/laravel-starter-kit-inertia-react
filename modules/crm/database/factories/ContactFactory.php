<?php

declare(strict_types=1);

namespace Modules\Crm\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Crm\Models\Contact;

/**
 * @extends Factory<Contact>
 */
final class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->company(),
            'position' => fake()->jobTitle(),
            'source' => fake()->randomElement(['website', 'referral', 'linkedin', 'cold_call', 'event', 'other']),
            'status' => fake()->randomElement(['new', 'contacted', 'qualified', 'unqualified', 'customer']),
            'notes' => null,
            'assigned_employee_id' => null,
        ];
    }

    public function withNotes(): self
    {
        return $this->state(fn (array $attributes): array => [
            'notes' => fake()->paragraph(),
        ]);
    }

    public function customer(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'customer',
        ]);
    }

    public function qualified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'qualified',
        ]);
    }
}
