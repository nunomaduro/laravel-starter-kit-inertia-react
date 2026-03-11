<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CategoriesTableController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('categories/table', CategoryDataTable::inertiaProps($request));
    }
}
