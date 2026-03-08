<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\OrganizationDomain;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class CaddyAskController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $domain = (string) $request->query('domain', '');

        if ($domain === '') {
            return response('', 400);
        }

        $exists = OrganizationDomain::query()
            ->where('domain', $domain)
            ->where('is_verified', true)
            ->where('status', 'dns_verified')
            ->exists();

        if (! $exists) {
            return response('', 403);
        }

        return response('', 200);
    }
}
