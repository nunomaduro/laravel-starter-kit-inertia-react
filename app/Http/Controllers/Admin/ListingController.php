<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\ApproveProperty;
use App\Actions\RejectProperty;
use App\Enums\PropertyStatus;
use App\Http\Requests\Admin\UpdateListingStatusRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ListingController
{
    public function index(Request $request): Response
    {
        $query = Property::query()->with(['host', 'media']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        } else {
            $query->where('status', PropertyStatus::Pending);
        }

        $listings = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('admin/listings/index', [
            'listings' => $listings,
            'filters' => $request->only(['status']),
        ]);
    }

    public function update(
        UpdateListingStatusRequest $request,
        Property $property,
        ApproveProperty $approveProperty,
        RejectProperty $rejectProperty,
    ): RedirectResponse {
        /** @var array{status: string, rejection_reason: string|null} $validated */
        $validated = $request->validated();

        if ($validated['status'] === 'approved') {
            Gate::authorize('approve', $property);
            $approveProperty->handle($property);
        } else {
            Gate::authorize('reject', $property);
            $rejectProperty->handle($property);
        }

        return redirect()->back();
    }
}
