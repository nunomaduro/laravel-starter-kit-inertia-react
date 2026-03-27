<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Actions\CreateProperty;
use App\Actions\UpdateProperty;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final readonly class PropertyController
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $properties = $user->properties()
            ->with('media')
            ->latest()
            ->paginate(15);

        return Inertia::render('host/properties/index', [
            'properties' => $properties,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('host/properties/create');
    }

    public function store(StorePropertyRequest $request, CreateProperty $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->handle($user, $request->validated());

        return redirect()->route('host.properties.index');
    }

    public function edit(Property $property): Response
    {
        Gate::authorize('update', $property);

        $property->load(['media', 'roomTypes']);

        return Inertia::render('host/properties/edit', [
            'property' => $property,
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property, UpdateProperty $action): RedirectResponse
    {
        Gate::authorize('update', $property);

        $action->handle($property, $request->validated());

        return redirect()->route('host.properties.index');
    }

    public function destroy(Property $property): RedirectResponse
    {
        Gate::authorize('delete', $property);

        $property->delete();

        return redirect()->route('host.properties.index');
    }
}
