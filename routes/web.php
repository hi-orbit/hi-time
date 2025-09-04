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
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Public proposal routes (no auth required)
Route::get('/proposals/view/{token}', [\App\Http\Controllers\PublicProposalController::class, 'view'])->name('proposals.public.view');
Route::post('/proposals/sign/{token}', [\App\Http\Controllers\PublicProposalController::class, 'sign'])->name('proposals.public.sign');
Route::post('/proposals/reject/{token}', [\App\Http\Controllers\PublicProposalController::class, 'reject'])->name('proposals.public.reject');

// Temporary: Live preview route without auth middleware for debugging
Route::post('/proposals/live-preview', [\App\Http\Controllers\ProposalController::class, 'livePreview'])->name('proposals.live-preview');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Project routes with customer access middleware
    Route::resource('projects', \App\Http\Controllers\ProjectController::class)->middleware('customer.project');

    // Task attachment routes
    Route::post('/tasks/{task}/upload-attachment', [\App\Http\Controllers\TaskAttachmentController::class, 'upload'])->name('tasks.upload-attachment');
    Route::post('/tasks/{task}/dropzone-upload', [\App\Http\Controllers\TaskAttachmentController::class, 'dropzoneUpload'])->name('tasks.dropzone-upload');

    // Routes restricted from customers
    Route::middleware('restrict.customer')->group(function () {
        // Customer entity routes (different from customer role users)
        Route::resource('customers', \App\Http\Controllers\CustomerController::class);

        // Proposal system routes
        Route::resource('proposals', \App\Http\Controllers\ProposalController::class);
        Route::resource('leads', \App\Http\Controllers\LeadController::class);
        Route::resource('proposal-templates', \App\Http\Controllers\ProposalTemplateController::class);

        // Temporary workaround for proposal template update issue
        Route::post('/proposal-templates/{proposalTemplate}', [\App\Http\Controllers\ProposalTemplateController::class, 'update'])
            ->name('proposal-templates.update.post');

        // Additional proposal routes
        Route::post('/proposals/{proposal}/send', [\App\Http\Controllers\ProposalController::class, 'send'])->name('proposals.send');
        Route::get('/proposals/{proposal}/preview', [\App\Http\Controllers\ProposalController::class, 'preview'])->name('proposals.preview');
        Route::get('/proposals/{proposal}/pdf', [\App\Http\Controllers\ProposalController::class, 'downloadPdf'])->name('proposals.pdf');
        Route::post('/proposals/upload-image', [\App\Http\Controllers\ProposalController::class, 'uploadImage'])->name('proposals.upload-image');

        // Proposal template image upload route
        Route::post('/proposal-templates/upload-image', [\App\Http\Controllers\ProposalTemplateController::class, 'uploadImage'])->name('proposal-templates.upload-image');

        // Lead conversion
        Route::post('/leads/{lead}/convert', [\App\Http\Controllers\LeadController::class, 'convert'])->name('leads.convert');

        // Reports routes
        Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/time-by-customer-this-month', [\App\Http\Controllers\ReportsController::class, 'timeByCustomerThisMonth'])->name('reports.time-by-customer-this-month');
        Route::get('/reports/time-by-customer-last-month', [\App\Http\Controllers\ReportsController::class, 'timeByCustomerLastMonth'])->name('reports.time-by-customer-last-month');
        Route::get('/reports/time-by-user', [\App\Http\Controllers\ReportsController::class, 'timeByUser'])->name('reports.time-by-user');
        Route::get('/reports/time-by-user-enhanced', \App\Livewire\Reports\TimeByUserLivewire::class)->name('reports.time-by-user-enhanced');
        Route::get('/reports/my-time-today', [\App\Http\Controllers\ReportsController::class, 'myTimeToday'])->name('reports.my-time-today');

        Route::get('/time-tracking', function () {
            return view('time-tracking.index');
        })->name('time-tracking.index');
    });

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
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');

        // User management routes
        Route::resource('settings/users', \App\Http\Controllers\UserManagementController::class, [
            'names' => [
                'index' => 'settings.users.index',
                'create' => 'settings.users.create',
                'store' => 'settings.users.store',
                'show' => 'settings.users.show',
                'edit' => 'settings.users.edit',
                'update' => 'settings.users.update',
                'destroy' => 'settings.users.destroy',
            ]
        ]);

        // Additional password reset routes
        Route::get('/settings/users/{user}/reset-password', [\App\Http\Controllers\UserManagementController::class, 'showResetPassword'])
            ->name('settings.users.reset-password');
        Route::post('/settings/users/{user}/reset-password', [\App\Http\Controllers\UserManagementController::class, 'resetPassword'])
            ->name('settings.users.reset-password.store');
    });

    // CSRF token refresh route (for large forms)
    Route::get('/csrf-token', function () {
        return response()->json(['csrf_token' => csrf_token()]);
    })->name('csrf-token');
});
