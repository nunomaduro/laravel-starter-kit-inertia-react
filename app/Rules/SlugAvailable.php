<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Organization;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class SlugAvailable implements ValidationRule
{
    public function __construct(private ?int $excludeOrgId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        if (! preg_match('/^[a-z0-9][a-z0-9\-]{1,61}[a-z0-9]$/', $value)) {
            $fail('The :attribute must be 3–63 characters, start and end with a letter or number, and contain only lowercase letters, numbers, and hyphens.');

            return;
        }

        if (in_array($value, config('reserved-slugs', []), true)) {
            $fail('The :attribute "'.$value.'" is reserved and cannot be used.');

            return;
        }

        $query = Organization::query()->where('slug', $value);

        if ($this->excludeOrgId !== null) {
            $query->where('id', '!=', $this->excludeOrgId);
        }

        if ($query->exists()) {
            $fail('The :attribute "'.$value.'" is already taken.');
        }
    }
}
