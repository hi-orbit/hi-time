<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of leads
     */
    public function index()
    {
        $leads = Lead::latest()->paginate(20);
        return view('leads.index', compact('leads'));
    }

    /**
     * Show the form for creating a new lead
     */
    public function create()
    {
        return view('leads.create');
    }

    /**
     * Store a newly created lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:leads,email',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,converted,lost',
            'source' => 'nullable|string|max:255',
        ]);

        // Set default status if not provided
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }

        Lead::create($validated);

        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified lead
     */
    public function show(Lead $lead)
    {
        $lead->load(['proposals', 'convertedCustomer']);
        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead
     */
    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:leads,email,' . $lead->id,
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,converted,lost',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified lead
     */
    public function destroy(Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('leads.index')
                ->with('error', 'Cannot delete a converted lead.');
        }

        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Convert lead to customer
     */
    public function convert(Lead $lead)
    {
        if ($lead->isConverted()) {
            return redirect()->route('leads.show', $lead)
                ->with('error', 'Lead is already converted.');
        }

        $customer = $lead->convertToCustomer();

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Lead converted to customer successfully.');
    }
}
