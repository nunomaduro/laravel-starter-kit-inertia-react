<?php

declare(strict_types=1);

namespace Modules\Announcements\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Announcements\DataTables\AnnouncementDataTable;

final class AnnouncementsTableController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('announcements/table', AnnouncementDataTable::inertiaProps($request));
    }
}
