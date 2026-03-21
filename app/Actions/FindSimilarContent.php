<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Ai\Embeddings;
use Pgvector\Laravel\Distance;

final readonly class FindSimilarContent
{
    /**
     * Find the most semantically similar records to the given query.
     *
     * @param  class-string<Model>  $modelClass
     * @return Collection<int, Model>
     */
    public function handle(string $query, string $modelClass, int $limit = 5): Collection
    {
        $response = Embeddings::for([$query])->generate();

        return $modelClass::query()
            ->whereNotNull('embedding')
            ->nearestNeighbors('embedding', $response->first(), Distance::Cosine)
            ->take($limit)
            ->get();
    }
}
