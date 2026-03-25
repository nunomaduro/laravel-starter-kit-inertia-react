<?php

declare(strict_types=1);

namespace Modules\BotStudio\Ai\Tools;

use App\Models\ModelEmbedding;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Tools\Request;
use Modules\BotStudio\Models\AgentKnowledgeFile;
use Stringable;

/**
 * AI tool that searches knowledge files scoped to a specific agent definition.
 *
 * Uses vector similarity search over model_embeddings filtered to the given
 * knowledge file IDs and organization. Returns results with source filenames
 * as citations.
 */
final class KnowledgeSearchTool implements Tool
{
    /**
     * @param  array<int, int>  $knowledgeFileIds
     */
    public function __construct(
        private array $knowledgeFileIds,
        private int $organizationId,
    ) {}

    public function description(): Stringable|string
    {
        return 'Search the agent\'s knowledge base using natural language. Returns the most relevant passages from uploaded documents with source citations.';
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The natural language search query to find relevant knowledge.'),
            'limit' => $schema->integer()->description('Maximum number of results to return. Defaults to 5.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $queryText = $request->get('query');

        if ($queryText === null || $queryText === '') {
            return 'A search query is required.';
        }

        if ($this->knowledgeFileIds === []) {
            return 'No knowledge files are available for this agent.';
        }

        $limit = (int) $request->get('limit', 5);

        $response = Embeddings::for([$queryText])->generate();

        /** @var array<int, float> $queryVector */
        $queryVector = $response->first();
        $vectorString = '['.implode(',', $queryVector).']';

        $selectExpression = "model_embeddings.*, 1 - (embedding <=> '".$vectorString."'::vector) as similarity_score";

        /** @var \Illuminate\Database\Eloquent\Collection<int, ModelEmbedding> $embeddings */
        $embeddings = ModelEmbedding::query()
            ->selectRaw($selectExpression) // @phpstan-ignore argument.type
            ->where('organization_id', $this->organizationId)
            ->where('embeddable_type', (new AgentKnowledgeFile)->getMorphClass())
            ->whereIn('embeddable_id', $this->knowledgeFileIds)
            ->orderByDesc('similarity_score')
            ->limit($limit)
            ->get();

        if ($embeddings->isEmpty()) {
            return 'No relevant knowledge found for: '.$queryText;
        }

        $fileNames = AgentKnowledgeFile::query()
            ->withoutGlobalScopes()
            ->whereIn('id', $this->knowledgeFileIds)
            ->pluck('filename', 'id');

        $output = "# Knowledge Search Results\n\n";

        foreach ($embeddings as $index => $embedding) {
            $number = $index + 1;
            $score = round((float) $embedding->getAttribute('similarity_score'), 4);
            $fileId = $embedding->embeddable_id;
            $filename = $fileNames[$fileId] ?? 'Unknown file';
            $chunkIndex = $embedding->chunk_index;

            $output .= "**{$number}. Source: {$filename}** (chunk {$chunkIndex}, similarity: {$score})\n";

            /** @var array<string, mixed>|null $metadata */
            $metadata = $embedding->metadata;

            if (is_array($metadata) && isset($metadata['content'])) {
                $content = mb_strlen((string) $metadata['content']) > 500
                    ? mb_substr((string) $metadata['content'], 0, 500).'...'
                    : (string) $metadata['content'];

                $output .= "{$content}\n";
            }

            $output .= "\n";
        }

        return $output;
    }
}
