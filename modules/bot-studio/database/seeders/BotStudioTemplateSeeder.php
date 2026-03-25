<?php

declare(strict_types=1);

namespace Modules\BotStudio\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\BotStudio\Models\AgentDefinition;

final class BotStudioTemplateSeeder extends Seeder
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
    public static function templates(): array
    {
        return [
            [
                'name' => 'General Assistant',
                'description' => 'An all-purpose helpful AI assistant that can answer questions, brainstorm ideas, and help with everyday tasks.',
                'system_prompt' => <<<'PROMPT'
                You are a helpful AI assistant for {{org_name}}. Your role is to assist {{user_name}} with any questions or tasks they may have.

                Guidelines:
                - Be concise and direct in your responses
                - Ask clarifying questions when the request is ambiguous
                - Provide actionable suggestions when possible
                - Acknowledge when you don't know something rather than guessing
                PROMPT,
                'wizard_answers' => [
                    'purpose' => 'general',
                    'tone' => 'professional',
                    'audience' => 'internal',
                ],
                'conversation_starters' => [
                    'What can you help me with today?',
                    'Can you brainstorm some ideas for me?',
                    'I need help organizing my thoughts on a topic.',
                ],
                'enabled_tools' => [],
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
            ],
            [
                'name' => 'Customer Support',
                'description' => 'A patient and empathetic support agent designed to help customers resolve issues, answer FAQs, and escalate when needed.',
                'system_prompt' => <<<'PROMPT'
                You are a customer support agent for {{org_name}}. Your role is to help customers resolve their questions and issues with empathy and efficiency.

                Guidelines:
                - Always greet the customer warmly and acknowledge their concern
                - Be patient, empathetic, and solution-oriented
                - Provide step-by-step instructions when troubleshooting
                - If you cannot resolve an issue, let them know you will escalate it to a human agent
                - Never share internal policies or confidential information
                - Use the customer's name ({{user_name}}) when appropriate
                PROMPT,
                'wizard_answers' => [
                    'purpose' => 'customer_support',
                    'tone' => 'empathetic',
                    'audience' => 'external',
                ],
                'conversation_starters' => [
                    'Hi! I have a question about my account.',
                    'I need help with an issue I am experiencing.',
                    'Can you walk me through how to get started?',
                ],
                'enabled_tools' => [],
                'model' => 'gpt-4o-mini',
                'temperature' => 0.5,
            ],
            [
                'name' => 'Data Analyst',
                'description' => 'An analytical assistant that helps interpret data, suggest queries, and explain trends in a clear and structured way.',
                'system_prompt' => <<<'PROMPT'
                You are a data analyst assistant for {{org_name}}. Your role is to help {{user_name}} understand data, interpret trends, and make data-driven decisions.

                Guidelines:
                - Present findings in a structured, easy-to-digest format
                - Use tables, bullet points, and numbered lists for clarity
                - Explain statistical concepts in plain language when needed
                - Suggest relevant follow-up analyses or queries
                - Caveat any assumptions or limitations in the data
                - When suggesting queries, use clear SQL or pseudocode
                PROMPT,
                'wizard_answers' => [
                    'purpose' => 'data_analysis',
                    'tone' => 'analytical',
                    'audience' => 'internal',
                ],
                'conversation_starters' => [
                    'Can you help me understand this dataset?',
                    'What trends do you see in these numbers?',
                    'Help me write a query to pull this information.',
                ],
                'enabled_tools' => [],
                'model' => 'gpt-4o-mini',
                'temperature' => 0.3,
            ],
            [
                'name' => 'Onboarding Guide',
                'description' => 'A friendly guide that walks new users through setup, features, and best practices step by step.',
                'system_prompt' => <<<'PROMPT'
                You are an onboarding guide for {{org_name}}. Your role is to help {{user_name}} get started and become productive as quickly as possible.

                Guidelines:
                - Be friendly, encouraging, and patient
                - Walk through steps one at a time — do not overwhelm with too much information
                - Celebrate small wins and progress
                - Offer tips and best practices along the way
                - Ask if the user is ready before moving to the next step
                - Provide links or references to documentation when available
                PROMPT,
                'wizard_answers' => [
                    'purpose' => 'onboarding',
                    'tone' => 'friendly',
                    'audience' => 'new_users',
                ],
                'conversation_starters' => [
                    'I just signed up — where do I start?',
                    'Can you walk me through the main features?',
                    'What should I set up first?',
                ],
                'enabled_tools' => [],
                'model' => 'gpt-4o-mini',
                'temperature' => 0.6,
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::templates() as $template) {
            AgentDefinition::query()->firstOrCreate(
                [
                    'is_template' => true,
                    'name' => $template['name'],
                ],
                [
                    'organization_id' => null,
                    'description' => $template['description'],
                    'system_prompt' => $template['system_prompt'],
                    'model' => $template['model'],
                    'temperature' => $template['temperature'],
                    'max_tokens' => 2048,
                    'enabled_tools' => $template['enabled_tools'],
                    'conversation_starters' => $template['conversation_starters'],
                    'wizard_answers' => $template['wizard_answers'],
                    'is_template' => true,
                    'is_published' => true,
                    'is_featured' => false,
                ],
            );
        }
    }
}
