<?php

namespace App\Mail;

use App\Models\FollowUpSchedule;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowUpEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lead;
    public $schedule;
    public $userName;

    /**
     * Create a new message instance.
     */
    public function __construct(Lead $lead, FollowUpSchedule $schedule, string $userName)
    {
        $this->lead = $lead;
        $this->schedule = $schedule;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Follow-up Reminder: {$this->lead->company}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.follow-up-email',
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
