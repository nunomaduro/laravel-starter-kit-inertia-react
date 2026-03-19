<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class IndexNotificationsController extends Controller
{
    public function __invoke(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $request->user()
                    ->notifications()
                    ->latest()
                    ->limit(20)
                    ->get(),
            ]);
        }

        return Inertia::render('notifications/index', [
            'notificationsList' => $request->user()
                ->notifications()
                ->latest()
                ->paginate(20),
        ]);
    }
}
