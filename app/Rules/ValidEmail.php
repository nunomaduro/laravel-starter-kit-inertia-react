<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class ValidEmail implements ValidationRule
{
    private const string REGEX = '/[a-z0-9!#$%&*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&*+\/=?^_`{|}~-]+)*@[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.[a-z]{2,}/';

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        assert(is_string($value));

        if (in_array(preg_match(self::REGEX, $value), [0, false], true)) {
            $fail('The :attribute must be a valid email address.');
        }
    }
}
