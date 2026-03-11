<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTables\PostDataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PostsTableController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('posts/table', PostDataTable::inertiaProps($request));
    }
}
