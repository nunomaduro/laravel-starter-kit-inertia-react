<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\BotStudio\Http\Controllers\AgentChatController;
use Modules\BotStudio\Http\Controllers\AgentDefinitionController;
use Modules\BotStudio\Http\Controllers\KnowledgeFileController;

Route::middleware(['auth', 'verified', 'tenant', 'feature:bot-studio'])->prefix('bot-studio')->name('bot-studio.')->group(function (): void {

    // Agent CRUD
    Route::get('/', [AgentDefinitionController::class, 'index'])->name('index');
    Route::get('/templates', [AgentDefinitionController::class, 'templates'])->name('templates');
    Route::get('/create', [AgentDefinitionController::class, 'create'])->name('create');
    Route::post('/', [AgentDefinitionController::class, 'store'])->name('store');
    Route::get('/{agentDefinition:slug}/edit', [AgentDefinitionController::class, 'edit'])->name('edit');
    Route::put('/{agentDefinition:slug}', [AgentDefinitionController::class, 'update'])->name('update');
    Route::delete('/{agentDefinition:slug}', [AgentDefinitionController::class, 'destroy'])->name('destroy');
    Route::post('/{agentDefinition:slug}/duplicate', [AgentDefinitionController::class, 'duplicate'])->name('duplicate');

    // Chat & Preview
    Route::post('/{agentDefinition:slug}/chat', [AgentChatController::class, 'stream'])->name('chat');
    Route::post('/{agentDefinition:slug}/preview', [AgentChatController::class, 'preview'])->name('preview');
    Route::get('/{agentDefinition:slug}/conversations', [AgentChatController::class, 'conversations'])->name('conversations');

    // Knowledge Files
    Route::post('/{agentDefinition:slug}/knowledge', [KnowledgeFileController::class, 'store'])->name('knowledge.store');
    Route::delete('/{agentDefinition:slug}/knowledge/{knowledgeFile}', [KnowledgeFileController::class, 'destroy'])->name('knowledge.destroy');
    Route::post('/{agentDefinition:slug}/knowledge/{knowledgeFile}/retry', [KnowledgeFileController::class, 'retry'])->name('knowledge.retry');
});
