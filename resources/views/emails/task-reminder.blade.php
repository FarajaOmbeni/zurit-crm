<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Reminder: {{ $task->title }}</title>
</head>
<body style="font-family: Inter, sans-serif; line-height: 1.6; color: #2E2E2E; background-color: #F5F3F7; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #7639C2; font-family: 'Zilla Slab', serif; font-size: 28px; margin: 0;">Zurit CRM</h1>
        </div>
        
        <h2 style="color: #2E2E2E; font-family: 'Zilla Slab', serif; font-size: 24px; margin-bottom: 20px;">Task Reminder</h2>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            Hello {{ $userName }},
        </p>
        
        <p style="color: #6B6B6B; font-size: 16px; margin-bottom: 20px;">
            You have a task that {{ $isOverdue ? 'is overdue' : 'is due soon' }}:
        </p>
        
        <div style="background-color: #F5F3F7; border-left: 4px solid {{ $isOverdue ? '#FF5B5D' : '#7639C2' }}; padding: 20px; margin: 30px 0; border-radius: 4px;">
            <h3 style="margin: 0 0 15px 0; color: #2E2E2E; font-size: 20px; font-weight: bold;">
                {{ $task->title }}
            </h3>
            
            @if($task->description)
            <p style="margin: 10px 0; color: #6B6B6B; font-size: 14px;">
                {{ $task->description }}
            </p>
            @endif
            
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #E0E0E0;">
                <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                    <strong>Due Date:</strong> 
                    <span style="color: {{ $isOverdue ? '#FF5B5D' : '#2E2E2E' }}; font-weight: bold;">
                        {{ $task->due_date->format('l, F j, Y \a\t g:i A') }}
                    </span>
                </p>
                
                <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                    <strong>Priority:</strong> 
                    <span style="display: inline-block; padding: 4px 12px; background-color: {{ 
                        $task->priority === 'high' ? '#000000' : ($task->priority === 'medium' ? '#FF5B5D' : '#6B6B6B')
                    }}; color: #ffffff; border-radius: 4px; font-size: 12px; text-transform: capitalize;">
                        {{ $task->priority }}
                    </span>
                </p>
                
                <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                    <strong>Status:</strong> 
                    <span style="display: inline-block; padding: 4px 12px; background-color: {{ 
                        $task->status === 'completed' ? '#7639C2' : ($task->status === 'in_progress' ? '#7639C2' : ($task->status === 'cancelled' ? '#FF5B5D' : '#6B6B6B'))
                    }}; color: #ffffff; border-radius: 4px; font-size: 12px; text-transform: capitalize;">
                        {{ str_replace('_', ' ', $task->status) }}
                    </span>
                </p>
                
                <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                    <strong>Type:</strong> 
                    <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $task->type) }}</span>
                </p>
            </div>
        </div>
        
        @if($task->lead)
        <div style="background-color: #F5F3F7; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; color: #2E2E2E; font-size: 14px; font-weight: bold;">
                Related Lead/Client:
            </p>
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Company:</strong> {{ $task->lead->company }}
            </p>
            @if($task->lead->name)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Contact:</strong> {{ $task->lead->name }}
            </p>
            @endif
            @if($task->lead->email)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Email:</strong> <a href="mailto:{{ $task->lead->email }}" style="color: #7639C2; text-decoration: none;">{{ $task->lead->email }}</a>
            </p>
            @endif
            @if($task->lead->phone)
            <p style="margin: 5px 0; color: #6B6B6B; font-size: 14px;">
                <strong>Phone:</strong> <a href="tel:{{ $task->lead->phone }}" style="color: #7639C2; text-decoration: none;">{{ $task->lead->phone }}</a>
            </p>
            @endif
        </div>
        @endif
        
        @if($isOverdue)
        <div style="margin-top: 20px; padding: 15px; background-color: #FF5B5D; color: #ffffff; border-radius: 4px;">
            <p style="margin: 0; font-size: 14px; font-weight: bold;">
                ⚠️ This task is overdue. Please complete it as soon as possible.
            </p>
        </div>
        @else
        <div style="margin-top: 20px; padding: 15px; background-color: #F5F3F7; border-radius: 4px;">
            <p style="margin: 0; color: #6B6B6B; font-size: 14px;">
                This task is due soon. Please make sure to complete it on time.
            </p>
        </div>
        @endif
        
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

