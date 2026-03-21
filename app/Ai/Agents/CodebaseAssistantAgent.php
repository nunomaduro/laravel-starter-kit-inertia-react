<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\ListActionsAiTool;
use App\Ai\Tools\ListModelsAiTool;
use App\Ai\Tools\ListRoutesAiTool;
use App\Ai\Tools\UsersIndexAiTool;
use App\Modules\Support\AIContextAggregator;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

/**
 * AI assistant that understands the project's codebase.
 *
 * Can answer questions about models, routes, and actions using read-only tools.
 * Code suggestions are presented as descriptions — never auto-applied.
 *
 * This is the MVP Codebase Assistant from the design doc (Phase 3a).
 */
final class CodebaseAssistantAgent implements Agent, HasTools
{
    use Promptable;

    public function __construct(
        private readonly AIContextAggregator $contextAggregator,
    ) {}

    public function instructions(): string
    {
        $domainContext = $this->contextAggregator->systemPrompt();

        return <<<INSTRUCTIONS
        You are a codebase assistant for a Laravel application. You help developers understand
        and work with the project's models, routes, actions, and modules.

        IMPORTANT RULES:
        - You are READ-ONLY. You can query models, routes, and actions but NEVER modify files.
        - When suggesting code changes, describe them as instructions, not executable code.
        - Always scope data queries to the current organization (multi-tenant).
        - Never expose sensitive data (passwords, API keys, salaries).

        Use your tools to answer questions:
        - list_models: Find Eloquent models and their tables
        - list_routes: Find application routes and controllers
        - list_actions: Find Action classes and their signatures
        - users_index: Query user data

        {$domainContext}
        INSTRUCTIONS;
    }

    public function tools(): iterable
    {
        return [
            new ListModelsAiTool,
            new ListRoutesAiTool,
            new ListActionsAiTool,
            new UsersIndexAiTool,
        ];
    }
}
