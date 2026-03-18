<?php

declare(strict_types=1);

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Blog\DataTables\PostDataTable;

final class PostsTableController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('posts/table', PostDataTable::inertiaProps($request));
    }
}
