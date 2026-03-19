<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CompleteOnboardingAction;
use App\Features\OnboardingFeature;
use App\Models\User;
use App\Support\FeatureHelper;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class OnboardingController
{
    public function show(Request $request): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null || ! FeatureHelper::isActiveForClass(OnboardingFeature::class, $user)) {
            return redirect()->route('dashboard');
        }

        $onboarding = $user->onboarding();
        $steps = $onboarding->steps()->map(fn ($step): array => [
            'title' => $step->title,
            'cta' => $step->cta,
            'link' => $step->link,
            'complete' => $step->complete(),
        ])->values()->all();

        $nextStep = $onboarding->nextUnfinishedStep();

        return Inertia::render('onboarding/show', [
            'status' => $request->session()->get('status'),
            'alreadyCompleted' => $user->onboarding_completed,
            'steps' => $steps,
            'inProgress' => $onboarding->inProgress(),
            'percentageCompleted' => $onboarding->percentageCompleted(),
            'nextStep' => $nextStep !== null
                ? ['title' => $nextStep->title, 'link' => $nextStep->link, 'cta' => $nextStep->cta]
                : null,
        ]);
    }

    public function store(#[CurrentUser] User $user, CompleteOnboardingAction $action): RedirectResponse
    {
        $action->handle($user);

        return to_route('dashboard')->with('status', __('Welcome! You’re all set.'));
    }
}
