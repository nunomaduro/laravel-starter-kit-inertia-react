<?php

declare(strict_types=1);

namespace App\Actions;

use App\Ai\Agents\ThemeSuggestionAgent;
use Illuminate\Http\UploadedFile;
use Laravel\Ai\Files\Image;
use Throwable;

final class SuggestThemeFromLogo
{
    /**
     * @return array{dark: string, primary: string, light: string, skin: string, radius: string, font: string, menuColor: string, menuAccent: string, reason: string, ai_derived: bool}
     */
    public function handle(UploadedFile $file, ?string $colorHint = null): array
    {
        if (empty(config('prism.providers.openrouter.api_key'))) {
            return $this->fallback($colorHint);
        }

        $hintNote = $colorHint !== null
            ? sprintf(" The dominant detected brand color is '%s' — use this as primary unless your visual analysis clearly indicates another.", $colorHint)
            : '';

        $prompt = 'Analyze this logo image carefully and suggest the optimal complete theme configuration that best matches the logo.'.$hintNote;

        try {
            $response = (new ThemeSuggestionAgent)->prompt(
                prompt: $prompt,
                attachments: [Image::fromUpload($file)],
            );

            return [...$response->structured, 'ai_derived' => true];
        } catch (Throwable) {
            return $this->fallback($colorHint);
        }
    }

    /**
     * @return array{dark: string, primary: string, light: string, skin: string, radius: string, font: string, menuColor: string, menuAccent: string, reason: string, ai_derived: bool}
     */
    private function fallback(?string $colorHint): array
    {
        $validColors = ['indigo', 'blue', 'green', 'amber', 'purple', 'rose'];
        $primary = ($colorHint !== null && in_array($colorHint, $validColors, true)) ? $colorHint : 'indigo';

        return [
            'dark' => 'navy',
            'primary' => $primary,
            'light' => 'slate',
            'skin' => 'shadow',
            'radius' => 'default',
            'font' => 'inter',
            'menuColor' => 'default',
            'menuAccent' => 'subtle',
            'reason' => 'Color extracted from your logo.',
            'ai_derived' => false,
        ];
    }
}
