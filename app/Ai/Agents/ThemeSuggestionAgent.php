<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Provider('openrouter')]
#[Model('google/gemini-2.0-flash-001')]
final class ThemeSuggestionAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a professional UI/UX designer. Analyze logo images carefully. '
            .'Look at the dominant brand colors, style, and personality. '
            .'Suggest the optimal complete theme configuration that best matches the logo. '
            .'Choose primary color to closely match the logo\'s main brand color. '
            .'Choose dark/light schemes that complement it. '
            .'For radius: use lg/full for modern/playful logos, none/sm for corporate/technical ones. '
            .'Choose fonts that match brand personality. Keep reason under 100 characters.';
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'dark' => $schema->string()->enum(['navy', 'mirage', 'mint', 'black', 'cinder'])->required(),
            'primary' => $schema->string()->enum(['indigo', 'blue', 'green', 'amber', 'purple', 'rose'])->required(),
            'light' => $schema->string()->enum(['slate', 'gray', 'neutral'])->required(),
            'skin' => $schema->string()->enum(['shadow', 'bordered', 'flat', 'elevated'])->required(),
            'radius' => $schema->string()->enum(['none', 'sm', 'default', 'md', 'lg', 'full'])->required(),
            'font' => $schema->string()->enum(['ibm-plex-sans', 'inter', 'geist-sans', 'instrument-sans', 'poppins', 'outfit', 'plus-jakarta-sans'])->required(),
            'menuColor' => $schema->string()->enum(['default', 'primary', 'muted'])->required(),
            'menuAccent' => $schema->string()->enum(['subtle', 'strong', 'bordered'])->required(),
            'reason' => $schema->string()->required(),
        ];
    }
}
