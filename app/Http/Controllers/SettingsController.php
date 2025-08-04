<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard
     */
    public function index()
    {
        return view('settings.index');
    }
}
