# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Zurit CRM is a Laravel 12 + Vue 3 (Inertia.js) sales CRM application for managing leads, clients, tasks, and generating sales reports. It uses a unified data model where **leads become clients** when their status changes to "won" (via `is_client` flag).

## Development Commands

```bash
# Full development environment (runs Laravel server, queue listener, and Vite concurrently)
composer dev

# Initial project setup
composer setup

# Run tests
composer test

# Frontend only
npm run dev      # Vite dev server
npm run build    # Production build

# Laravel specific
php artisan serve              # Start development server
php artisan migrate            # Run migrations
php artisan db:seed            # Seed database
php artisan queue:listen       # Process queued jobs
php artisan pint               # Code linting (Laravel Pint)
```

## Architecture

### Backend (Laravel)

**Core Domain Model - Lead/Client Unified Entity:**
- `Lead` model (`app/Models/Lead.php`) is the primary entity
- When `status` becomes 'won', `LeadObserver` automatically sets `is_client = true` and `won_at` timestamp
- Client pages filter leads where `is_client = true`
- Lead statuses: `new_lead`, `initial_outreach`, `follow_ups`, `negotiations`, `won`, `lost`

**API Controllers** (`app/Http/Controllers/Api/`):
- `LeadController` - Full lead management including kanban view, CSV import/export, notes per product
- `ClientController` - Client-filtered views (is_client = true)
- `TaskController` - Task management with upcoming/overdue filtering
- `ActivityController` - Activity logging (calls, emails, meetings, notes)
- `ReportController` - EOD and custom date range report generation
- `DashboardController` - Dashboard stats and overview data

**Services** (`app/Services/`):
- `FollowUpService` - Automated follow-up scheduling (2 days initial, 7 days recurring)
- `ReportService` - Report generation with outreach summaries, engagement updates, won/lost deals

**Authorization:**
- Role-based access: `admin`, `manager`, `team_member`
- `EnsureRole` middleware for route protection
- Policies for `Lead`, `Task`, `User` access control
- Admins see all data, managers see team data, team members see own data

### Frontend (Vue 3 + Inertia.js)

**Pages** (`resources/js/Pages/`):
- `Dashboard.vue` - Main dashboard
- `Pipeline/` - Kanban board and lead management
- `Clients/` - Client database views
- `Tasks/` - Task management
- `Users/` - User management (admin)

**Key Components** (`resources/js/Components/`):
- `KanbanBoard.vue` - Drag-and-drop pipeline view
- `LeadCard.vue` - Lead card for kanban
- `AddLeadModal.vue` - Lead creation form
- `ClientViewModal.vue` - Client detail modal
- `CsvImportModal.vue` - CSV import functionality
- `SalesChart.vue`, `LeadsPerSource.vue`, `ProductsByPurchase.vue` - Dashboard charts

### Database

**Key Tables:**
- `leads` - Primary entity with `is_client` flag, status, value, products relationship
- `lead_product` - Pivot table for lead-product relationships with pipeline fields
- `tasks` - Tasks linked to leads with priority/status
- `activities` - Activity log for leads
- `follow_up_schedules` - Automated follow-up tracking
- `products` - Product catalog
- `users` - Extended with role, manager_id for team hierarchy

### Routes

- **Web routes** (`routes/web.php`) - Inertia page routes
- **API routes** (`routes/api.php`) - JSON API endpoints, authenticated via session
- **Auth routes** (`routes/auth.php`) - Laravel Breeze authentication

## Testing

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

Uses Pest PHP for testing (`tests/` directory).
