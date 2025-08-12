<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with('customer')
            ->latest()
            ->paginate(20);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::active()->orderBy('name')->get();
        $selectedCustomerId = $request->get('customer_id');

        return view('projects.create', compact('customers', 'selectedCustomerId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,archived',
            'customer_id' => 'nullable|exists:customers,id',
            'due_date' => 'nullable|date|after:today',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'customer_id' => $request->customer_id,
            'due_date' => $request->due_date,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // Prevent access to archived projects unless user is admin
        if ($project->archived && (!Auth::user() || Auth::user()->role !== 'admin')) {
            abort(404);
        }

        $project->load('customer', 'tasks');

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $customers = Customer::active()->orderBy('name')->get();

        return view('projects.edit', compact('project', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,archived',
            'due_date' => 'nullable|date|after_or_equal:today',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
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
