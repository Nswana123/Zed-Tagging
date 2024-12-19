<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address; // Ensure this is imported

class HourlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $messageContent;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->messageContent = "Hello"; // Set the message here or pass it dynamically if needed
        \Log::info('MAIL_FROM_ADDRESS: ' . env('MAIL_FROM_ADDRESS'));
    \Log::info('MAIL_FROM_NAME: ' . env('MAIL_FROM_NAME'));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'notification@zedmobile.co.zm'), env('MAIL_FROM_NAME', 'ZedMobile')),
            subject: 'Hourly Report Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.hourly_report',
            with: ['content' => $this->messageContent] // Pass the dynamic content
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
