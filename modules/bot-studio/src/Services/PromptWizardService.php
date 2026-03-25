<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

/**
 * Pure PHP service that generates a system prompt from structured wizard answers.
 *
 * No AI API calls — template-based generation only.
 */
final class PromptWizardService
{
    /**
     * Map of wizard tone values to human-readable descriptors.
     *
     * @var array<string, string>
     */
    private const TONE_MAP = [
        'professional' => 'professional',
        'friendly' => 'friendly and approachable',
        'casual' => 'casual and conversational',
        'technical' => 'precise and technical',
        'empathetic' => 'empathetic and understanding',
    ];

    /**
     * Generate a system prompt from structured wizard answers.
     *
     * @param  array{role?: string, tone?: string, expertise?: string, restrictions?: string}  $answers
     */
    public function generate(array $answers): string
    {
        $role = mb_trim($answers['role'] ?? '');
        $tone = mb_trim($answers['tone'] ?? '');
        $expertise = mb_trim($answers['expertise'] ?? '');
        $restrictions = mb_trim($answers['restrictions'] ?? '');

        $toneLabel = self::TONE_MAP[$tone] ?? $tone;

        $lines = [];

        // Opening identity line
        if ($role !== '') {
            $identity = $toneLabel !== ''
                ? "You are a {$toneLabel} {$role} for {{org_name}}."
                : "You are a {$role} for {{org_name}}.";
        } else {
            $identity = 'You are an assistant for {{org_name}}.';
        }

        $lines[] = $identity;

        // Expertise section
        if ($expertise !== '') {
            $lines[] = '';
            $lines[] = "Your expertise covers: {$expertise}.";
        }

        // User greeting variable
        $lines[] = '';
        $lines[] = 'When greeting users, address them as {{user_name}}.';

        // Restrictions section
        if ($restrictions !== '') {
            $lines[] = '';
            $lines[] = 'Important restrictions:';

            foreach ($this->parseRestrictions($restrictions) as $item) {
                $lines[] = "- {$item}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Split a restrictions string into individual bullet items.
     *
     * Supports period-separated sentences or newline-separated lines.
     *
     * @return list<string>
     */
    private function parseRestrictions(string $restrictions): array
    {
        // Try splitting on ". " first (period-separated sentences)
        if (str_contains($restrictions, '. ')) {
            $items = array_filter(
                array_map('trim', explode('. ', $restrictions)),
                fn (string $item): bool => $item !== '',
            );

            return array_map(
                fn (string $item): string => mb_rtrim($item, '.'),
                array_values($items),
            );
        }

        // Fall back to newline-separated
        $items = array_filter(
            array_map('trim', explode("\n", $restrictions)),
            fn (string $item): bool => $item !== '',
        );

        return array_map(
            fn (string $item): string => mb_ltrim(mb_rtrim($item, '.'), '-• '),
            array_values($items),
        );
    }
}
