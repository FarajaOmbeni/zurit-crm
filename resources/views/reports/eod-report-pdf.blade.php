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
        <h1>Zurit CRM</h1>
        <div class="subtitle">Sales Report</div>
        <div class="date">{{ $report->report_date->format('l, F j, Y') }}</div>
        <div class="subtitle" style="margin-top: 10px;">
            Generated: {{ $report->created_at->format('M d, Y \a\t g:i A') }}
        </div>
        @if($report->user)
        <div class="subtitle">Prepared by: {{ $report->user->name }}</div>
        @endif
    </div>

    <!-- Outreach Summary -->
    <div class="section">
        <div class="section-title">Outreach Summary</div>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Schemes Contacted</div>
                <div class="stat-value">{{ $outreachSummary['schemes_contacted'] ?? 0 }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Newly Engaged</div>
                <div class="stat-value">{{ $outreachSummary['schemes_newly_engaged'] ?? 0 }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Follow-ups</div>
                <div class="stat-value">{{ $outreachSummary['follow_ups_conducted'] ?? 0 }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Active Pipeline</div>
                <div class="stat-value">{{ $outreachSummary['active_pipeline'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Won Deals -->
    <div class="section">
        <div class="section-title">Won Deals ({{ count($wonDeals) }})</div>
        @if(count($wonDeals) > 0)
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Value (KES)</th>
                    <th>Product</th>
                    <th>Date Won</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wonDeals as $deal)
                <tr>
                    <td>{{ $deal['company'] ?? 'N/A' }}</td>
                    <td>{{ number_format($deal['value'] ?? 0, 2) }}</td>
                    <td>{{ $deal['product'] ?? 'N/A' }}</td>
                    <td>{{ isset($deal['won_at']) ? \Carbon\Carbon::parse($deal['won_at'])->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">No won deals for this period.</div>
        @endif
    </div>

    <!-- New Leads -->
    <div class="section">
        <div class="section-title">New Leads ({{ count($newLeads) }})</div>
        @if(count($newLeads) > 0)
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Expected Close</th>
                    <th>Value (KES)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($newLeads as $lead)
                <tr>
                    <td>{{ $lead['company'] ?? 'N/A' }}</td>
                    <td>{{ $lead['source'] ?? 'N/A' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $lead['status'] ?? 'N/A')) }}</td>
                    <td>{{ isset($lead['expected_close_date']) ? \Carbon\Carbon::parse($lead['expected_close_date'])->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ isset($lead['value']) ? number_format($lead['value'], 2) : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">No new leads for this period.</div>
        @endif
    </div>

    <!-- Lost Deals -->
    @if(count($lostDeals) > 0)
    <div class="section">
        <div class="section-title">Lost Deals ({{ count($lostDeals) }})</div>
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Reason</th>
                    <th>Value (KES)</th>
                    <th>Date Lost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lostDeals as $deal)
                <tr>
                    <td>{{ $deal['company'] ?? 'N/A' }}</td>
                    <td>{{ $deal['reason'] ?? 'Not specified' }}</td>
                    <td>{{ isset($deal['value']) ? number_format($deal['value'], 2) : 'N/A' }}</td>
                    <td>{{ isset($deal['lost_at']) ? \Carbon\Carbon::parse($deal['lost_at'])->format('M d, Y') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Key Reminders -->
    <div class="section">
        <div class="section-title">Key Reminders</div>
        
        @if(count($upcomingTasks) > 0)
        <div style="margin-bottom: 15px;">
            <strong style="color: #7639C2; font-size: 13px;">Upcoming Tasks ({{ count($upcomingTasks) }})</strong>
            <table style="margin-top: 5px;">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
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
                        <td>
                            <span class="badge badge-{{ $task['status'] ?? 'pending' }}">
                                {{ ucfirst($task['status'] ?? 'Pending') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($overdueTasks) > 0)
        <div>
            <strong style="color: #FF5B5D; font-size: 13px;">Overdue Tasks ({{ count($overdueTasks) }})</strong>
            <table style="margin-top: 5px;">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueTasks as $task)
                    <tr>
                        <td>{{ $task['title'] ?? 'N/A' }}</td>
                        <td>{{ isset($task['due_date']) ? \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $task['priority'] ?? 'low' }}">
                                {{ ucfirst($task['priority'] ?? 'Low') }}
                            </span>
                        </td>
                        <td style="color: #FF5B5D; font-weight: bold;">
                            {{ isset($task['due_date']) ? \Carbon\Carbon::parse($task['due_date'])->diffInDays(now()) : 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($upcomingTasks) === 0 && count($overdueTasks) === 0)
        <div class="no-data">No pending tasks.</div>
        @endif
    </div>

    <!-- Highlights -->
    @if($highlights)
    <div class="section">
        <div class="section-title">Highlights</div>
        <div class="text-content">
            <p>{{ $highlights }}</p>
        </div>
    </div>
    @endif

    <!-- Challenges -->
    @if($challenges)
    <div class="section">
        <div class="section-title">Challenges</div>
        <div class="text-content">
            <p>{{ $challenges }}</p>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was automatically generated by Zurit CRM</p>
        <p>© {{ date('Y') }} Zurit CRM. All rights reserved.</p>
    </div>
</body>
</html>

