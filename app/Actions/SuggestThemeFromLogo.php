<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Prism\Prism\Enums\Provider as PrismProvider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

final class SuggestThemeFromLogo
{
    /**
     * @return array{dark: string, primary: string, light: string, skin: string, radius: string, font: string, menuColor: string, menuAccent: string, reason: string, ai_derived: bool}
     */
    public function handle(UploadedFile $file, ?string $colorHint = null): array
    {
        $schema = new ObjectSchema(
            name: 'theme_suggestion',
            description: 'Optimal theme configuration based on the provided logo',
            properties: [
                new EnumSchema('dark', 'Dark color scheme', ['navy', 'mirage', 'mint', 'black', 'cinder']),
                new EnumSchema('primary', 'Primary accent color', ['indigo', 'blue', 'green', 'amber', 'purple', 'rose']),
                new EnumSchema('light', 'Light color scheme', ['slate', 'gray', 'neutral']),
                new EnumSchema('skin', 'Card skin style', ['shadow', 'bordered', 'flat', 'elevated']),
                new EnumSchema('radius', 'Border radius', ['none', 'sm', 'default', 'md', 'lg', 'full']),
                new EnumSchema('font', 'Font family', ['inter', 'geist-sans', 'instrument-sans', 'poppins', 'outfit', 'plus-jakarta-sans']),
                new EnumSchema('menuColor', 'Menu color scheme', ['default', 'primary', 'muted']),
                new EnumSchema('menuAccent', 'Menu accent style', ['subtle', 'strong', 'bordered']),
                new StringSchema('reason', 'Brief explanation of theme choices (max 100 characters)'),
            ],
            requiredFields: ['dark', 'primary', 'light', 'skin', 'radius', 'font', 'menuColor', 'menuAccent', 'reason'],
        );

        $hintNote = $colorHint !== null
            ? sprintf(" The dominant detected brand color is '%s' — use this as primary unless your visual analysis clearly indicates another.", $colorHint)
            : '';

        $prompt = "You are a professional UI/UX designer. Analyze this logo image carefully. Look at the dominant brand colors, style, and personality. Then suggest the optimal complete theme configuration that best matches the logo. Choose primary color to closely match the logo's main brand color. Choose dark/light schemes that complement it. For radius: use lg/full for modern/playful logos, none/sm for corporate/technical ones. Choose fonts that match brand personality. Keep reason under 100 characters.".$hintNote;

        $apiKey = config('prism.providers.openrouter.api_key', '');

        if (empty($apiKey)) {
            return $this->fallback($colorHint);
        }

        try {
            $response = Prism::structured()
                ->using(PrismProvider::OpenRouter, 'google/gemini-2.0-flash-001')
                ->withSchema($schema)
                ->withMessages([
                    new UserMessage(
                        $prompt,
                        [Image::fromBase64(base64_encode((string) file_get_contents($file->getRealPath())), $file->getMimeType())],
                    ),
                ])
                ->generate();

            return [
                ...(array) $response->structured,
                'ai_derived' => true,
            ];
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
