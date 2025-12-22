<?php

namespace App\Mail;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public Report $report;
    public string $recipientEmail;
    public ?string $userHighlights;
    public ?string $userChallenges;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, string $recipientEmail, ?string $highlights = null, ?string $challenges = null)
    {
        $this->report = $report->load('user');
        $this->recipientEmail = $recipientEmail;
        $this->userHighlights = $highlights;
        $this->userChallenges = $challenges;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sales Report - ' . $this->report->report_date->format('M d, Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.report-email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Prepare data for PDF
        $data = $this->report->data ?? [];
        
        // Use user-provided highlights/challenges if available, otherwise use stored ones
        $highlights = $this->userHighlights ?? $this->report->highlights;
        $challenges = $this->userChallenges ?? $this->report->challenges;
        
        $pdfData = [
            'report' => $this->report,
            'highlights' => $highlights,
            'challenges' => $challenges,
            'outreachSummary' => $data['outreach_summary'] ?? [
                'total_contacted' => 0,
                'contacted_leads' => [],
            ],
            'wonDeals' => $data['won_deals'] ?? [],
            'newLeads' => $data['new_leads'] ?? [],
            'lostDeals' => $data['lost_deals'] ?? [],
            'upcomingTasks' => $data['key_reminders']['upcoming_tasks'] ?? [],
            'overdueTasks' => $data['key_reminders']['overdue_tasks'] ?? [],
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.eod-report-pdf', $pdfData);
        
        $salespersonName = str_replace(' ', '-', strtolower($this->report->user->name));
        $filename = 'sales-report-' . $salespersonName . '-' . $this->report->report_date->format('Y-m-d') . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}

