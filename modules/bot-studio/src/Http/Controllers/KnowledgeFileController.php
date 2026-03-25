<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\BotStudio\Jobs\ProcessKnowledgeFileJob;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;
use Modules\BotStudio\Services\KnowledgeProcessor;

final readonly class KnowledgeFileController
{
    public function __construct(private KnowledgeProcessor $processor) {}

    /**
     * Accept a file upload for knowledge processing.
     */
    public function store(Request $request, AgentDefinition $agentDefinition): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $knowledgeFile = $this->processor->upload(
            $agentDefinition,
            $request->file('file'),
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
