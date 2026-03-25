<?php

declare(strict_types=1);

namespace Modules\BotStudio\Contracts;

interface ProvidesAgentTemplates
{
    /**
     * @return array<int, array{
     *     name: string,
     *     description: string,
     *     system_prompt: string,
     *     wizard_answers: array<string, mixed>,
     *     conversation_starters: array<int, string>,
     *     enabled_tools: array<int, string>,
     *     model: string,
     *     temperature: float,
     * }>
     */
    public function agentTemplates(): array;
}
