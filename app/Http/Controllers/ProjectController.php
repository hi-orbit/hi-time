<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isCustomer()) {
            // Customers can only see projects they're assigned to
            $projects = $user->assignedProjects()
                ->with('customer')
                ->latest()
                ->paginate(20);
        } else {
            // Admin, users, and contractors can see all projects
            $projects = Project::with('customer')
                ->latest()
                ->paginate(20);
        }

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Check if user is a customer and prevent access
        if (Auth::user()->isCustomer()) {
            abort(403, 'Customers cannot create projects.');
        }

        $customers = Customer::active()->orderBy('name')->get();
        $customerUsers = User::where('role', 'customer')->orderBy('name')->get();
        $selectedCustomerId = $request->get('customer_id');

        return view('projects.create', compact('customers', 'customerUsers', 'selectedCustomerId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is a customer and prevent access
        if (Auth::user()->isCustomer()) {
            abort(403, 'Customers cannot create projects.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,archived',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'customer_id' => $request->customer_id,
            'created_by' => Auth::id(),
        ]);

        // Assign users to the project
        if ($request->assigned_users) {
            $project->assignedUsers()->attach($request->assigned_users);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $user = Auth::user();

        // Prevent access to archived projects unless user is admin
        if ($project->archived && (!$user || $user->role !== 'admin')) {
            abort(404);
        }

        // Check if customer user has access to this project
        if ($user->isCustomer() && !$project->assignedUsers()->where('user_id', $user->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        $project->load('customer', 'tasks');

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        // Check if user is a customer and prevent access
        if (Auth::user()->isCustomer()) {
            abort(403, 'Customers cannot edit projects.');
        }

        $customers = Customer::active()->orderBy('name')->get();
        $customerUsers = User::where('role', 'customer')->orderBy('name')->get();

        return view('projects.edit', compact('project', 'customers', 'customerUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        // Check if user is a customer and prevent access
        if (Auth::user()->isCustomer()) {
            abort(403, 'Customers cannot update projects.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,archived',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'customer_id' => $validated['customer_id'],
        ]);

        // Update assigned users
        if (isset($validated['assigned_users'])) {
            $project->assignedUsers()->sync($validated['assigned_users']);
        } else {
            $project->assignedUsers()->detach();
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        // Check if user is a customer and prevent access
        if (Auth::user()->isCustomer()) {
            abort(403, 'Customers cannot delete projects.');
        }

        // Check if project has tasks
        if ($project->tasks()->count() > 0) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'Cannot delete project with existing tasks. Archive it instead.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
