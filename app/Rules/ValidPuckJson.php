<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidPuckJson implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The :attribute must be a valid Puck document (array).');

            return;
        }

        $maxItems = config('pages.puck.max_content_items', 200);
        $allowed = config('pages.puck.allowed_components', []);

        $content = $value['content'] ?? [];
        if (! is_array($content)) {
            $fail('The :attribute content must be an array.');

            return;
        }

        if (count($content) > $maxItems) {
            $fail("The :attribute may not contain more than {$maxItems} blocks.");

            return;
        }

        foreach ($content as $index => $item) {
            if (! is_array($item)) {
                $fail("The :attribute content[{$index}] must be an object.");

                return;
            }
            $type = $item['type'] ?? null;
            if ($type === null || $type === '') {
                $fail("The :attribute content[{$index}] must have a type.");

                return;
            }
            if ($allowed !== [] && ! in_array($type, $allowed, true)) {
                $fail("The :attribute content[{$index}] has disallowed component type \"{$type}\".");

                return;
            }
        }
    }
}
