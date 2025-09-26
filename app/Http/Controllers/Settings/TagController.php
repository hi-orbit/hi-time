<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = \App\Models\Tag::withCount('tasks')->orderBy('name')->get();
        return view('settings.tags-test', compact('tags'));
    }
}
