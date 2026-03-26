<?php

declare(strict_types=1);

namespace Modules\BotStudio\Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentInstall;

/**
 * @extends Factory<AgentInstall>
 */
final class AgentInstallFactory extends Factory
{
    protected $model = AgentInstall::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'agent_definition_id' => AgentDefinition::factory(),
        ];
    }
}
