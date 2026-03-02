<?php

namespace App\Mail;

use App\Models\DataRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowupDataRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DataRequest $dataRequest,
        public string $clientName,
        public string $kapName,
        public int $daysOverdue,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Data Audit Belum Diterima — ' . $this->dataRequest->section,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.followup-reminder',
        );
    }
}
