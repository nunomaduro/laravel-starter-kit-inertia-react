<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\EmbeddingDemo;
use App\Models\ModelEmbedding;
use App\Models\Organization;
use Illuminate\Database\Seeder;

final class ModelEmbeddingSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['OrganizationSeeder'];

    public function run(): void
    {
        $org = Organization::query()->first();

        if (! $org) {
            return;
        }

        $demo = EmbeddingDemo::query()->first();

        if (! $demo) {
            return;
        }

        // Seed a sample embedding record for the demo model
        ModelEmbedding::query()->firstOrCreate(
            [
                'organization_id' => $org->id,
                'embeddable_type' => EmbeddingDemo::class,
                'embeddable_id' => $demo->id,
                'chunk_index' => 0,
            ],
            [
                'content_hash' => md5('demo embedding content'),
                'metadata' => ['source' => 'seeder', 'model' => 'text-embedding-3-small'],
            ]
        );
    }
}
