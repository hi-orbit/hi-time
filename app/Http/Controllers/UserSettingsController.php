<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    public function updateViewPreference(Request $request)
    {
        $request->validate([
            'view' => 'required|in:cards,list',
            'page' => 'required|string'
        ]);

        Auth::user()->setSetting($request->page . '_view', $request->view);

        return response()->json(['success' => true]);
    }
}
