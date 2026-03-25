<?php

declare(strict_types=1);

namespace Modules\BotStudio\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BotStudio\Models\AgentKnowledgeFile;

/**
 * @extends Factory<AgentKnowledgeFile>
 */
final class AgentKnowledgeFileFactory extends Factory
{
    protected $model = AgentKnowledgeFile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 10485760),
            'status' => 'pending',
            'chunk_count' => 0,
        ];
    }

    public function indexed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'indexed',
            'chunk_count' => fake()->numberBetween(5, 100),
            'processed_at' => now(),
        ]);
    }

    public function processing(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'processing',
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }
}
