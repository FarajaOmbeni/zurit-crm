<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Report</title>
</head>
<body style="font-family: Inter, sans-serif; line-height: 1.6; color: #2E2E2E; background-color: #F5F3F7; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #7639C2; font-family: 'Zilla Slab', serif; font-size: 28px; margin: 0;">Zurit CRM</h1>
        </div>
        
        <h2 style="color: #2E2E2E; font-family: 'Zilla Slab', serif; font-size: 24px; margin-bottom: 20px;">Sales Report</h2>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            Hello,
        </p>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            Please find attached the sales report for <strong>{{ $report->report_date->format('l, F j, Y') }}</strong>.
        </p>
        
        <div style="background-color: #F5F3F7; border-left: 4px solid #7639C2; padding: 20px; margin: 30px 0; border-radius: 4px;">
            <p style="margin: 5px 0; color: #2E2E2E; font-size: 14px;">
                <strong>Report Date:</strong> {{ $report->report_date->format('M d, Y') }}
            </p>
            <p style="margin: 5px 0; color: #2E2E2E; font-size: 14px;">
                <strong>Generated:</strong> {{ $report->created_at->format('M d, Y \a\t g:i A') }}
            </p>
            @if($report->user)
            <p style="margin: 5px 0; color: #2E2E2E; font-size: 14px;">
                <strong>Prepared by:</strong> {{ $report->user->name }}
            </p>
            @endif
        </div>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            This comprehensive report includes:
        </p>
        
        <ul style="color: #6B6B6B; font-size: 14px; margin-bottom: 30px; padding-left: 20px;">
            <li style="margin-bottom: 8px;">Outreach Summary and Metrics</li>
            <li style="margin-bottom: 8px;">Won Deals Overview</li>
            <li style="margin-bottom: 8px;">New Leads Information</li>
            <li style="margin-bottom: 8px;">Key Reminders and Tasks</li>
            <li style="margin-bottom: 8px;">Highlights and Challenges</li>
        </ul>
        
        <p style="color: #6B6B6B; font-size: 14px; margin-top: 30px;">
            The report is attached as a PDF document for your convenience.
        </p>
        
        <p style="color: #6B6B6B; font-size: 14px; margin-top: 20px;">
            Best regards,<br>
            <strong style="color: #7639C2;">Zurit CRM Team</strong>
        </p>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #F5F3F7; text-align: center;">
            <p style="color: #6B6B6B; font-size: 12px; margin: 5px 0;">
                This is an automated email from Zurit CRM
            </p>
            <p style="color: #6B6B6B; font-size: 12px; margin: 5px 0;">
                © {{ date('Y') }} Zurit CRM. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

