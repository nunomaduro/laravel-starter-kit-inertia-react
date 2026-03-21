<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\StoreDealRequest;
use Cogneiss\ModuleCrm\Models\Contact;
use Cogneiss\ModuleCrm\Models\Deal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $deal->update($request->validate([
            'contact_id' => ['required', 'integer', 'exists:crm_contacts,id'],
            'title' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'stage' => ['required', 'string', 'max:50'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
        ]));

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
