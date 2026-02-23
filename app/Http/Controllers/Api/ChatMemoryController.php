<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Eznix86\AI\Memory\Facades\AgentMemory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ChatMemoryController
{
    /**
     * Return the current user's AI memories (read-only) for the chat UI.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $limit = max(1, min(50, (int) $request->get('limit', 20)));
        $memories = AgentMemory::all(['user_id' => $user->id], limit: $limit);

        $data = $memories->values()->map(fn ($m): array => [
            'id' => $m->id,
            'content' => $m->content ?? '',
        ])->all();

        return response()->json(['data' => $data]);
    }
}
