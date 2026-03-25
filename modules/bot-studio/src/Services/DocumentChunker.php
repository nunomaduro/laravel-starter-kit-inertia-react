<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

final class DocumentChunker
{
    /** @var int Words per chunk (tokens × 0.75) */
    private readonly int $chunkWords;

    /** @var int Overlap in words */
    private readonly int $overlapWords;

    public function __construct(
        private readonly int $chunkSize = 500,
        private readonly int $overlap = 50,
    ) {
        $this->chunkWords = (int) round($chunkSize * 0.75);
        $this->overlapWords = (int) round($overlap * 0.75);
    }

    /**
     * Split text into overlapping chunks.
     *
     * @return array<int, array{text: string, chunk_index: int}>
     */
    public function chunk(string $text): array
    {
        $text = mb_trim($text);

        if ($text === '') {
            return [];
        }

        $words = preg_split('/\s+/', $text);

        if ($words === false || count($words) === 0) {
            return [];
        }

        $totalWords = count($words);

        if ($totalWords <= $this->chunkWords) {
            return [
                [
                    'text' => implode(' ', $words),
                    'chunk_index' => 0,
                ],
            ];
        }

        $chunks = [];
        $chunkIndex = 0;
        $position = 0;
        $step = max(1, $this->chunkWords - $this->overlapWords);

        while ($position < $totalWords) {
            $slice = array_slice($words, $position, $this->chunkWords);

            $chunks[] = [
                'text' => implode(' ', $slice),
                'chunk_index' => $chunkIndex,
            ];

            $chunkIndex++;
            $position += $step;
        }

        return $chunks;
    }
}
