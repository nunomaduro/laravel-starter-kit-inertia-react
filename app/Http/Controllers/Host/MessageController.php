<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class MessageController
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $conversations = Conversation::query()
            ->where('host_id', $user->id)
            ->with(['property', 'guest', 'host', 'latestMessage'])
            ->latest()
            ->paginate(20);

        return Inertia::render('host/messages/index', [
            'conversations' => $conversations,
        ]);
    }
}
