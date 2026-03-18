<?php

declare(strict_types=1);

namespace Modules\Contact\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Contact\Actions\StoreContactSubmission;
use Modules\Contact\Http\Requests\StoreContactSubmissionRequest;

final readonly class ContactSubmissionController
{
    public function create(): Response
    {
        return Inertia::render('contact/create');
    }

    public function store(
        StoreContactSubmissionRequest $request,
        StoreContactSubmission $action,
    ): RedirectResponse {
        /** @var array{name: string, email: string, subject: string, message: string} $data */
        $data = $request->safe()->only(['name', 'email', 'subject', 'message']);

        $action->handle($data);

        return to_route('contact.create')
            ->with('status', 'Thank you. Your message has been sent.');
    }
}
