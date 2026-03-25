<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Services\SemanticSearchService;
use App\Support\TenantContext;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * AI tool for semantic search over embedded models.
 *
 * This is a base tool (always available) that wraps the SemanticSearchService.
 * Agents can invoke this to search embeddings scoped to the current organization.
 */
final class SemanticSearchTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search across all indexed content using natural language. Returns the most semantically similar results from contacts, deals, documents, and other embedded records.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The natural language search query.'),
            'limit' => $schema->integer()->description('Maximum number of results to return. Defaults to 10.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $queryText = $request->get('query');

        if ($queryText === null || $queryText === '') {
            return 'A search query is required.';
        }

        $organizationId = TenantContext::id();

        if ($organizationId === null) {
            return 'No organization context available. Cannot perform search.';
        }

        $limit = (int) $request->get('limit', 10);

        $results = SemanticSearchService::query($queryText)
            ->forOrganization($organizationId)
            ->limit($limit)
            ->get();

        if ($results->isEmpty()) {
            return 'No results found for: '.$queryText;
        }

        $output = "# Search Results\n\n";

        foreach ($results as $index => $model) {
            /** @var Model $model */
            $number = $index + 1;
            $type = class_basename($model);
            $score = round((float) $model->getAttribute('similarity_score'), 4);
            $id = $model->getKey();

            $output .= "**{$number}. {$type} #{$id}** (similarity: {$score})\n";

            $searchable = method_exists($model, 'toSearchableArray')
                ? $model->toSearchableArray()
                : $model->toArray();

            foreach (array_slice($searchable, 0, 5) as $key => $value) {
                if ($key === 'id' || $value === null || is_array($value)) {
                    continue;
                }

                $display = mb_strlen((string) $value) > 100
                    ? mb_substr((string) $value, 0, 100).'...'
                    : (string) $value;

                $output .= "  - {$key}: {$display}\n";
            }

            $output .= "\n";
        }

        return $output;
    }
}
