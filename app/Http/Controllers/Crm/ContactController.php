<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\StoreContactRequest;
use App\Http\Requests\Crm\UpdateContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Crm\Models\Contact;

final readonly class ContactController
{
    public function index(Request $request): Response
    {
        $contacts = Contact::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('crm/contacts/index', [
            'contacts' => $contacts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('crm/contacts/create');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        Contact::query()->create($request->validated());

        return to_route('crm.contacts.index')
            ->with('status', __('Contact created.'));
    }

    public function show(Contact $contact): RedirectResponse
    {
        return to_route('crm.contacts.edit', $contact);
    }

    public function edit(Contact $contact): Response
    {
        return Inertia::render('crm/contacts/edit', [
            'contact' => $contact,
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return to_route('crm.contacts.index')
            ->with('status', __('Contact updated.'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return to_route('crm.contacts.index')
            ->with('status', __('Contact deleted.'));
    }
}
