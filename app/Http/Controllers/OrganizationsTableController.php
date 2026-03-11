<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTables\OrganizationDataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class OrganizationsTableController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('organizations/table', OrganizationDataTable::inertiaProps($request));
    }
}
