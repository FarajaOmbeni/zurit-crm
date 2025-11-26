# CRM Backend Architecture Plan

## Key Concept: Leads Become Clients

**Important**: Leads are the primary entity. When a lead's status changes to "won", it automatically becomes a client (is_client flag set to true). The Client Database page shows leads where is_client = true.

## Database Schema Design

### Core Tables

1. **users** (extend existing)
   - Add: `role` enum('admin', 'manager', 'team_member')
   - Add: `manager_id` foreign key (nullable, for team members)
   - Add: `is_active` boolean

2. **leads** (PRIMARY ENTITY - becomes client when won)
   - `id`
   - `name` (contact person name)
   - `position` (contact person's position)
   - `company` (company name)
   - `city`
   - `country`
   - `added_by` (user_id - foreign key)
   - `status` enum('new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost')
   - `value` decimal (deal value)
   - `product` string (product name or reference)
   - `expected_close_date` date (nullable)
   - `actual_close_date` date (nullable)
   - `lost_reason` text (nullable)
   - `won_at` timestamp (nullable, when converted to client)
   - `is_client` boolean (default false, set to true when status = 'won')
   - `notes` text (nullable)
   - `created_at`, `updated_at`

3. **products**
   - `id`
   - `name`
   - `price` decimal
   - `category`
   - `created_at`, `updated_at`

4. **activities**
   - `id`
   - `lead_id` (foreign key)
   - `user_id` (foreign key)
   - `type` enum('call', 'email', 'meeting', 'note')
   - `activity_date` datetime
   - `created_at`, `updated_at`

5. **tasks**
   - `id`
   - `lead_id` (foreign key)
   - `created_by` (user_id - foreign key)
   - `type` enum('follow_up', 'call', 'email', 'meeting', 'other')
   - `due_date` datetime
   - `completed_at` datetime (nullable)
   - `priority` enum('low', 'medium', 'high')
   - `status` enum('pending', 'in_progress', 'completed', 'cancelled')
   - `created_at`, `updated_at`

6. **follow_up_schedules**
   - `id`
   - `lead_id` (foreign key)
   - `task_id` (foreign key, nullable)
   - `type` enum('initial_email', 'follow_up_email', 'call')
   - `scheduled_at` datetime
   - `completed_at` datetime (nullable)
   - `interval_days` integer (for recurring: 2 days, 7 days)
   - `is_recurring` boolean
   - `next_follow_up_date` datetime (nullable)
   - `created_at`, `updated_at`

### Indexes and Relationships

- Foreign keys with cascade deletes where appropriate
- Indexes on frequently queried fields (added_by, status, is_client, due_date, activity_date)
- Composite indexes for common queries (added_by + status, is_client + status, lead_id + activity_date)

## Models and Relationships

### User Model (`app/Models/User.php`)
- Relationships:
  - `manager()` - belongsTo(User) for team members
  - `teamMembers()` - hasMany(User) for managers
  - `leads()` - hasMany(Lead, 'added_by')
  - `clients()` - hasMany(Lead) where is_client = true (scope)
  - `tasks()` - hasMany(Task, 'created_by')
  - `activities()` - hasMany(Activity)
- Scopes: `admins()`, `managers()`, `teamMembers()`

### Lead Model (`app/Models/Lead.php`) - Primary entity (becomes client when won)
- Relationships:
  - `addedBy()` - belongsTo(User, 'added_by')
  - `activities()` - hasMany(Activity)
  - `tasks()` - hasMany(Task)
  - `followUpSchedules()` - hasMany(FollowUpSchedule)
- Scopes: 
  - `byStatus()` - filter by status
  - `won()` - where status = 'won'
  - `lost()` - where status = 'lost'
  - `active()` - where status != 'won' and status != 'lost'
  - `clients()` - where is_client = true
  - `leads()` - where is_client = false
  - `newLeads()` - where status = 'new_lead'
- Methods:
  - `markAsWon()` - Set status to 'won', is_client to true, set won_at timestamp, set actual_close_date
  - `markAsLost($reason)` - Set status to 'lost', set lost_reason, set actual_close_date
- Events/Observers:
  - `updating` event - When status changes to 'won', automatically set is_client = true and won_at = now()

### Task Model (`app/Models/Task.php`)
- Relationships:
  - `lead()` - belongsTo(Lead)
  - `createdBy()` - belongsTo(User, 'created_by')
- Scopes: `dueToday()`, `overdue()`, `pending()`, `upcoming()`

### Activity Model (`app/Models/Activity.php`)
- Relationships:
  - `lead()` - belongsTo(Lead)
  - `user()` - belongsTo(User)
- Scopes: `today()`, `byType()`, `forUser()`

### FollowUpSchedule Model (`app/Models/FollowUpSchedule.php`)
- Relationships:
  - `lead()` - belongsTo(Lead)
  - `task()` - belongsTo(Task, nullable)
- Methods: `scheduleNext()`, `markCompleted()`

## API Endpoints Structure

### Authentication (existing via Laravel Breeze)
- POST `/api/login`
- POST `/api/logout`
- POST `/api/forgot-password`
- POST `/api/reset-password`

### Dashboard APIs (`app/Http/Controllers/Api/DashboardController.php`)
- GET `/api/dashboard/overview` - Daily overview, quick stats
- GET `/api/dashboard/tasks-due-today` - Tasks due today
- GET `/api/dashboard/stats` - Quick stats (pie charts, lead counts, etc.)

### Leads & Pipeline APIs (`app/Http/Controllers/Api/LeadController.php`)
- GET `/api/leads` - List leads (with filters, pagination, exclude clients by default)
- GET `/api/leads/{id}` - Lead details
- POST `/api/leads` - Create new lead
- PUT `/api/leads/{id}` - Update lead
- PATCH `/api/leads/{id}/status` - Update lead status
- PATCH `/api/leads/{id}/mark-won` - Mark lead as won (converts to client)
- PATCH `/api/leads/{id}/mark-lost` - Mark lead as lost
- DELETE `/api/leads/{id}` - Delete lead
- GET `/api/leads/kanban` - Kanban view data (grouped by status)

### Client Database APIs (`app/Http/Controllers/Api/ClientController.php`)
- GET `/api/clients` - List clients (leads where is_client = true)
- GET `/api/clients/{id}` - Client details
- PUT `/api/clients/{id}` - Update client

### Tasks & Follow-ups APIs (`app/Http/Controllers/Api/TaskController.php`)
- GET `/api/tasks` - List tasks (with filters)
- GET `/api/tasks/{id}` - Task details
- POST `/api/tasks` - Create task
- PUT `/api/tasks/{id}` - Update task
- PATCH `/api/tasks/{id}/complete` - Mark task complete
- DELETE `/api/tasks/{id}` - Delete task
- GET `/api/tasks/upcoming` - Upcoming follow-ups

### Activities APIs (`app/Http/Controllers/Api/ActivityController.php`)
- GET `/api/activities` - List activities
- GET `/api/leads/{leadId}/activities` - Lead/Client activities
- POST `/api/activities` - Create activity/note
- PUT `/api/activities/{id}` - Update activity
- DELETE `/api/activities/{id}` - Delete activity

### Reports APIs (`app/Http/Controllers/Api/ReportController.php`)
- POST `/api/reports/eod` - Generate End of Day Report
  - Request body: `{ date: 'YYYY-MM-DD', highlights: 'text', challenges: 'text' }`
  - Returns structured EOD report with all sections
- POST `/api/reports/custom` - Generate custom report for date range
  - Request body: `{ start_date: 'YYYY-MM-DD', end_date: 'YYYY-MM-DD', highlights: 'text', challenges: 'text' }`

### Products APIs (`app/Http/Controllers/Api/ProductController.php`)
- GET `/api/products` - List products
- GET `/api/products/{id}` - Product details
- POST `/api/products` - Create product
- PUT `/api/products/{id}` - Update product
- DELETE `/api/products/{id}` - Delete product

### Users Management APIs (`app/Http/Controllers/Api/UserController.php`)
- GET `/api/users` - List users (role-based filtering)
- GET `/api/users/{id}` - User details
- POST `/api/users` - Create user (admin only)
- PUT `/api/users/{id}` - Update user
- DELETE `/api/users/{id}` - Delete user
- GET `/api/users/{id}/team` - Get user's team (for managers)

## End of Day (EOD) Report Structure

### Report Generation Flow
1. User clicks "Generate Report" button
2. Calendar opens to select date/period
3. Form opens with fields for user input:
   - Highlights (textarea)
   - Challenges (textarea)
4. System generates report with the following sections:

### EOD Report Sections

**Header**: Salesperson_name - date

**1. Outreach Summary** (auto-calculated from activities and leads)
- Number of Pension Schemes Contacted Today: [count of activities where type='call' or type='email' for the day]
- Schemes Newly Engaged: [count of new leads created today]
- Follow-Ups Conducted: [count of activities where lead status='follow_ups' for the day]
- Total Schemes in Active Pipeline: [count of leads where status != 'won' and status != 'lost']

**2. Schemes Engagement Update** (table - auto-generated)
| Client | Product | Stage | Activities |
|--------|---------|-------|-----------|
| [lead.company] | [lead.product] | [lead.status] | [activities for today] |

**3. New Leads or Emails Received** (auto-generated)
- List of leads created today with basic info

**4. Won Deals** (table - auto-generated)
| Client | Product | Payment |
|--------|---------|---------|
| [lead.company] | [lead.product] | [lead.value] |
- Filtered: leads where status changed to 'won' today

**5. Lost Deals** (table - auto-generated)
| Client | Product | Lost Reason |
|--------|---------|-------------|
| [lead.company] | [lead.product] | [lead.lost_reason] |
- Filtered: leads where status changed to 'lost' today

**6. Highlights and Challenges** (user input)
- Highlights: [user input from form]
- Challenges: [user input from form]

**7. Key Reminders** (auto-generated from tasks)
- Upcoming tasks due tomorrow and next 3 days
- Overdue tasks
- Important follow-ups scheduled

## Middleware & Authorization

### Role-based Middleware (`app/Http/Middleware/EnsureRole.php`)
- `admin` - Admin only
- `manager` - Manager and Admin
- `team_member` - All authenticated users

### Policies (`app/Policies/`)
- `LeadPolicy` - Control lead access based on role
  - Admin: sees all leads
  - Manager: sees team members' leads
  - Team member: sees only their own leads
- `TaskPolicy` - Control task access
- `UserPolicy` - Admin-only user management

## Automated Follow-up System

### Follow-up Logic (`app/Services/FollowUpService.php`)
- Automatically create follow-up tasks when lead moves to "follow_ups" status
- Schedule initial email (2 days after contact)
- Schedule recurring follow-ups (every 7 days)
- Mark follow-ups as completed when activities are logged

### Scheduled Jobs (`app/Console/Commands/`)
- `ProcessFollowUpSchedules` - Check and process due follow-ups
- `SendFollowUpEmails` - Send scheduled follow-up emails
- `SendTaskReminders` - Send reminders for tasks due soon

### Email Templates (`resources/views/emails/`)
- `follow-up-email.blade.php` - Follow-up email template
- `task-reminder.blade.php` - Task reminder email

## Database Migrations

All migrations in `database/migrations/`:
1. `2024_XX_XX_add_role_to_users_table.php` - Add role, manager_id, is_active to users
2. `2024_XX_XX_create_leads_table.php` - Main table (name, position, company, city, country, added_by, status, value, product, expected_close_date, actual_close_date, lost_reason, won_at, is_client, notes)
3. `2024_XX_XX_create_products_table.php` - Product catalog (name, price, category)
4. `2024_XX_XX_create_activities_table.php` - Activities (lead_id, user_id, type, activity_date)
5. `2024_XX_XX_create_tasks_table.php` - Tasks (lead_id, created_by, type, due_date, completed_at, priority, status)
6. `2024_XX_XX_create_follow_up_schedules_table.php` - Automated follow-up scheduling

## Seeders

- `UserSeeder` - Seed admin user
- `ProductSeeder` - Seed sample products (optional)

## API Resources

Use Laravel API Resources (`app/Http/Resources/`) for consistent API responses:
- `LeadResource` - Lead/client data
- `TaskResource` - Task data
- `ActivityResource` - Activity data
- `UserResource` - User data
- `ProductResource` - Product data
- `EODReportResource` - Structured EOD report

## Key Features Implementation

1. **Role-based Access Control**: Middleware and policies ensure users only see appropriate data
2. **Lead to Client Conversion**: When lead status changes to 'won', automatically set is_client = true and won_at timestamp (via Model Observer)
3. **Automated Follow-ups**: Scheduled jobs check and create follow-up tasks
4. **Activity Tracking**: All interactions logged in activities table
5. **Email Integration**: Laravel Mail for sending follow-up emails
6. **On-Demand Reporting**: Reports generated when user clicks button, with user input for highlights/challenges
7. **Unified Data Model**: Leads and clients are the same entity, differentiated by is_client flag

## Report Service Implementation

### ReportService (`app/Services/ReportService.php`)

Methods:
- `generateEODReport($userId, $date, $highlights, $challenges)` - Generate EOD report for specific date
- `generateCustomReport($userId, $startDate, $endDate, $highlights, $challenges)` - Generate report for date range
- `calculateOutreachSummary($userId, $date)` - Calculate outreach metrics
- `getSchemesEngagementUpdate($userId, $date)` - Get schemes engagement table data
- `getNewLeads($userId, $date)` - Get new leads for the day
- `getWonDeals($userId, $date)` - Get won deals for the day
- `getLostDeals($userId, $date)` - Get lost deals for the day
- `getKeyReminders($userId, $date)` - Get upcoming tasks and reminders

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── DashboardController.php
│   │   │   ├── LeadController.php
│   │   │   ├── ClientController.php
│   │   │   ├── TaskController.php
│   │   │   ├── ActivityController.php
│   │   │   ├── ReportController.php
│   │   │   ├── ProductController.php
│   │   │   └── UserController.php
│   │   └── ...
│   ├── Middleware/
│   │   └── EnsureRole.php
│   └── Resources/
│       ├── LeadResource.php
│       ├── TaskResource.php
│       ├── ActivityResource.php
│       ├── UserResource.php
│       ├── ProductResource.php
│       └── EODReportResource.php
├── Models/
│   ├── User.php (extend)
│   ├── Lead.php
│   ├── Product.php
│   ├── Activity.php
│   ├── Task.php
│   └── FollowUpSchedule.php
├── Observers/
│   └── LeadObserver.php (handles lead to client conversion)
├── Policies/
│   ├── LeadPolicy.php
│   ├── TaskPolicy.php
│   └── UserPolicy.php
├── Services/
│   ├── FollowUpService.php
│   └── ReportService.php
└── Console/
    └── Commands/
        ├── ProcessFollowUpSchedules.php
        ├── SendFollowUpEmails.php
        └── SendTaskReminders.php

database/
├── migrations/
│   └── [6 migration files]
└── seeders/
    ├── UserSeeder.php
    └── ProductSeeder.php

routes/
└── api.php (new file for API routes)
```

## tasks

1. Create all database migrations for pipeline_stages, clients, leads, contacts, activities, tasks, follow_up_schedules, products, product_history, reports, and settings tables
2. Create all Eloquent models (Client, Lead, PipelineStage, Contact, Activity, Task, FollowUpSchedule, Product, ProductHistory, Report, Setting) with relationships and scopes
3. Extend User model with role field, manager relationship, and role-based scopes
4. Create authorization policies (LeadPolicy, ClientPolicy, TaskPolicy, UserPolicy) for role-based access control
5. Create EnsureRole middleware for role-based route protection
6. Set up API routes file (routes/api.php) with all endpoint definitions and authentication middleware
7. Create all API controllers (DashboardController, LeadController, ClientController, ContactController, TaskController, ActivityController, ReportController, ProductController, SettingController, UserController) with CRUD operations
8. Create FollowUpService for automated follow-up scheduling logic (2 days initial, 7 days recurring)
9. Create console commands for scheduled jobs (ProcessFollowUpSchedules, SendFollowUpEmails, GenerateDailyReports, GenerateEODReports)
10. Create seeders for pipeline stages and initial admin user
11. Create email templates for follow-up emails and task reminders

Next Steps After Backend
Set up API routes in routes/api.php
Implement authentication middleware for API
Create database migrations
Build models with relationships and conversion logic
Implement LeadObserver for automatic client conversion
Implement ReportService for EOD report generation
Implement controllers with proper authorization
Set up scheduled jobs for automation
Create API resources for consistent responses
Write tests for critical functionality

