<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        return view("admin.dashboard.index");
    }

    public function components_test()
    {
        return view('test');
    }


    /**
     * API
     */
    public function apiToggleSidebar()
    {
        $sidebarState = session('sidebar', 1);
        $newSidebarState = $sidebarState === 1 ? 0 : 1;

        session(['sidebar' => $newSidebarState]);

        return response()->json(['sidebar' => $newSidebarState]);
    }
}
