<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTables\AnnouncementDataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AnnouncementsTableController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('announcements/table', AnnouncementDataTable::inertiaProps($request));
    }
}
