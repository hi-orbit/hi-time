<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Fix PHP upload temp directory issue
$uploadTmpDir = dirname(__DIR__) . '/storage/tmp';
if (!is_dir($uploadTmpDir)) {
    mkdir($uploadTmpDir, 0755, true);
}
ini_set('upload_tmp_dir', $uploadTmpDir);

// Also set other upload related settings
ini_set('file_uploads', '1');
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('max_file_uploads', '20');

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'customer.project' => \App\Http\Middleware\CustomerProjectAccess::class,
            'restrict.customer' => \App\Http\Middleware\RestrictCustomerAccess::class,
            'api.key' => \App\Http\Middleware\ApiAuthenticate::class,
        ]);

        // Exclude specific routes from CSRF for image uploads
        $middleware->validateCsrfTokens(except: [
            'proposals/upload-image',
            'proposal-templates/upload-image',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render JSON error responses (401/403/404/422) for API requests
        $exceptions->shouldRenderJsonWhen(fn ($request) => str_starts_with($request->path(), 'api/') || $request->expectsJson());
    })->create();
