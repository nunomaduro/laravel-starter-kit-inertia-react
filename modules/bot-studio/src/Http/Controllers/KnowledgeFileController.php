<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;

final class KnowledgeFileController
{
    /**
     * Accept a file upload for knowledge processing (stub — full implementation in Task 8).
     */
    public function store(Request $request, AgentDefinition $agentDefinition): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,txt,md,csv,docx'],
        ]);

        return response()->json([
            'message' => 'Knowledge processing coming in next update.',
        ], 501);
    }

    /**
     * Delete a knowledge file record.
     */
    public function destroy(AgentDefinition $agentDefinition, AgentKnowledgeFile $knowledgeFile): Response
    {
        abort_if($knowledgeFile->agent_definition_id !== $agentDefinition->id, 404);

        $knowledgeFile->delete();

        return response()->noContent();
    }

    /**
     * Retry processing a knowledge file (stub — full implementation in Task 8).
     */
    public function retry(AgentDefinition $agentDefinition, AgentKnowledgeFile $knowledgeFile): JsonResponse
    {
        abort_if($knowledgeFile->agent_definition_id !== $agentDefinition->id, 404);

        return response()->json([
            'message' => 'Knowledge processing coming in next update.',
        ], 501);
    }
}
