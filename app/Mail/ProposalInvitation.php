<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ProposalInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public Proposal $proposal;

    /**
     * Create a new message instance.
     */
    public function __construct(Proposal $proposal)
    {
        $this->proposal = $proposal;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->proposal->recipient_email,
            subject: $this->proposal->email_subject ?? 'New Proposal: ' . $this->proposal->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.proposal-invitation',
            with: [
                'proposal' => $this->proposal,
                'viewUrl' => $this->proposal->public_url,
                'recipientName' => $this->proposal->recipient_name,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Attach PDF if it exists
        if ($this->proposal->pdf_path && file_exists(storage_path('app/public/' . $this->proposal->pdf_path))) {
            $attachments[] = Attachment::fromPath(storage_path('app/public/' . $this->proposal->pdf_path))
                ->as($this->proposal->proposal_number . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
