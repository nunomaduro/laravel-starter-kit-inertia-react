<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationDomain>
 */
final class OrganizationDomainFactory extends Factory
{
    protected $model = OrganizationDomain::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'domain' => fake()->domainName(),
            'type' => 'custom',
            'status' => fake()->randomElement(['active', 'pending_dns']),
            'is_verified' => fake()->boolean(70),
            'verification_token' => fake()->optional(0.3)->uuid(),
            'is_primary' => false,
            'cname_target' => null,
            'failure_reason' => null,
            'dns_check_attempts' => 0,
            'verified_at' => fake()->optional(0.7)->dateTimeThisYear(),
            'last_dns_check_at' => null,
            'ssl_issued_at' => null,
            'ssl_expires_at' => null,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => ['is_primary' => true]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_verified' => true,
            'status' => 'active',
            'verified_at' => now(),
        ]);
    }
}
