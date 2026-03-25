<?php

declare(strict_types=1);

use Modules\BotStudio\Services\DocumentChunker;

it('returns a single chunk for short text', function (): void {
    $chunker = new DocumentChunker(chunkSize: 500, overlap: 50);
    $text = 'Hello world this is a short sentence.';

    $chunks = $chunker->chunk($text);

    expect($chunks)->toHaveCount(1)
        ->and($chunks[0]['text'])->toBe($text)
        ->and($chunks[0]['chunk_index'])->toBe(0);
});

it('returns empty array for empty text', function (): void {
    $chunker = new DocumentChunker();

    expect($chunker->chunk(''))->toBeEmpty()
        ->and($chunker->chunk('   '))->toBeEmpty();
});

it('splits long text into multiple chunks', function (): void {
    $chunker = new DocumentChunker(chunkSize: 10, overlap: 2);
    // 10 tokens × 0.75 = 7.5 → 8 words per chunk; overlap = 2 × 0.75 = 1.5 → 2 words
    $words = array_fill(0, 30, 'word');
    $text = implode(' ', $words);

    $chunks = $chunker->chunk($text);

    expect(count($chunks))->toBeGreaterThan(1);
});

it('produces overlapping content between consecutive chunks', function (): void {
    $chunker = new DocumentChunker(chunkSize: 10, overlap: 4);
    // chunkWords = round(10 × 0.75) = 8, overlapWords = round(4 × 0.75) = 3, step = 8-3 = 5
    $words = [];
    for ($i = 1; $i <= 40; $i++) {
        $words[] = "word{$i}";
    }
    $text = implode(' ', $words);

    $chunks = $chunker->chunk($text);

    expect(count($chunks))->toBeGreaterThan(1);

    // Verify overlap: last words of chunk[0] appear at start of chunk[1]
    $chunk0Words = explode(' ', $chunks[0]['text']);
    $chunk1Words = explode(' ', $chunks[1]['text']);

    $overlapWords = array_intersect($chunk0Words, $chunk1Words);
    expect($overlapWords)->not->toBeEmpty();
});

it('produces sequential chunk indices starting from 0', function (): void {
    $chunker = new DocumentChunker(chunkSize: 10, overlap: 2);
    $words = array_fill(0, 50, 'word');
    $text = implode(' ', $words);

    $chunks = $chunker->chunk($text);

    foreach ($chunks as $i => $chunk) {
        expect($chunk['chunk_index'])->toBe($i);
    }
});

it('produces correct number of chunks for a very long text', function (): void {
    // chunkWords = round(10 × 0.75) = 8, overlapWords = round(2 × 0.75) = 2, step = 6
    $chunker = new DocumentChunker(chunkSize: 10, overlap: 2);

    $totalWords = 48; // 48 words: positions 0,6,12,18,24,30,36,42 → 8 chunks
    $words = array_fill(0, $totalWords, 'word');
    $text = implode(' ', $words);

    $chunkWords = (int) round(10 * 0.75); // 8
    $overlapWords = (int) round(2 * 0.75); // 2
    $step = $chunkWords - $overlapWords;   // 6

    $expectedChunks = 0;
    for ($pos = 0; $pos < $totalWords; $pos += $step) {
        $expectedChunks++;
    }

    $chunks = $chunker->chunk($text);

    expect($chunks)->toHaveCount($expectedChunks);
});
