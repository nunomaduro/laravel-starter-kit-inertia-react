<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\StoreDealRequest;
use App\Http\Requests\Crm\UpdateDealRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Crm\Models\Contact;
use Modules\Crm\Models\Deal;

final readonly class DealController
{
    public function index(Request $request): Response
    {
        $deals = Deal::query()
            ->with('contact')
            ->latest()
            ->paginate(15);

        return Inertia::render('crm/deals/index', [
            'deals' => $deals,
        ]);
    }

    public function create(): Response
    {
        $contacts = Contact::query()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return Inertia::render('crm/deals/create', [
            'contacts' => $contacts,
        ]);
    }

    public function store(StoreDealRequest $request): RedirectResponse
    {
        Deal::query()->create($request->validated());

        return to_route('crm.deals.index')
            ->with('status', __('Deal created.'));
    }

    public function show(Deal $deal): RedirectResponse
    {
        return to_route('crm.deals.edit', $deal);
    }

    public function edit(Deal $deal): Response
    {
        $deal->load('contact');

        $contacts = Contact::query()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return Inertia::render('crm/deals/edit', [
            'deal' => $deal,
            'contacts' => $contacts,
        ]);
    }

    public function update(UpdateDealRequest $request, Deal $deal): RedirectResponse
    {
        $deal->update($request->validated());

        return to_route('crm.deals.index')
            ->with('status', __('Deal updated.'));
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $deal->delete();

        return to_route('crm.deals.index')
            ->with('status', __('Deal deleted.'));
    }
}
