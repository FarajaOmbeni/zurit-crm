<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ $report->report_date->format('M d, Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #2E2E2E;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #7639C2;
        }
        .header h1 {
            color: #7639C2;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header .subtitle {
            color: #6B6B6B;
            font-size: 14px;
            margin-top: 5px;
        }
        .header .date {
            color: #2E2E2E;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #7639C2;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #F5F3F7;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            padding: 15px;
            background-color: #F5F3F7;
            border: 1px solid #E0E0E0;
            text-align: center;
        }
        .stat-label {
            font-size: 10px;
            color: #6B6B6B;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #7639C2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #7639C2;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E0E0E0;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background-color: #F5F3F7;
        }
        .text-content {
            background-color: #F5F3F7;
            padding: 15px;
            border-left: 4px solid #7639C2;
            margin-bottom: 10px;
        }
        .text-content p {
            color: #2E2E2E;
            line-height: 1.8;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-high {
            background-color: #000000;
            color: white;
        }
        .badge-medium {
            background-color: #FF5B5D;
            color: white;
        }
        .badge-low {
            background-color: #6B6B6B;
            color: white;
        }
        .badge-pending {
            background-color: #FFA500;
            color: white;
        }
        .badge-completed {
            background-color: #4CAF50;
            color: white;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #F5F3F7;
            text-align: center;
            color: #6B6B6B;
            font-size: 10px;
        }
        .no-data {
            padding: 20px;
            text-align: center;
            color: #6B6B6B;
            font-style: italic;
            background-color: #F5F3F7;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Zurit Consulting - {{ $report->user->name ?? 'Unknown' }} Report</h1>
        <div class="date">{{ $report->report_date->format('l, F j, Y') }}</div>
        <div class="subtitle" style="margin-top: 10px;">
            Generated: {{ $report->created_at->format('M d, Y \a\t g:i A') }}
        </div>
    </div>

    <!-- Outreach Summary -->
    <div class="section">
        <div class="section-title">Outreach Summary</div>
        <div class="text-content">
            <p style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">
                Total Leads Contacted: <span style="color: #7639C2;">{{ $outreachSummary['total_contacted'] ?? 0 }}</span>
            </p>
            @if(isset($outreachSummary['contacted_leads']) && count($outreachSummary['contacted_leads']) > 0)
                <p style="font-weight: bold; margin-top: 15px; margin-bottom: 5px; color: #2E2E2E;">Leads Contacted:</p>
                <ul style="list-style-type: disc; padding-left: 25px; margin-top: 5px;">
                    @foreach($outreachSummary['contacted_leads'] as $lead)
                        <li style="margin-bottom: 3px;">{{ $lead['display_name'] }}</li>
                    @endforeach
                </ul>
            @else
                <p style="font-style: italic; color: #6B6B6B; margin-top: 10px;">No leads contacted during this period.</p>
            @endif
        </div>
    </div>

    <!-- Scheme Engagement Updates -->
    <div class="section">
        <div class="section-title">Scheme Engagement Updates</div>
        @if(isset($engagementUpdates) && count($engagementUpdates) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Contact Person</th>
                    <th style="width: 25%;">Phone Number</th>
                    <th style="width: 45%;">Feedback</th>
                </tr>
            </thead>
            <tbody>
                @foreach($engagementUpdates as $update)
                <tr>
                    <td>{{ $update['contact_person'] ?? 'N/A' }}</td>
                    <td>{{ $update['phone'] ?? 'N/A' }}</td>
                    <td>{{ $update['feedback'] ?? 'No feedback provided' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">No engagement updates for this period.</div>
        @endif
    </div>

    <!-- Program Sales Update -->
    <div class="section">
        <div class="section-title">Program Sales Update</div>
        @if(count($wonDeals) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Client/Lead Name</th>
                    <th style="width: 35%;">Product</th>
                    <th style="width: 25%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wonDeals as $deal)
                <tr>
                    <td>{{ $deal['client_name'] ?? $deal['company'] ?? 'N/A' }}</td>
                    <td>{{ $deal['product_name'] ?? $deal['product'] ?? 'N/A' }}</td>
                    <td><strong>Ksh {{ number_format($deal['amount'] ?? $deal['value'] ?? 0, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top: 15px; padding: 10px; background-color: #F5F3F7; text-align: right;">
            <strong style="color: #7639C2; font-size: 14px;">
                Total Sales: Ksh {{ number_format(array_sum(array_map(function($deal) { return $deal['amount'] ?? $deal['value'] ?? 0; }, $wonDeals)), 2) }}
            </strong>
        </div>
        @else
        <div class="no-data">No program sales for this period.</div>
        @endif
    </div>

    <!-- Key Reminders -->
    @if((isset($upcomingTasks) && count($upcomingTasks) > 0) || (isset($overdueTasks) && count($overdueTasks) > 0))
    <div class="section">
        <div class="section-title">Key Reminders</div>

        @if(isset($overdueTasks) && count($overdueTasks) > 0)
        <div style="margin-bottom: 15px;">
            <strong style="color: #FF5B5D; font-size: 13px;">Overdue Tasks ({{ count($overdueTasks) }})</strong>
            <table style="margin-top: 5px;">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueTasks as $task)
                    <tr>
                        <td>{{ $task['title'] ?? 'N/A' }}</td>
                        <td style="color: #FF5B5D; font-weight: bold;">
                            {{ isset($task['due_date']) ? \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') : 'N/A' }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $task['priority'] ?? 'low' }}">
                                {{ ucfirst($task['priority'] ?? 'Low') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($upcomingTasks) && count($upcomingTasks) > 0)
        <div>
            <strong style="color: #7639C2; font-size: 13px;">Upcoming Tasks ({{ count($upcomingTasks) }})</strong>
            <table style="margin-top: 5px;">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingTasks as $task)
                    <tr>
                        <td>{{ $task['title'] ?? 'N/A' }}</td>
                        <td>{{ isset($task['due_date']) ? \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $task['priority'] ?? 'low' }}">
                                {{ ucfirst($task['priority'] ?? 'Low') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was automatically generated by Zurit Consulting CRM</p>
        <p>© {{ date('Y') }} Zurit Consulting. All rights reserved.</p>
    </div>
</body>
</html>

