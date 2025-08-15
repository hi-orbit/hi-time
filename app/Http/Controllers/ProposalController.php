<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\ProposalTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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

        $leads = Lead::whereIn('status', ['new', 'contacted', 'qualified'])->get();
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
        // Debug the raw request
        Log::info('Raw Request Debug:', [
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'raw_content' => $request->getContent(),
            'php_raw_input' => file_get_contents('php://input'),
            'input_all' => $request->all(),
            'input_json' => $request->json() ? $request->json()->all() : null,
            'has_files' => $request->hasFile('*'),
            'request_size' => strlen($request->getContent()),
            'php_input_size' => strlen(file_get_contents('php://input')),
            'server_content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'not_set',
            'post_data' => $_POST,
        ]);

        Log::info('ProposalController store method called:', [
            'request_method' => $request->method(),
            'request_uri' => $request->getRequestUri(),
            'request_data_keys' => array_keys($request->all()),
            'content_length' => strlen($request->get('content', '')),
            'user_id' => Auth::id(),
            'user_authenticated' => Auth::check(),
            'csrf_token' => $request->get('_token'),
            'session_token' => session()->token()
        ]);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'lead_id' => 'nullable|exists:leads,id',
                'customer_id' => 'nullable|exists:customers,id',
                'template_id' => 'nullable|exists:proposal_templates,id',
                'content' => 'required|string',
                'amount' => 'nullable|numeric|min:0',
                'valid_until' => 'nullable|date',
                'status' => 'nullable|in:draft,sent',
                'client_name' => 'nullable|string|max:255',
                'client_email' => 'nullable|email',
            ]);

            Log::info('Proposal validation passed:', ['validated_keys' => array_keys($validated)]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Proposal validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        // Set status based on which button was clicked
        $status = $request->input('status', 'draft');
        $validated['status'] = $status;

        // Ensure either lead_id, customer_id, or manual client info is provided
        if (!$validated['lead_id'] && !$validated['customer_id'] && !$validated['client_name']) {
            return back()->withErrors(['recipient' => 'Please select a lead, customer, or enter client information manually.'])->withInput();
        }

        // If both lead and customer are selected, prefer lead
        if ($validated['lead_id'] && $validated['customer_id']) {
            $validated['customer_id'] = null;
        }

        // Get recipient email from lead/customer or manual entry
        if ($validated['lead_id']) {
            $lead = \App\Models\Lead::find($validated['lead_id']);
            $validated['recipient_email'] = $lead->email;
        } elseif ($validated['customer_id']) {
            $customer = \App\Models\Customer::find($validated['customer_id']);
            $validated['recipient_email'] = $customer->email;
        } else {
            $validated['recipient_email'] = $validated['client_email'];
        }

        $validated['created_by'] = Auth::id();

        $proposal = Proposal::create($validated);

        $message = $status === 'sent' ? 'Proposal created and sent successfully!' : 'Proposal saved as draft successfully!';

        return redirect()->route('proposals.show', $proposal)
            ->with('success', $message);
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

    /**
     * Live preview proposal content with variable replacements
     */
    public function livePreview(Request $request)
    {
        try {
            // Check if user is authenticated (optional for debugging)
            $userAuthenticated = Auth::check();
            if (!$userAuthenticated) {
                Log::warning('LivePreview called without authentication - allowing for debugging');
                // Temporarily allow unauthenticated access for debugging
                // return response()->json([
                //     'success' => false,
                //     'message' => 'Authentication required. Please log in to use the preview feature.',
                //     'error_code' => 'AUTHENTICATION_REQUIRED'
                // ], 401);
            }

            // Handle both JSON and form data
            if ($request->isJson()) {
                $data = $request->json()->all();
            } else {
                $data = $request->all();
            }

            $content = $data['content'] ?? '';
            $leadId = $data['lead_id'] ?? null;
            $customerId = $data['customer_id'] ?? null;
            $title = $data['title'] ?? '';
            $amount = $data['amount'] ?? '';
            $validUntil = $data['valid_until'] ?? '';
            $clientName = $data['client_name'] ?? '';
            $clientEmail = $data['client_email'] ?? '';

            // Validate request size
            if (strlen($content) > 1000000) { // 1MB limit
                return response()->json([
                    'success' => false,
                    'message' => 'Content is too large. Please reduce the content size.',
                    'error_code' => 'CONTENT_TOO_LARGE'
                ], 413);
            }

            // Validate CSRF token more thoroughly (make it more lenient for debugging)
            $tokenFromRequest = $data['_token'] ?? $request->header('X-CSRF-TOKEN');
            $sessionToken = session()->token();

            if (!$tokenFromRequest) {
                Log::warning('No CSRF token in request');
                return response()->json([
                    'success' => false,
                    'message' => 'No CSRF token provided. Please refresh the page.',
                    'error_code' => 'NO_CSRF_TOKEN'
                ], 422);
            }

            if (!$sessionToken) {
                Log::warning('No session token available - creating new session');
                // Try to create a new session token
                $request->session()->regenerateToken();
                $sessionToken = session()->token();
            }

            if (!hash_equals($sessionToken, $tokenFromRequest)) {
                Log::warning('CSRF Token Mismatch in LivePreview:', [
                    'request_token' => $tokenFromRequest ? substr($tokenFromRequest, 0, 10) . '...' : 'null',
                    'session_token' => $sessionToken ? substr($sessionToken, 0, 10) . '...' : 'null',
                    'tokens_match' => false,
                    'user_id' => Auth::id(),
                    'user_authenticated' => $userAuthenticated
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Session expired or CSRF token mismatch. Please refresh the page and try again.',
                    'error_code' => 'CSRF_TOKEN_MISMATCH',
                    'debug' => [
                        'request_token_preview' => $tokenFromRequest ? substr($tokenFromRequest, 0, 10) . '...' : 'null',
                        'session_token_preview' => $sessionToken ? substr($sessionToken, 0, 10) . '...' : 'null'
                    ]
                ], 419);
            }

            // Get client data
            $clientData = [
                'name' => '',
                'email' => '',
                'address' => '',
                'company_number' => '',
                'company' => ''
            ];

            if ($leadId) {
                $lead = Lead::find($leadId);
                if ($lead) {
                    $clientData = [
                        'name' => $lead->name,
                        'email' => $lead->email,
                        'address' => $lead->address,
                        'company_number' => $lead->company_number,
                        'company' => $lead->company
                    ];
                }
            } elseif ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer) {
                    $clientData = [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'address' => $customer->address,
                        'company_number' => $customer->company_number,
                        'company' => $customer->name // For customers, the name IS the company name
                    ];
                }
            } elseif ($clientName || $clientEmail) {
                $clientData = [
                    'name' => $clientName,
                    'email' => $clientEmail,
                    'address' => '',
                    'company_number' => '',
                    'company' => ''
                ];
            }

            // Create replacements array
            $replacements = [
                'client_name' => $clientData['name'] ?: '[Client Name]',
                'client_email' => $clientData['email'] ?: '[Client Email]',
                'client_address' => $clientData['address'] ?: '[Client Address]',
                'client_company_number' => $clientData['company_number'] ?: '[Company Number]',
                'company_name' => $clientData['company'] ?: '[Company Name]',
                'company_number' => $clientData['company_number'] ?: '[Company Number]',
                'company_address' => $clientData['address'] ?: '[Client Address]',
                'proposal_title' => $title ?: '[Proposal Title]',
                'amount' => $amount ? '$' . $amount : '[Amount]',
                'date' => now()->format('d F Y'),
                'valid_until' => $validUntil ? \Carbon\Carbon::parse($validUntil)->format('d F Y') : '[Valid Until Date]',
                'first_name' => $clientData['name'] ? explode(' ', $clientData['name'])[0] : '[First Name]',
                'last_name' => $clientData['name'] ? implode(' ', array_slice(explode(' ', $clientData['name']), 1)) : '[Last Name]'
            ];

            // Replace placeholders with highlighted values
            $previewContent = $content;

            // Debug: Log what we're working with
            Log::info('LivePreview Debug:', [
                'content_length' => strlen($content),
                'content_sample' => substr($content, 0, 500),
                'has_client_name' => strpos($content, '{{client_name}}') !== false,
                'has_amount' => strpos($content, '{{amount}}') !== false,
                'replacements' => $replacements,
                'lead_id' => $leadId,
                'customer_id' => $customerId,
                'request_type' => $request->isJson() ? 'JSON' : 'FORM',
                'user_id' => Auth::id(),
                'csrf_validation' => 'passed'
            ]);

            // Simple test replacement first
            if (strpos($content, '{{client_name}}') !== false) {
                Log::info('Found {{client_name}} in content, attempting replacement');
            }

            foreach ($replacements as $key => $value) {
                $pattern = '/\{\{' . preg_quote($key) . '\}\}/';
                $replacement = '<span style="background-color: #fef3c7; padding: 2px 4px; border-radius: 3px; font-weight: 500; border: 1px solid #f59e0b;">' . $value . '</span>';

                // Debug each replacement
                $beforeCount = substr_count($previewContent, '{{' . $key . '}}');
                $previewContent = preg_replace($pattern, $replacement, $previewContent);
                $afterCount = substr_count($previewContent, '{{' . $key . '}}');

                if ($beforeCount > 0) {
                    Log::info("Replacement for {$key}:", [
                        'pattern' => $pattern,
                        'before_count' => $beforeCount,
                        'after_count' => $afterCount,
                        'value' => $value
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'preview' => $previewContent,
                'replacements' => $replacements,
                'debug' => [
                    'content_length' => strlen($content),
                    'processed_length' => strlen($previewContent),
                    'original_sample' => substr($content, 0, 200),
                    'processed_sample' => substr($previewContent, 0, 200),
                    'user_authenticated' => true,
                    'user_id' => Auth::id()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('LivePreview Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id() ?? 'not_authenticated',
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the preview: ' . $e->getMessage(),
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
