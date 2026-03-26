<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\BotStudio\Models\AgentDefinition;

final class EmbedController
{
    /**
     * Update the embed theme and settings for an agent definition.
     */
    public function updateTheme(Request $request, AgentDefinition $agentDefinition): JsonResponse
    {
        $validated = $request->validate([
            'embed_enabled' => ['sometimes', 'boolean'],
            'embed_theme' => ['sometimes', 'array'],
            'embed_theme.primary_color' => ['sometimes', 'string', 'max:50'],
            'embed_theme.position' => ['sometimes', 'string', 'in:bottom-right,bottom-left'],
            'embed_theme.greeting' => ['sometimes', 'string', 'max:500'],
            'embed_theme.placeholder' => ['sometimes', 'string', 'max:200'],
            'embed_theme.show_powered_by' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['embed_enabled'])) {
            $agentDefinition->embed_enabled = $validated['embed_enabled'];
        }

        if (isset($validated['embed_theme'])) {
            $currentTheme = $agentDefinition->embed_theme ?? [];
            $agentDefinition->embed_theme = array_merge($currentTheme, $validated['embed_theme']);
        }

        $agentDefinition->save();

        return response()->json([
            'definition' => $agentDefinition->only(['embed_enabled', 'embed_theme']),
        ]);
    }
}
