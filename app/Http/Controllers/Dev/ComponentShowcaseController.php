<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dev;

use Inertia\Inertia;
use Inertia\Response;

final class ComponentShowcaseController
{
    public function __invoke(): Response
    {
        return Inertia::render('dev/components');
    }
}
