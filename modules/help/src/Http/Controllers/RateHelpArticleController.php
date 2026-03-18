<?php

declare(strict_types=1);

namespace Modules\Help\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Help\Actions\RateHelpArticleAction;
use Modules\Help\Models\HelpArticle;

final class RateHelpArticleController
{
    public function __invoke(Request $request, HelpArticle $helpArticle): RedirectResponse
    {
        /** @var array{is_helpful: bool} $validated */
        $validated = $request->validate([
            'is_helpful' => ['required', 'boolean'],
        ]);

        resolve(RateHelpArticleAction::class)->handle($helpArticle, $validated['is_helpful']);

        return back()->with('status', 'Thank you for your feedback.');
    }
}
