<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $conversations = AgentConversation::query()
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $conversations->getCollection()->map(fn (AgentConversation $c): array => [
                'id' => $c->id,
                'title' => $c->title,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
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

        $conversation = AgentConversation::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $messages = $conversation->messages()
            ->orderBy('id')
            ->get()
            ->map(fn (AgentConversationMessage $m): array => [
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

        $conversation = AgentConversation::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $conversation->update(['title' => $request->input('title')]);

        return response()->json(['data' => ['id' => $id, 'title' => $conversation->title]]);
    }

    /**
     * Delete a conversation and its messages for the authenticated user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $conversation = AgentConversation::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json(null, 204);
    }
}
