<?php

declare(strict_types=1);

namespace Modules\BotStudio\Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentReview;

/**
 * @extends Factory<AgentReview>
 */
final class AgentReviewFactory extends Factory
{
    protected $model = AgentReview::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_definition_id' => AgentDefinition::factory(),
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'review' => fake()->optional(0.7)->paragraph(),
        ];
    }
}
