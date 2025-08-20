<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use Symfony\Component\HttpFoundation\Response;

class CustomerProjectAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is not a customer, allow access
        if (!$user || !$user->isCustomer()) {
            return $next($request);
        }

        // For customer users, check if they have access to the project
        $projectId = $request->route('project')?->id ?? $request->route('id');

        if ($projectId) {
            $project = Project::find($projectId);

            if ($project && !$project->assignedUsers()->where('user_id', $user->id)->exists()) {
                abort(403, 'You do not have access to this project.');
            }
        }

        return $next($request);
    }
}
