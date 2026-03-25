<?php

declare(strict_types=1);

namespace Modules\BotStudio\Jobs;

use App\Models\ModelEmbedding;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Modules\BotStudio\Models\AgentKnowledgeFile;
use Modules\BotStudio\Services\DocumentChunker;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use RuntimeException;
use Spatie\PdfToText\Pdf;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class ProcessKnowledgeFileJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 300;

    public function __construct(public readonly AgentKnowledgeFile $knowledgeFile) {}

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new RateLimited)
                ->allow(30)
                ->everySeconds(60)
                ->releaseAfterSeconds(30),
        ];
    }

    public function handle(DocumentChunker $chunker): void
    {
        $this->knowledgeFile->update(['status' => 'processing']);

        $text = $this->extractText();

        $chunks = $chunker->chunk($text);

        if (count($chunks) === 0) {
            $this->knowledgeFile->update([
                'status' => 'indexed',
                'chunk_count' => 0,
                'processed_at' => now(),
            ]);

            return;
        }

        // Delete existing embeddings for re-processing
        ModelEmbedding::query()
            ->where('embeddable_type', AgentKnowledgeFile::class)
            ->where('embeddable_id', $this->knowledgeFile->id)
            ->delete();

        foreach ($chunks as $chunk) {
            $response = Embeddings::for([$chunk['text']])->generate();
            $vector = $response->first();

            ModelEmbedding::query()->create([
                'organization_id' => $this->knowledgeFile->organization_id,
                'embeddable_type' => AgentKnowledgeFile::class,
                'embeddable_id' => $this->knowledgeFile->id,
                'chunk_index' => $chunk['chunk_index'],
                'embedding' => $vector,
                'content_hash' => md5($chunk['text']),
                'metadata' => ['content' => $chunk['text']],
            ]);
        }

        $this->knowledgeFile->update([
            'status' => 'indexed',
            'chunk_count' => count($chunks),
            'processed_at' => now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ProcessKnowledgeFileJob failed', [
            'knowledge_file_id' => $this->knowledgeFile->id,
            'error' => $exception->getMessage(),
        ]);

        $this->knowledgeFile->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }

    private function extractText(): string
    {
        $media = $this->knowledgeFile->getFirstMedia('knowledge');

        if ($media === null) {
            throw new RuntimeException('No media file attached to knowledge file record.');
        }

        $path = $media->getPath();

        return match ($this->knowledgeFile->mime_type) {
            'application/pdf' => Pdf::getText($path),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => $this->extractDocx($path),
            'text/plain', 'text/csv', 'text/markdown' => (string) file_get_contents($path),
            default => throw new RuntimeException("Unsupported mime type: {$this->knowledgeFile->mime_type}"),
        };
    }

    private function extractDocx(string $path): string
    {
        $phpWord = IOFactory::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof Text) {
                    $text .= $element->getText()."\n";
                } elseif ($element instanceof TextRun) {
                    foreach ($element->getElements() as $child) {
                        if ($child instanceof Text) {
                            $text .= $child->getText();
                        }
                    }
                    $text .= "\n";
                }
            }
        }

        return $text;
    }
}
