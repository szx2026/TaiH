<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        return view('integrations.index');
    }
}
