<?php

namespace App\Http\Controllers;

use App\Queries\DashboardQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardQuery $dashboardQuery): View
    {
        return view('dashboard.index', $dashboardQuery->for($request->user()));
    }
}
