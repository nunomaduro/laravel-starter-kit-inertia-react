<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ToggleWishlist;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final readonly class WishlistController
{
    public function __invoke(Request $request, Property $property, ToggleWishlist $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->handle($user, $property);

        return redirect()->back();
    }
}
