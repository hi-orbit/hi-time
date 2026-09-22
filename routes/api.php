<?php

use Illuminate\Support\Facades\Route;

// Public API - authenticated with per-user API keys (X-API-Key header)
Route::middleware('api.key')->group(function () {
    Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class);

    // Task comments (task notes)
    Route::get('tasks/{task}/comments', [\App\Http\Controllers\Api\TaskCommentController::class, 'index']);
    Route::post('tasks/{task}/comments', [\App\Http\Controllers\Api\TaskCommentController::class, 'store']);

    // Tags (list + create)
    Route::apiResource('tags', \App\Http\Controllers\Api\TagController::class, ['only' => ['index', 'store']]);
});
