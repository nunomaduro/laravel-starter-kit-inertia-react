<?php

declare(strict_types=1);

namespace Modules\Changelog\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Changelog\Models\ChangelogEntry;

final class ChangelogController
{
    public function index(): Response
    {
        $entries = ChangelogEntry::query()
            ->published()
            ->with('tags')
            ->latest('released_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('changelog/index', [
            'entries' => $entries,
        ]);
    }
}
