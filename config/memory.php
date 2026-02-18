<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Vector Dimensions
    |--------------------------------------------------------------------------
    |
    | The number of dimensions for the embedding vectors. This should match
    | the output dimensions of your configured embedding model. Common values:
    | - 1536 (OpenAI text-embedding-3-small, text-embedding-ada-002)
    | - 3072 (OpenAI text-embedding-3-large)
    | - 1024 (Cohere embed-english-v3.0)
    |
    */

    'dimensions' => env('MEMORY_DIMENSIONS', 1536),

    /*
    |--------------------------------------------------------------------------
    | Similarity Threshold
    |--------------------------------------------------------------------------
    |
    | The minimum cosine similarity score (0.0 to 1.0) required for a memory
    | to be considered relevant during recall. Lower values return more results
    | but may include less relevant memories. Higher values are stricter.
    |
    */

    'similarity_threshold' => env('MEMORY_SIMILARITY_THRESHOLD', 0.5),

    /*
    |--------------------------------------------------------------------------
    | Default Recall Limit
    |--------------------------------------------------------------------------
    |
    | The default maximum number of memories returned by the recall method.
    | This can be overridden per-call via the $limit parameter.
    |
    */

    'recall_limit' => env('MEMORY_RECALL_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | Middleware Recall Limit
    |--------------------------------------------------------------------------
    |
    | The default number of memories injected into agent prompts by the
    | WithMemory middleware. Keep this relatively low to avoid consuming
    | too much of the context window.
    |
    */

    'middleware_recall_limit' => env('MEMORY_MIDDLEWARE_RECALL_LIMIT', 5),

    /*
    |--------------------------------------------------------------------------
    | Recall Oversample Factor
    |--------------------------------------------------------------------------
    |
    | When recalling memories, we first retrieve (limit × factor) candidates
    | via vector similarity search, then rerank them to return the top N.
    | Higher values improve reranking quality at the cost of more DB reads.
    |
    */

    'recall_oversample_factor' => env('MEMORY_RECALL_OVERSAMPLE_FACTOR', 2),

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | The database table name used to store memories. Change this if you need
    | to avoid conflicts with existing tables in your application.
    |
    */

    'table' => env('MEMORY_TABLE', 'memories'),

];
