<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SlugAvailabilityController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $slug = (string) $request->query('slug', '');
        $currentOrg = TenantContext::get();
        $excludeOrgId = $currentOrg?->id;

        $reserved = in_array($slug, config('reserved-slugs', []), true);
        $validFormat = (bool) preg_match('/^[a-z0-9][a-z0-9\-]{1,61}[a-z0-9]$/', $slug);

        if (! $validFormat || $reserved) {
            return response()->json([
                'available' => false,
                'reserved' => $reserved,
                'taken' => false,
                'suggestion' => null,
            ]);
        }

        $query = Organization::query()->where('slug', $slug);
        if ($excludeOrgId) {
            $query->where('id', '!=', $excludeOrgId);
        }

        $taken = $query->exists();

        $suggestion = null;
        if ($taken) {
            for ($i = 1; $i <= 10; $i++) {
                $candidate = $slug.'-'.$i;
                if (! Organization::query()->where('slug', $candidate)->exists()) {
                    $suggestion = $candidate;
                    break;
                }
            }
        }

        return response()->json([
            'available' => ! $taken,
            'reserved' => false,
            'taken' => $taken,
            'suggestion' => $suggestion,
        ]);
    }
}
