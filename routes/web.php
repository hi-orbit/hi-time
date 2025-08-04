<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

// Redirect root to login or dashboard
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

// Authentication routes
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Project routes
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);

    // Customer routes
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);

    // Reports routes
    Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/time-by-customer-this-month', [\App\Http\Controllers\ReportsController::class, 'timeByCustomerThisMonth'])->name('reports.time-by-customer-this-month');
    Route::get('/reports/time-by-customer-last-month', [\App\Http\Controllers\ReportsController::class, 'timeByCustomerLastMonth'])->name('reports.time-by-customer-last-month');
    Route::get('/reports/time-by-user', [\App\Http\Controllers\ReportsController::class, 'timeByUser'])->name('reports.time-by-user');

    Route::get('/time-tracking', function () {
        return view('time-tracking.index');
    })->name('time-tracking.index');

    // Simple notification system endpoints
    Route::get('/api/notifications/pending', function () {
        $notificationService = new \App\Services\NotificationService();
        $notifications = $notificationService->getPendingNotifications(Auth::id());

        return response()->json(['notifications' => $notifications]);
    });

    Route::post('/api/notifications/clear', function () {
        $notificationService = new \App\Services\NotificationService();
        $notificationService->markAllAsRead(Auth::id());

        return response()->json(['success' => true]);
    });

    Route::post('/api/notifications/{id}/read', function ($id) {
        $notificationService = new \App\Services\NotificationService();
        $success = $notificationService->markNotificationAsRead($id);

        return response()->json(['success' => $success]);
    });

    // Legacy FCM subscription endpoint (kept for compatibility)
    Route::post('/api/fcm/subscribe', function () {
        return response()->json(['success' => true, 'message' => 'Using simple notifications instead']);
    });

    // Admin only routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/settings', function () {
            return view('settings.index');
        })->name('settings.index');
    });
});
