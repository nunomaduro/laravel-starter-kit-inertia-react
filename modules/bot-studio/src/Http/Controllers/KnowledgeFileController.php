<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use App\Settings\BotStudioSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Modules\BotStudio\Jobs\ProcessKnowledgeFileJob;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;
use Modules\BotStudio\Services\KnowledgeProcessor;

final readonly class KnowledgeFileController
{
    public function __construct(
        private KnowledgeProcessor $processor,
        private BotStudioSettings $settings,
    ) {}

    /**
     * Accept a file upload for knowledge processing.
     */
    public function store(Request $request, AgentDefinition $agentDefinition): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $maxFileSizeBytes = $this->settings->max_knowledge_file_size_mb * 1024 * 1024;

        if ($file->getSize() > $maxFileSizeBytes) {
            throw ValidationException::withMessages([
                'file' => [__('The file exceeds the maximum allowed size of :mb MB.', [
                    'mb' => $this->settings->max_knowledge_file_size_mb,
                ])],
            ]);
        }

        $maxTotalBytes = $this->settings->max_knowledge_total_mb * 1024 * 1024;
        $currentTotalBytes = AgentKnowledgeFile::query()
            ->where('agent_definition_id', $agentDefinition->id)
            ->sum('file_size');

        if (($currentTotalBytes + $file->getSize()) > $maxTotalBytes) {
            throw ValidationException::withMessages([
                'file' => [__('Uploading this file would exceed the total knowledge base limit of :mb MB.', [
                    'mb' => $this->settings->max_knowledge_total_mb,
                ])],
            ]);
        }

        $knowledgeFile = $this->processor->upload(
            $agentDefinition,
            $file,
        );

        return response()->json([
            'knowledge_file' => $knowledgeFile,
        ], 201);
    }

    /**
     * Delete a knowledge file record.
     */
    public function destroy(AgentDefinition $agentDefinition, AgentKnowledgeFile $knowledgeFile): Response
    {
        abort_if($knowledgeFile->agent_definition_id !== $agentDefinition->id, 404);

        $this->processor->delete($knowledgeFile);

        return response()->noContent();
    }

    /**
     * Retry processing a failed knowledge file.
     */
    public function retry(AgentDefinition $agentDefinition, AgentKnowledgeFile $knowledgeFile): JsonResponse
    {
        abort_if($knowledgeFile->agent_definition_id !== $agentDefinition->id, 404);

        $knowledgeFile->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        ProcessKnowledgeFileJob::dispatch($knowledgeFile);

        return response()->json([
            'knowledge_file' => $knowledgeFile->fresh(),
        ]);
    }
}
