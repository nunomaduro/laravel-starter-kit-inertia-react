<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ModelEmbedding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Ai\Embeddings;

final class SemanticSearchService
{
    private string $queryText;

    /** @var array<int, class-string<Model>> */
    private array $scopes = [];

    private ?int $organizationId = null;

    private int $limit = 10;

    private ?float $threshold = null;

    private function __construct(string $queryText)
    {
        $this->queryText = $queryText;
    }

    public static function query(string $queryText): self
    {
        return new self($queryText);
    }

    /**
     * @param  class-string<Model>  ...$modelClasses
     */
    public function scope(string ...$modelClasses): self
    {
        $this->scopes = array_values($modelClasses);

        return $this;
    }

    public function forOrganization(int $organizationId): self
    {
        $this->organizationId = $organizationId;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function threshold(float $threshold): self
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * @return Collection<int, Model>
     */
    public function get(): Collection
    {
        if ($this->organizationId === null) {
            throw new InvalidArgumentException('forOrganization() is required');
        }

        $response = Embeddings::for([$this->queryText])->generate();

        /** @var array<int, float> $queryVector */
        $queryVector = $response->first();
        $vectorString = '['.implode(',', $queryVector).']';

        $selectExpression = "model_embeddings.*, 1 - (embedding <=> '".$vectorString."'::vector) as similarity_score";

        $query = ModelEmbedding::query()
            ->selectRaw($selectExpression) // @phpstan-ignore argument.type
            ->where('organization_id', $this->organizationId);

        if ($this->scopes !== []) {
            $morphTypes = array_map(
                fn (string $class): string => (new $class)->getMorphClass(),
                $this->scopes,
            );
            $query->whereIn('embeddable_type', $morphTypes);
        }

        if ($this->threshold !== null) {
            $query->havingRaw('1 - (embedding <=> ?::vector) >= ?', [$vectorString, $this->threshold]);
        }

        $query->orderByDesc('similarity_score')
            ->limit($this->limit);

        /** @var \Illuminate\Database\Eloquent\Collection<int, ModelEmbedding> $embeddings */
        $embeddings = $query->get();

        /** @var Collection<int, Model> $result */
        $result = $embeddings
            ->groupBy('embeddable_type')
            ->flatMap(function (Collection $group): array {
                /** @var ModelEmbedding $first */
                $first = $group->first();

                /** @var class-string<Model> $type */
                $type = $first->embeddable_type;

                /** @var array<int, int|string> $ids */
                $ids = $group->pluck('embeddable_id')->all();

                /** @var Collection<int|string, mixed> $scores */
                $scores = $group->pluck('similarity_score', 'embeddable_id');

                $instance = new $type;

                /** @var \Illuminate\Database\Eloquent\Collection<int, Model> $models */
                $models = $instance->newQuery()
                    ->withoutGlobalScopes()
                    ->whereIn($instance->getKeyName(), $ids)
                    ->get();

                $models->each(function (Model $model) use ($scores): void {
                    /** @var float $score */
                    $score = $scores[$model->getKey()];
                    $model->setAttribute('similarity_score', $score);
                });

                return $models->all();
            })
            ->sortByDesc('similarity_score')
            ->values();

        return $result;
    }
}
