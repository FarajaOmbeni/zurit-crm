<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Follow-up: {{ $lead->company }}</title>
</head>
<body style="font-family: Inter, sans-serif; line-height: 1.6; color: #2E2E2E; background-color: #F5F3F7; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #7639C2; font-family: 'Zilla Slab', serif; font-size: 28px; margin: 0;">Zurit CRM</h1>
        </div>
        
        <h2 style="color: #2E2E2E; font-family: 'Zilla Slab', serif; font-size: 24px; margin-bottom: 20px;">Follow-up Reminder</h2>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            Hello {{ $userName }},
        </p>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            This is a reminder that you have a scheduled follow-up for:
        </p>
        
        <div style="background-color: #F5F3F7; border-left: 4px solid #7639C2; padding: 20px; margin: 30px 0; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; color: #2E2E2E; font-size: 18px; font-weight: bold;">
                {{ $lead->company }}
            </p>
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Contact:</strong> {{ $lead->name }}
            </p>
            @if($lead->position)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Position:</strong> {{ $lead->position }}
            </p>
            @endif
            @if($lead->email)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Email:</strong> <a href="mailto:{{ $lead->email }}" style="color: #7639C2; text-decoration: none;">{{ $lead->email }}</a>
            </p>
            @endif
            @if($lead->phone)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Phone:</strong> <a href="tel:{{ $lead->phone }}" style="color: #7639C2; text-decoration: none;">{{ $lead->phone }}</a>
            </p>
            @endif
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Status:</strong> 
                <span style="display: inline-block; padding: 4px 12px; background-color: #FF5B5D; color: #ffffff; border-radius: 4px; font-size: 12px; text-transform: capitalize;">
                    {{ str_replace('_', ' ', $lead->status) }}
                </span>
            </p>
            @if($lead->product)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Product:</strong> {{ $lead->product }}
            </p>
            @endif
            @if($lead->value)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Deal Value:</strong> KES {{ number_format($lead->value, 2) }}
            </p>
            @endif
        </div>
        
        @if($schedule->notes)
        <div style="background-color: #F5F3F7; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #6B6B6B; font-size: 14px;">
                <strong>Notes:</strong> {{ $schedule->notes }}
            </p>
        </div>
        @endif
        
        @if($lead->notes)
        <div style="background-color: #F5F3F7; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #6B6B6B; font-size: 14px;">
                <strong>Lead Notes:</strong> {{ $lead->notes }}
            </p>
        </div>
        @endif
        
        <div style="margin-top: 30px; padding: 20px; background-color: #F5F3F7; border-radius: 4px;">
            <p style="margin: 0 0 15px 0; color: #2E2E2E; font-size: 14px; font-weight: bold;">
                Scheduled for: {{ $schedule->scheduled_at->format('l, F j, Y \a\t g:i A') }}
            </p>
            @if($schedule->is_recurring)
            <p style="margin: 0; color: #6B6B6B; font-size: 12px;">
                This is a recurring follow-up (every {{ $schedule->interval_days }} days)
            </p>
            @endif
        </div>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #F5F3F7; text-align: center;">
            <p style="color: #6B6B6B; font-size: 12px; margin: 0;">
                © {{ date('Y') }} Zurit Consulting. All rights reserved.
            </p>
            <p style="color: #6B6B6B; font-size: 12px; margin: 10px 0 0 0;">
                This is an automated reminder from Zurit CRM.
            </p>
        </div>
    </div>
</body>
</html>

