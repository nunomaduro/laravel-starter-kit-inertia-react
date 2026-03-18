<?php

declare(strict_types=1);

namespace Modules\Contact\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Contact\Models\ContactSubmission;

/**
 * @extends Factory<ContactSubmission>
 */
final class ContactSubmissionFactory extends Factory
{
    protected $model = ContactSubmission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraphs(2, true),
            'status' => 'new',
        ];
    }
}
