<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetRequiredTermsVersionsForUser;
use App\Actions\RecordTermsAcceptance;
use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final readonly class TermsAcceptController
{
    public function __construct(
        private GetRequiredTermsVersionsForUser $getRequiredTermsVersionsForUser,
        private RecordTermsAcceptance $recordTermsAcceptance
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $pending = $this->getRequiredTermsVersionsForUser->handle($user);

        if ($pending->isEmpty()) {
            $intended = $request->query('intended') ?? $request->session()->pull('url.intended', route('dashboard'));

            return redirect()->to($intended);
        }

        $intended = (string) ($request->query('intended') ?? $request->session()->pull('url.intended', route('dashboard')));

        $pendingVersions = $pending->map(fn (TermsVersion $v): array => [
            'id' => $v->id,
            'title' => $v->title,
            'slug' => $v->slug,
            'type' => $v->type->value,
            'type_label' => $v->type->label(),
            'effective_at' => $v->effective_at->format('M j, Y'),
            'summary' => $v->summary,
            'body' => $v->body,
            'body_html' => Str::markdown($v->body),
        ])->values()->all();

        return Inertia::render('terms/accept', [
            'pendingVersions' => $pendingVersions,
            'intended' => $intended,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $pending = $this->getRequiredTermsVersionsForUser->handle($user);

        if ($pending->isEmpty()) {
            return redirect()->to($request->input('intended', route('dashboard')));
        }

        $requiredIds = $pending->pluck('id')->all();
        $acceptedIds = array_map(intval(...), (array) $request->input('accepted_ids', []));
        $missing = array_diff($requiredIds, $acceptedIds);

        if ($missing !== []) {
            return back()->withErrors([
                'accepted_ids' => __('You must accept all required documents to continue.'),
            ]);
        }

        foreach (TermsVersion::query()->whereIn('id', $requiredIds)->get() as $version) {
            $this->recordTermsAcceptance->handle($user, $version, $request);
        }

        $intended = $request->input('intended', route('dashboard'));

        return redirect()->to($intended)->with('flash', ['success' => __('Thank you. You have accepted the required documents.')]);
    }
}
