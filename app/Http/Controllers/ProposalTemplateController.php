<?php

namespace App\Http\Controllers;

use App\Models\ProposalTemplate;
use Illuminate\Http\Request;

class ProposalTemplateController extends Controller
{
    /**
     * Display a listing of proposal templates
     */
    public function index()
    {
        $templates = ProposalTemplate::latest()->paginate(20);
        return view('proposal-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('proposal-templates.create');
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        ProposalTemplate::create($validated);

        return redirect()->route('proposal-templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template
     */
    public function show(ProposalTemplate $proposalTemplate)
    {
        return view('proposal-templates.show', compact('proposalTemplate'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(ProposalTemplate $proposalTemplate)
    {
        return view('proposal-templates.edit', compact('proposalTemplate'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, ProposalTemplate $proposalTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $proposalTemplate->update($validated);

        return redirect()->route('proposal-templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template
     */
    public function destroy(ProposalTemplate $proposalTemplate)
    {
        // Check if template is being used by any proposals
        if ($proposalTemplate->proposals()->count() > 0) {
            return redirect()->route('proposal-templates.index')
                ->with('error', 'Cannot delete template that is being used by proposals.');
        }

        $proposalTemplate->delete();

        return redirect()->route('proposal-templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
