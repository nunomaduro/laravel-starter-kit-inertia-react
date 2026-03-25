<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Modules\BotStudio\Jobs\ProcessKnowledgeFileJob;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;

final class KnowledgeProcessor
{
    /** @var array<int, string> */
    private const array ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'text/csv',
        'text/markdown',
    ];

    public function upload(AgentDefinition $definition, UploadedFile $file): AgentKnowledgeFile
    {
        $this->validateMimeType($file);
        $this->validateFileSize($file);
        $this->validateTotalSize($definition, $file);

        /** @var AgentKnowledgeFile $knowledgeFile */
        $knowledgeFile = $definition->knowledgeFiles()->create([
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
            'chunk_count' => 0,
        ]);

        $knowledgeFile->addMedia($file)->toMediaCollection('knowledge');

        ProcessKnowledgeFileJob::dispatch($knowledgeFile);

        return $knowledgeFile->refresh();
    }

    public function delete(AgentKnowledgeFile $file): void
    {
        $file->delete();
    }

    private function validateMimeType(UploadedFile $file): void
    {
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw ValidationException::withMessages([
                'file' => ["Unsupported file type: {$mimeType}. Allowed: PDF, DOCX, TXT, CSV, MD."],
            ]);
        }
    }

    private function validateFileSize(UploadedFile $file): void
    {
        $maxMb = (int) config('bot-studio.max_knowledge_file_size_mb', 10);
        $maxBytes = $maxMb * 1024 * 1024;

        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => ["File exceeds maximum size of {$maxMb} MB."],
            ]);
        }
    }

    private function validateTotalSize(AgentDefinition $definition, UploadedFile $file): void
    {
        $maxTotalMb = (int) config('bot-studio.max_knowledge_total_mb', 100);
        $maxTotalBytes = $maxTotalMb * 1024 * 1024;

        $currentTotalBytes = (int) $definition->knowledgeFiles()->sum('file_size');
        $newTotal = $currentTotalBytes + $file->getSize();

        if ($newTotal > $maxTotalBytes) {
            throw ValidationException::withMessages([
                'file' => ["Adding this file would exceed the {$maxTotalMb} MB knowledge base limit."],
            ]);
        }
    }
}
