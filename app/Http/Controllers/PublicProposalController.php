<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicProposalController extends Controller
{
    /**
     * Display a proposal for public viewing/signing
     */
    public function view(string $token)
    {
        $proposal = Proposal::where('signature_token', $token)->firstOrFail();

        // Mark as viewed if it's the first time
        if ($proposal->status === 'sent') {
            $proposal->markAsViewed();
        }

        $proposal->load(['lead', 'customer', 'template', 'creator']);

        return view('proposals.public.view', compact('proposal'));
    }

    /**
     * Accept and sign the proposal
     */
    public function sign(Request $request, string $token)
    {
        $proposal = Proposal::where('signature_token', $token)->firstOrFail();

        if (!in_array($proposal->status, ['sent', 'viewed'])) {
            return redirect()->route('proposals.public.view', $token)
                ->with('error', 'This proposal cannot be signed in its current status.');
        }

        $validated = $request->validate([
            'signature_name' => 'required|string|max:255',
            'signature_email' => 'required|email',
            'signature_date' => 'required|date',
            'signature_data' => 'nullable|string', // Base64 signature if using canvas
            'agreed_terms' => 'required|accepted',
        ]);

        // Prepare signature data
        $signatureData = [
            'name' => $validated['signature_name'],
            'email' => $validated['signature_email'],
            'date' => $validated['signature_date'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signed_at' => now()->toISOString(),
        ];

        if (isset($validated['signature_data'])) {
            $signatureData['signature_image'] = $validated['signature_data'];
        }

        $proposal->accept($signatureData);

        Log::info('Proposal signed', [
            'proposal_id' => $proposal->id,
            'signer_name' => $validated['signature_name'],
            'signer_email' => $validated['signature_email'],
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('proposals.public.view', $token)
            ->with('success', 'Proposal signed successfully! We\'ll be in touch soon.');
    }

    /**
     * Reject the proposal
     */
    public function reject(Request $request, string $token)
    {
        $proposal = Proposal::where('signature_token', $token)->firstOrFail();

        if (!in_array($proposal->status, ['sent', 'viewed'])) {
            return redirect()->route('proposals.public.view', $token)
                ->with('error', 'This proposal cannot be rejected in its current status.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $proposal->reject();

        Log::info('Proposal rejected', [
            'proposal_id' => $proposal->id,
            'reason' => $validated['rejection_reason'] ?? 'No reason provided',
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('proposals.public.view', $token)
            ->with('info', 'Proposal has been declined. Thank you for your consideration.');
    }
}
