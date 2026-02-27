<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ConversationController extends Controller
{
    /**
     * List conversations for the authenticated user.
     *
     * @return JsonResponse{data: array{id: string, title: string, created_at: string, updated_at: string}}
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = max(1, min(50, (int) $request->get('per_page', 20)));

        $conversations = DB::table('agent_conversations')
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $conversations->getCollection()->map(fn ($row): array => [
                'id' => $row->id,
                'title' => $row->title,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ])->all(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Get one conversation and its messages for the authenticated user.
     *
     * @return JsonResponse{data: array{id: string, title: string, created_at: string, updated_at: string, messages: array}}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $conversation = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $messages = DB::table('agent_conversation_messages')
            ->where('conversation_id', $id)
            ->orderBy('id')
            ->get()
            ->map(fn ($m): array => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content ?? '',
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at,
                'updated_at' => $conversation->updated_at,
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Rename a conversation for the authenticated user.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate(['title' => ['required', 'string', 'max:255']]);
        $user = $request->user();

        $updated = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->update(['title' => $request->input('title'), 'updated_at' => now()]);

        if (! $updated) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        return response()->json(['data' => ['id' => $id, 'title' => $request->input('title')]]);
    }

    /**
     * Delete a conversation and its messages for the authenticated user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $deleted = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        DB::table('agent_conversation_messages')
            ->where('conversation_id', $id)
            ->delete();

        return response()->json(null, 204);
    }
}
