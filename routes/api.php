<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatMemoryController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): JsonResponse => response()->json([
    'name' => config('app.name'),
    'version' => config('scramble.info.version'),
    'message' => 'API documentation is at /docs/api. Versioned API base is /api/v1.',
]))->name('api');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('chat', ChatController::class)->name('api.chat');
    Route::get('chat/memories', ChatMemoryController::class)->name('chat.memories');
    Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('conversations/{id}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::patch('conversations/{id}', [ConversationController::class, 'update'])->name('conversations.update');
    Route::delete('conversations/{id}', [ConversationController::class, 'destroy'])->name('conversations.destroy');
});

Route::prefix('v1')->name('api.v1.')->middleware('throttle:60,1')->group(function (): void {
    Route::get('/', fn (): JsonResponse => response()->json([
        'name' => config('app.name'),
        'version' => config('scramble.info.version'),
        'message' => 'API documentation is available at /docs/api',
    ]))->name('info');

    Route::middleware(['auth:sanctum', 'feature:api_access'])->group(function (): void {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/batch', [UserController::class, 'batch'])->name('users.batch');
        Route::post('users/search', [UserController::class, 'search'])->name('users.search');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
