<?php

namespace App\Http\Controllers;

use App\Models\ProposalTemplate;
use App\Helpers\SunEditorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // Debug: Log all request data
        Log::info('ProposalTemplate store request data:', $request->all());
        Log::info('Content field specifically:', ['content' => $request->get('content')]);
        Log::info('Content length:', ['length' => strlen($request->get('content', ''))]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string|min:1',
            'is_active' => 'required|boolean',
        ]);

        // Convert string "0"/"1" to proper boolean
        $validated['is_active'] = (bool) $validated['is_active'];

        // Auto-extract variables from the new content
        preg_match_all('/\{\{([^}]+)\}\}/', $validated['content'], $matches);
        $validated['variables'] = array_unique($matches[1]);

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
            'is_active' => 'required|boolean',
        ]);

        // Convert string "0"/"1" to proper boolean
        $validated['is_active'] = (bool) $validated['is_active'];

        // Auto-extract variables from the new content
        preg_match_all('/\{\{([^}]+)\}\}/', $validated['content'], $matches);
        $validated['variables'] = array_unique($matches[1]);

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

    /**
     * Handle image uploads for template content
     */
    public function uploadImage(Request $request)
    {
        return SunEditorHelper::uploadImage($request, 'template-images');
    }
}
