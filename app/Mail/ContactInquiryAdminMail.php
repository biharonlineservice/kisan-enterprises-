<?php

namespace App\Mail;

use App\Models\WebsiteInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactInquiryAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WebsiteInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $typeLabel = match ($this->inquiry->inquiry_type) {
            'bulk_buyer' => 'Bulk Buyer Inquiry',
            'farmer' => 'Farmer Inquiry',
            default => 'General Contact Message',
        };

        return new Envelope(
            subject: "📩 [{$typeLabel}] {$this->inquiry->name} - Kisan Enterprises",
            replyTo: $this->inquiry->email ? [$this->inquiry->email] : null,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-inquiry',
        );
    }
}

