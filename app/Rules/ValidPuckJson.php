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
            $fail(sprintf('The :attribute may not contain more than %s blocks.', $maxItems));

            return;
        }

        foreach ($content as $index => $item) {
            if (! is_array($item)) {
                $fail(sprintf('The :attribute content[%s] must be an object.', $index));

                return;
            }

            $type = $item['type'] ?? null;
            if ($type === null || $type === '') {
                $fail(sprintf('The :attribute content[%s] must have a type.', $index));

                return;
            }

            if ($allowed !== [] && ! in_array($type, $allowed, true)) {
                $fail(sprintf('The :attribute content[%s] has disallowed component type "%s".', $index, $type));

                return;
            }
        }
    }
}
