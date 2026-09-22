<?php

use Illuminate\Support\Facades\Route;

// Public API - authenticated with per-user API keys (X-API-Key header)
Route::middleware('api.key')->group(function () {
    Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class);
});
