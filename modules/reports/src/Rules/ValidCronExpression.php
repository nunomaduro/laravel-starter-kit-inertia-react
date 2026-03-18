<?php

declare(strict_types=1);

namespace Modules\Reports\Rules;

use Closure;
use Cron\CronExpression;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidCronExpression implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        if (! CronExpression::isValidExpression($value)) {
            $fail('The :attribute must be a valid cron expression (e.g. "0 9 * * 1").');
        }
    }
}
