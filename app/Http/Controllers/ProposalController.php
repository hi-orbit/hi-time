<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\ProposalTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ProposalController extends Controller
{
    /**
     * Display a listing of proposals
     */
    public function index()
    {
        $proposals = Proposal::with(['lead', 'customer', 'template', 'creator'])
            ->latest()
            ->paginate(20);

        return view('proposals.index', compact('proposals'));
    }

    /**
     * Show the form for creating a new proposal
     */
    public function create(Request $request)
    {
        $leadId = $request->get('lead_id');
        $customerId = $request->get('customer_id');

        $leads = Lead::where('status', 'active')->get();
        $customers = Customer::all();
        $templates = ProposalTemplate::active()->get();

        $selectedLead = $leadId ? Lead::find($leadId) : null;
        $selectedCustomer = $customerId ? Customer::find($customerId) : null;

        return view('proposals.create', compact('leads', 'customers', 'templates', 'selectedLead', 'selectedCustomer'));
    }

    /**
     * Store a newly created proposal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lead_id' => 'nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'template_id' => 'nullable|exists:proposal_templates,id',
            'content' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
            'recipient_email' => 'required|email',
        ]);

        // Ensure either lead_id or customer_id is provided, but not both
        if ((!$validated['lead_id'] && !$validated['customer_id']) ||
            ($validated['lead_id'] && $validated['customer_id'])) {
            return back()->withErrors(['recipient' => 'Please select either a lead or a customer, but not both.']);
        }

        $validated['created_by'] = Auth::id();

        $proposal = Proposal::create($validated);

        return redirect()->route('proposals.show', $proposal)
            ->with('success', 'Proposal created successfully.');
    }

    /**
     * Display the specified proposal
     */
    public function show(Proposal $proposal)
    {
        $proposal->load(['lead', 'customer', 'template', 'creator']);
        return view('proposals.show', compact('proposal'));
    }

    /**
     * Show the form for editing the proposal
     */
    public function edit(Proposal $proposal)
    {
        if (!$proposal->canBeEdited()) {
            return redirect()->route('proposals.show', $proposal)
                ->with('error', 'This proposal cannot be edited in its current status.');
        }

        $leads = Lead::where('status', 'active')->get();
        $customers = Customer::all();
        $templates = ProposalTemplate::active()->get();

        return view('proposals.edit', compact('proposal', 'leads', 'customers', 'templates'));
    }

    /**
     * Update the specified proposal
     */
    public function update(Request $request, Proposal $proposal)
    {
        if (!$proposal->canBeEdited()) {
            return redirect()->route('proposals.show', $proposal)
                ->with('error', 'This proposal cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lead_id' => 'nullable|exists:leads,id',
            'customer_id' => 'nullable|exists:customers,id',
            'template_id' => 'nullable|exists:proposal_templates,id',
            'content' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
            'recipient_email' => 'required|email',
        ]);

        // Ensure either lead_id or customer_id is provided, but not both
        if ((!$validated['lead_id'] && !$validated['customer_id']) ||
            ($validated['lead_id'] && $validated['customer_id'])) {
            return back()->withErrors(['recipient' => 'Please select either a lead or a customer, but not both.']);
        }

        $proposal->update($validated);

        return redirect()->route('proposals.show', $proposal)
            ->with('success', 'Proposal updated successfully.');
    }

    /**
     * Remove the specified proposal
     */
    public function destroy(Proposal $proposal)
    {
        if (!$proposal->canBeEdited()) {
            return redirect()->route('proposals.index')
                ->with('error', 'This proposal cannot be deleted in its current status.');
        }

        $proposal->delete();

        return redirect()->route('proposals.index')
            ->with('success', 'Proposal deleted successfully.');
    }

    /**
     * Send proposal via email
     */
    public function send(Request $request, Proposal $proposal)
    {
        if (!$proposal->canBeSent()) {
            return redirect()->route('proposals.show', $proposal)
                ->with('error', 'This proposal cannot be sent in its current status.');
        }

        $validated = $request->validate([
            'email_subject' => 'required|string|max:255',
            'email_body' => 'required|string',
        ]);

        $proposal->update([
            'email_subject' => $validated['email_subject'],
            'email_body' => $validated['email_body'],
        ]);

        // Generate PDF
        $this->generatePdf($proposal);

        // Send email (placeholder - will implement proper mailer)
        try {
            // TODO: Implement actual email sending
            $proposal->markAsSent();

            return redirect()->route('proposals.show', $proposal)
                ->with('success', 'Proposal sent successfully.');
        } catch (\Exception $e) {
            return redirect()->route('proposals.show', $proposal)
                ->with('error', 'Failed to send proposal: ' . $e->getMessage());
        }
    }

    /**
     * Preview proposal
     */
    public function preview(Proposal $proposal)
    {
        $proposal->load(['lead', 'customer', 'template']);
        return view('proposals.preview', compact('proposal'));
    }

    /**
     * Download proposal as PDF
     */
    public function downloadPdf(Proposal $proposal)
    {
        $pdfPath = $this->generatePdf($proposal);

        return response()->download($pdfPath, $proposal->proposal_number . '.pdf');
    }

    /**
     * Generate PDF for proposal
     */
    private function generatePdf(Proposal $proposal): string
    {
        $proposal->load(['lead', 'customer']);

        $pdf = Pdf::loadView('proposals.pdf', compact('proposal'));

        $filename = 'proposal-' . $proposal->proposal_number . '.pdf';
        $path = storage_path('app/public/proposals/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        $proposal->update(['pdf_path' => 'proposals/' . $filename]);

        return $path;
    }
}
