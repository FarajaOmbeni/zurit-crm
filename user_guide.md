# Zurit CRM User Guide

## Welcome to Your Sales Success Platform

Meet Alex, a sales representative at Zurit Consulting. This guide follows Alex through a typical day using the Zurit CRM, showing you exactly how to leverage every feature to maximize your sales performance and manage client relationships effectively.

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Your First Morning: The Dashboard](#your-first-morning-the-dashboard)
3. [Managing Leads: The Pipeline](#managing-leads-the-pipeline)
4. [Building Your Client Database](#building-your-client-database)
5. [Staying Organized with Tasks](#staying-organized-with-tasks)
6. [Generating Reports](#generating-reports)
7. [Managing Products](#managing-products)
8. [Team Management (Admin & Manager Features)](#team-management-admin--manager-features)
9. [Understanding User Roles and Permissions](#understanding-user-roles-and-permissions)
10. [Tips for Success](#tips-for-success)

---

## Getting Started

### Logging In for the First Time

It's Monday morning, and Alex received an email with login credentials for the Zurit CRM.

**Step 1: Access the System**
1. Open your web browser and navigate to the Zurit CRM URL
2. You'll see the login page

**Step 2: First-Time Login**
1. Enter your email address (the one you received in the invitation)
2. Enter the temporary password (OTP) sent to your email
3. Click **Log In**

**Step 3: Set Your Password**
1. You'll be prompted to create a new password
2. Enter a strong password (at least 8 characters)
3. Confirm your new password
4. Click **Update Password**

**Step 4: Complete Your Profile** (Optional)
1. Click on your name in the top-right corner
2. Select **Profile**
3. Update your information as needed
4. Click **Save**

You're now ready to start using the CRM!

---

## Your First Morning: The Dashboard

After logging in, Alex lands on the Dashboard - the command center for daily sales activities.

### Understanding Your Dashboard

The Dashboard gives you a complete overview of your sales performance at a glance.

**What You See:**

1. **Snapshot Cards** (Top Section)
   - **Leads Today**: Number of new leads added today
   - **Clients Today**: Number of leads converted to clients today
   - **Revenue Today**: Total sales revenue generated today
   - **Revenue This Month**: Cumulative revenue for the current month
   - **Conversion Rate**: Percentage of leads that become clients

2. **Recent Leads** (Left Column)
   - Shows your most recently added leads
   - Quick access to contact information
   - Visual status indicators

3. **Sales Chart** (Center)
   - Visual representation of your sales trends
   - Shows performance over time
   - Helps identify patterns and opportunities

4. **Tasks Due Today** (Right Column)
   - Lists all tasks with today's deadline
   - Color-coded by priority (High, Medium, Low)
   - Quick checkbox to mark tasks complete

5. **Analytics Section** (Bottom)
   - **Products by Purchase**: Shows which products are selling best
   - **Leads per Source**: Where your leads are coming from (referrals, website, events, etc.)
   - **Calendar Widget**: Quick view of upcoming appointments and deadlines

6. **Recent Activities**
   - Timeline of recent interactions with leads and clients
   - Shows calls, emails, meetings, and notes

**Pro Tip**: Check your Dashboard every morning to prioritize your day. Focus on overdue tasks first, then work on high-priority items.

---

## Managing Leads: The Pipeline

Alex has coffee in hand and is ready to tackle the day's leads. The Pipeline is where all the action happens.

### Selecting Your Product Focus

Before accessing the pipeline, you need to select which product you're working with.

**Step 1: Select Product**
1. From the main menu, click **Pipeline**
2. You'll see the Product Selection screen
3. Choose the product you want to focus on from the list
4. Click **Continue**

**Why This Matters**: Zurit CRM tracks leads separately for each product, allowing you to manage multiple product pipelines simultaneously.

### Understanding the Kanban Board

The Pipeline uses a Kanban board - a visual way to track leads through your sales process.

**The Five Stages:**

1. **New Lead**: Fresh leads that need initial contact
2. **Initial Outreach**: You've made first contact and introduced your services
3. **Follow-ups**: Active conversations and relationship building
4. **Negotiations**: Discussing pricing, terms, and closing details
5. **Won**: Successfully closed deals (these automatically become clients)

**Note**: Lost leads don't appear on the board but are tracked separately in reports.

### Adding a New Lead

Alex just got a referral from a client - time to add them to the CRM!

**Method 1: Add from Pipeline Header**

1. Click the **+ Add Lead** button in the top-right corner
2. You'll be asked to choose the lead type:
   - **Company Contact**: A business or organization (requires company name)
   - **Personal Contact**: An individual (requires person's name)
3. Select the appropriate type

**Method 2: Quick Add from Kanban Column**

1. Click the **+ New Lead** button at the top of the "New Lead" column
2. Same type selection as above

**Step-by-Step: Adding Company Contact**

1. After selecting "Company Contact", fill in the form:
   - **Company Name** (required): Enter the business name
   - **Contact Person**: Name of your primary contact
   - **Position**: Their job title
   - **Email**: Business email address
   - **Phone**: Contact number
   - **City**: Location of the business
   - **Country**: Select from dropdown
   - **Sector**: Industry (e.g., Finance, Healthcare, Technology)
   - **Source**: How you found them (Referral, Website, LinkedIn, Event, etc.)
   - **Expected Close Date**: When you think you'll close the deal
   - **Estimated Value**: Potential deal size in your currency
   - **Notes**: Any important details or context

2. Click **Add Lead**

**Step-by-Step: Adding Personal Contact**

1. After selecting "Personal Contact", fill in the form:
   - **Full Name** (required): The person's name
   - **Position**: Their occupation or title
   - **Company**: Where they work (optional for personal contacts)
   - All other fields are the same as company contacts

2. Click **Add Lead**

**What Happens Next**: The lead appears in the "New Lead" column and is automatically associated with your selected product.

### Importing Multiple Leads at Once

Alex attended a networking event and collected 30 business cards. Instead of entering them one by one, let's import them via CSV.

**Step 1: Prepare Your CSV File**

1. Create a spreadsheet with these columns (in order):
   - Name
   - Position
   - Company
   - Email
   - Phone
   - City
   - Country
   - Source
   - Sector

2. Fill in your lead data (one lead per row)
3. Save as CSV file

**Example CSV:**
```
Name,Position,Company,Email,Phone,City,Country,Source,Sector
John Smith,CEO,Tech Solutions,john@techsolutions.com,+254712345678,Nairobi,Kenya,Event,Technology
Sarah Johnson,CFO,Finance Corp,sarah@financecorp.com,+254723456789,Mombasa,Kenya,Referral,Finance
```

**Step 2: Import to CRM**

1. From the Pipeline or Client Database page, click **Import**
2. Select your contact type (Company or Personal)
3. Click **Choose File** and select your CSV
4. The system will preview your data
5. Click **Confirm Import**

**Step 3: Review Results**

The system will show you:
- **Imported**: Successfully added leads
- **Duplicates Skipped**: Leads with emails or phone numbers that already exist
- **Errors**: Any rows that couldn't be imported (with reasons)

**Pro Tip**: The system automatically detects duplicates based on email and phone number to prevent duplicate entries. If a duplicate is found, it shows you which salesperson already has that lead.

### Working with Leads on the Kanban Board

Alex sees a lead card in the "New Lead" column. Let's explore what you can do.

**Viewing Lead Details**

1. Click on any lead card
2. A detailed modal opens showing:
   - Full contact information
   - Associated products and their pipeline status
   - All activities (calls, emails, meetings, notes)
   - All tasks related to this lead
   - Conversation history

**Moving Leads Between Stages**

There are two ways to move leads:

**Method 1: Drag and Drop**
1. Click and hold on a lead card
2. Drag it to the desired stage column
3. Release to drop
4. The lead's status updates automatically

**Method 2: Update via Lead Details**
1. Open the lead details by clicking the card
2. Click the **Edit** button
3. Change the status dropdown
4. Click **Save**

**Adding Notes to a Lead**

While viewing lead details:
1. Scroll to the **Notes** section
2. Click **Add Note**
3. Enter your note (what was discussed, next steps, etc.)
4. Click **Save**

**Notes are product-specific**: If a lead is interested in multiple products, you can track separate notes for each product.

### Logging Activities

Alex just finished a call with a lead. Here's how to document it:

1. Open the lead details
2. Navigate to the **Activities** tab
3. Click **+ Add Activity**
4. Fill in the activity details:
   - **Type**: Call, Email, Meeting, or Note
   - **Date**: When the activity occurred
   - **Description**: What happened during the interaction
   - **Outcome**: Result of the interaction (interested, needs follow-up, not interested, etc.)
5. Click **Save Activity**

**Why This Matters**: Activity logs create a complete history of your interactions, making it easy to pick up where you left off and provide context for your team.

### Reassigning Leads

Sometimes leads need to be transferred to another team member.

**When You Might Reassign:**
- You're going on vacation
- Another team member has better expertise for this lead
- Territory or account management changes
- Manager redistributing workload

**How to Reassign:**

1. Open the lead details
2. Click the **Reassign** button
3. Select the new owner from the dropdown
   - Managers can assign to any team member
   - Team members can only reassign to their manager or teammates
4. Optionally add a note explaining why
5. Click **Confirm Reassignment**

**What Happens**: The lead moves to the new owner's pipeline, but the system keeps track of the original owner and reassignment history.

### Marking Leads as Won

Success! Alex closed a deal. Here's what happens when you win:

**Step 1: Move to Won**
1. Drag the lead card to the "Won" column, OR
2. Click the lead card, then click **Mark as Won**

**Step 2: Confirm Deal Details**
1. A modal appears asking for:
   - **Deal Value**: Final agreed amount
   - **Close Date**: When the deal was finalized
2. Enter the information
3. Click **Confirm**

**What Happens Automatically:**
- The lead is converted to a client (is_client = true)
- The system records the won_at timestamp
- The lead disappears from the Pipeline board
- The client now appears in the Client Database
- Your revenue statistics update automatically
- The conversion appears in your reports

**Important**: Once a lead is won and becomes a client, they're managed in the Client Database, not the Pipeline.

### Marking Leads as Lost

Not every lead converts, and that's okay. Tracking lost leads helps you learn and improve.

**How to Mark as Lost:**

1. Open the lead details
2. Click **Mark as Lost**
3. Select or enter a reason:
   - Budget constraints
   - Chose competitor
   - Timing not right
   - No longer interested
   - Other (specify)
4. Optionally add notes with more context
5. Click **Confirm**

**What Happens**: The lead is removed from the Pipeline board but retained in the system for reporting and analysis.

### Exporting Your Leads

Alex needs to send a lead list to the marketing team.

**Step 1: Export Data**
1. Click the **Export** button in the Pipeline header
2. Your browser downloads a CSV file automatically

**What's Included**: Name, Position, Company, Email, Phone, City, Country, Source, Sector

**Pro Tip**: Use the exported data for:
- Backing up your lead database
- Analyzing lead sources and patterns
- Sharing with marketing for campaigns
- Reporting to management

### Pipeline Statistics

At the top of your Pipeline page, you'll see key metrics:

- **Total Pipeline Value**: Sum of estimated values for all active leads
- **Total Leads**: Count of active leads (excluding won/lost)
- **Closed This Month**: Number of deals won this month
- **Total This Month**: Pipeline value of leads created this month

**Pro Tip**: Monitor these numbers weekly. If your pipeline value is dropping, focus on lead generation. If leads are piling up in one stage, identify bottlenecks.

---

## Building Your Client Database

After winning deals, Alex's clients are automatically added to the Client Database. This is where you nurture long-term relationships.

### Accessing the Client Database

1. Click **Clients** in the main navigation menu
2. You'll see a comprehensive view of all your clients

### Understanding the Client View

**Header Statistics:**
- **Average Progress**: Overall pipeline health across all leads and clients
- **Active Clients**: Number of won clients with ongoing relationships
- **Total Leads**: Active leads (not yet clients)
- **Completed**: Clients with finalized close dates

**Client List Features:**
- Search bar to find clients by name, company, or email
- Sorting options (Company, Name, Email, Created Date, Won Date)
- Filtering capabilities
- Pagination for easy navigation

### Viewing Client Details

1. Click on any client row
2. A detailed modal opens showing:
   - Complete contact information
   - Products they've purchased
   - Purchase history and values
   - All activities and interactions
   - Associated tasks
   - Notes and conversation history

### Editing Client Information

Clients' needs change over time. Here's how to update their information:

1. Open the client details
2. Click the **Edit** button (top-right of modal)
3. Update any field:
   - Contact information
   - Company details
   - Location
   - Notes
4. Click **Save Changes**

**What You Can Edit**:
- Name and position
- Company information
- Email and phone
- City and country
- Source and sector
- Deal value
- Notes

**What You Cannot Change**:
- Status (they remain clients once won)
- Won date
- Who added them

### Adding Leads from Client Database

You can also add new leads directly from the Client Database:

1. Click **+ Add Lead** button
2. Choose contact type (Company or Personal)
3. Fill in the lead information
4. The lead is created and appears in your Pipeline

### Importing Leads to Client Database

The import process is the same as in the Pipeline:

1. Click **Import**
2. Select contact type
3. Upload your CSV file
4. Review and confirm

### Exporting Client Data

1. Click **Export**
2. A CSV file downloads with all your leads and clients
3. Use this for backup, analysis, or sharing with team members

### Sorting and Filtering

**To Sort Clients:**
1. Click the column header you want to sort by (Company, Name, Email, etc.)
2. Click again to reverse the sort order
3. A small arrow indicates the current sort direction

**To Search:**
1. Type in the search bar at the top
2. The system searches across names, companies, and emails
3. Results update in real-time

**Pro Tip**: Use the search to quickly find a client before a meeting or call.

---

## Staying Organized with Tasks

Alex has a lot to juggle. The Task Management system ensures nothing falls through the cracks.

### Accessing Your Tasks

1. Click **Tasks** in the main navigation
2. You'll see your task management dashboard

### Understanding the Task Dashboard

**Statistics Cards** (Top):
- **Total Tasks**: All tasks you've created or been assigned
- **Pending**: Tasks not yet started or in progress
- **Completed**: Finished tasks
- **Overdue**: Tasks past their due date that aren't completed

**Task List**:
- Visual status indicators (color-coded)
- Priority badges (High, Medium, Low)
- Due dates with overdue warnings
- Associated lead/client information

### Creating a New Task

Alex needs to schedule a follow-up call with a client.

**Step 1: Open Task Creation**
1. Click **+Add Activity** tab button, OR
2. Scroll to the task list and click **+ Add Task**

**Step 2: Fill in Task Details**
1. **Task Title** (required): Brief description (e.g., "Follow up with John Smith")
2. **Description**: Detailed notes about what needs to be done
3. **Type**: Choose from:
   - Call
   - Email
   - Meeting
   - Follow Up
   - Other
4. **Due Date** (required): When the task should be completed
5. **Priority**: Low, Medium, or High
6. **Lead/Client**: Associate with a specific lead or client (optional)
   - Leave empty for internal tasks (e.g., "Update product catalog")
7. Click **Create Task**

**What Happens**: The task appears in your task list and on your Dashboard if it's due today.

### Understanding Task Status

Each task has a visual indicator:

1. **Pending** (Yellow clock icon): Not yet started
2. **Overdue** (Red warning icon): Past due date and not completed
3. **Completed** (Green checkmark): Finished

### Completing a Task

After Alex finishes the follow-up call:

1. Find the task in your list
2. Click the **checkmark** button (right side of the task)
3. The task is marked complete and moves to the completed section

**To Undo**: Click the **undo** button on a completed task to mark it as pending again.

### Editing a Task

Plans change. Here's how to update a task:

1. Find the task you want to edit
2. Click the **Edit** button (pencil icon)
3. Modify any field:
   - Title
   - Description
   - Type
   - Due date
   - Priority
   - Associated lead/client
4. Click **Update Task**

### Deleting a Task

If a task is no longer needed:

1. Click the **Delete** button (trash icon) on the task
2. Confirm the deletion
3. The task is permanently removed

**Warning**: Deletion cannot be undone, so be sure before confirming.

### Searching and Sorting Tasks

**To Search**:
1. Type in the search bar at the top of the task list
2. Search works across task titles and descriptions
3. Results update in real-time

**To Sort**:
1. Click the **Name** button to sort alphabetically by title
2. Click the **Date** button to sort by due date
3. Click again to reverse the sort order

### Filtering by Status

1. Use the tabs at the top:
   - **All Activities**: Shows all tasks
2. The count next to each tab shows how many tasks are in each category

### Task Priorities

Use priorities to focus your efforts:

**High Priority** (Red badge):
- Urgent and important
- Should be completed today
- Examples: Hot lead follow-ups, deadline-driven tasks

**Medium Priority** (Yellow badge):
- Important but not urgent
- Should be completed soon
- Examples: Regular check-ins, administrative tasks

**Low Priority** (Gray badge):
- Can be done when time allows
- No immediate deadline pressure
- Examples: Research, long-term planning

**Pro Tip**: Review your high-priority tasks every morning. Complete at least 2-3 before moving to medium-priority items.

### Internal vs. Lead-Related Tasks

**Lead-Related Tasks**:
- Linked to a specific lead or client
- Show the lead's name and company
- Help track your interaction history

**Internal Tasks**:
- Not linked to any lead
- General to-do items
- Examples: "Update product knowledge", "Review training materials"

### Tasks on the Dashboard

Your Dashboard shows tasks due today. This keeps you focused on immediate priorities without getting overwhelmed by your entire task list.

---

## Generating Reports

It's end of day, and Alex needs to submit a daily report to the manager. The Reports feature makes this easy.

### Accessing Reports

1. Click **Reports** in the main navigation
2. You'll see two tabs:
   - **End of Day Reports**
   - **End of Week Reports**

### Generating an End of Day (EOD) Report

**Step 1: Select Date**
1. Click on the date picker
2. Select the day you want to report on (usually today)
3. You cannot select future dates

**Step 2: Generate Report**
1. Click **Generate Report**
2. Wait a few seconds while the system compiles your data
3. The report appears on screen

**What's Included in EOD Reports:**

1. **Outreach Summary**:
   - Number of leads contacted today
   - List of contacted leads with names

2. **Scheme Engagement Updates** (Table):
   - Contact person names
   - Phone numbers
   - Feedback notes from conversations

3. **Program Sales Update** (Won Deals):
   - Client/lead names who became clients today
   - Products sold
   - Deal amounts in currency

**Step 3: Download as PDF**
1. Click **Download PDF**
2. The system generates a professionally formatted PDF
3. Your browser downloads the file
4. Filename format: `sales-report-yourname-2024-01-15.pdf`

**Step 4: Send to Supervisor**
1. Click **Send to Supervisor**
2. The report is automatically emailed to your manager
3. You'll see a confirmation message

### Generating an End of Week (EOW) Report

Weekly reports give a broader view of your performance.

**Step 1: Select Week**
1. Click the **End of Week Reports** tab
2. Choose from the dropdown:
   - **Current Week**: Monday through Friday of this week
   - **Last Week**: Monday through Friday of last week

**Note**: EOW reports follow a Monday-Friday workweek structure.

**Step 2: Generate Report**
1. Click **Generate Report**
2. The system compiles data for the entire week
3. Report appears on screen

**What's Included in EOW Reports**:
- Same sections as EOD reports, but aggregated across the full week
- All outreach activities for the week
- All engagement updates
- All sales closed during the week
- Weekly totals and summaries

**Step 3: Download or Send**
- Same process as EOD reports
- **Download PDF**: For your records
- **Send to Supervisor**: Automatic email to manager

### Understanding Report Data

**Outreach Summary**:
- Shows your activity level
- Helps managers see you're engaging with leads
- Demonstrates consistent effort

**Engagement Updates**:
- Captures conversation details
- Shows progress with each lead
- Documents what was discussed

**Sales Updates**:
- Tracks revenue generation
- Shows conversion success
- Highlights winning products

**Pro Tip**: Generate your EOD report at the end of each workday. This habit ensures you document everything while it's fresh in your mind.

### Report Best Practices

**For Daily Success**:
1. Log all activities as they happen (don't wait until end of day)
2. Add detailed notes to conversations
3. Update lead statuses immediately after calls/meetings
4. Generate and send reports before logging off

**For Weekly Success**:
1. Review your weekly report every Friday
2. Compare to previous weeks to track improvement
3. Identify patterns (which days are most productive, which products sell best)
4. Set goals for the following week based on insights

---

## Managing Products

As Alex becomes more familiar with the system, they learn that administrators can manage the product catalog.

**Note**: Product management is typically reserved for Admin users. If you're a Team Member or Manager, you can view products but may not edit them.

### Accessing Product Management

1. Click **Products** in the main navigation
2. You'll see the complete product catalog

### Understanding the Product List

**Product Information Displayed**:
- Product name
- Category (if assigned)
- Price
- Status (Active or Inactive)
- Creation date
- Description

### Creating a New Product

**Admin users only**:

**Step 1: Open Creation Form**
1. Click **Create Product**

**Step 2: Fill in Product Details**
1. **Product Name** (required): Clear, descriptive name
2. **Price** (required): Standard selling price
3. **Category**: Group products by type (e.g., "Retirement Plans", "Investment Products")
4. **Description**: Detailed information about the product
5. **Active**: Check this box to make the product available for selection in leads

**Step 3: Save**
1. Click **Create Product**
2. The product appears in your catalog

### Editing a Product

1. Find the product in the list
2. Click the **Edit** button
3. Modify any field
4. Click **Update Product**

**Common Edits**:
- Updating prices
- Refining descriptions
- Changing categories
- Activating or deactivating products

### Deactivating vs. Deleting Products

**Deactivate** (Recommended):
- Uncheck the "Active" box
- Product remains in system
- Historical data preserved
- Can be reactivated later
- Won't appear in lead creation dropdowns

**Delete**:
- Permanently removes the product
- Use only if product was created in error
- Cannot be undone

**Pro Tip**: Never delete products that have been associated with leads or clients. Always deactivate instead.

### Searching Products

1. Type in the search bar
2. Search works across names, categories, and descriptions
3. Results filter in real-time

### How Products Relate to Leads

**Product Association**:
- When creating a lead, you can select which products they're interested in
- If no products are selected, the lead is associated with all active products
- Each product has its own pipeline status for the lead
- This allows tracking a single lead's interest in multiple products separately

**Example**: A lead might be at "Negotiations" stage for Product A, but only at "Initial Outreach" for Product B.

---

## Team Management (Admin & Manager Features)

Alex has been promoted to Manager! Now they can manage a team and access additional features.

**Note**: This section applies to Admin and Manager roles only.

### Accessing User Management

1. Click **Users** in the main navigation
2. You'll see a list of all users you can manage

**What You See**:
- **Admins see**: All users in the organization
- **Managers see**: Only their direct team members

### Understanding User Roles

Zurit CRM has three user roles with different permissions:

**Admin**:
- Full system access
- Can create and manage all users
- Can assign managers to team members
- Can create and modify products
- Sees all leads, clients, tasks, and reports across the organization
- Can reassign any lead to anyone

**Manager**:
- Can create and manage team members
- Can create and modify products
- Sees only their own data and their team's data
- Can reassign leads within their team
- Can view team member reports and performance

**Team Member**:
- Can manage their own leads and clients
- Can create tasks and log activities
- Sees only their own data
- Can reassign leads to their manager or teammates
- Can generate their own reports

### Creating a New User

**For Admins**:

**Step 1: Open User Creation**
1. Click **Create User** button

**Step 2: Fill in User Information**
1. **Name**: Full name of the new user
2. **Email**: Their work email address (will be their username)
3. **Role**: Select Admin, Manager, or Team Member
4. **Assign to Manager**: If creating a Team Member, choose their manager
   - This is optional but recommended for proper data visibility
5. **Active**: Check to activate the account immediately

**Step 3: Save**
1. Click **Create User**
2. System generates a temporary password (OTP)
3. OTP is automatically emailed to the new user
4. They'll be prompted to change it on first login

**For Managers**:
- Same process, but role is automatically set to "Team Member"
- Can only assign team members to themselves

### Managing OTP (One-Time Passwords)

**If User Hasn't Logged In**:
- The OTP expires after a certain period
- If expired, you'll see an **Resend OTP** button next to their name
- Click it to generate and send a new OTP

**First Login Flow for New Users**:
1. User receives email with OTP
2. They log in with email + OTP
3. System forces password change
4. They set their permanent password
5. They can now use the system normally

### Team Hierarchy and Data Visibility

**How It Works**:
- Team Members see only their own leads, clients, and tasks
- Managers see their own data + all their team members' data
- Admins see everything across the entire organization

**Example Hierarchy**:
```
Admin (Sarah)
├── Manager (Alex)
│   ├── Team Member (John)
│   └── Team Member (Mary)
└── Manager (David)
    ├── Team Member (Lisa)
    └── Team Member (Tom)
```

**What Each User Sees**:
- Sarah (Admin): Everything from everyone
- Alex (Manager): Own data + John's + Mary's data
- John (Team Member): Only own data
- David (Manager): Own data + Lisa's + Tom's data

### Reassigning Leads as a Manager

Managers can redistribute workload within their team:

**Step 1: Open Lead Details**
1. Navigate to any lead (yours or a team member's)
2. Open the lead details

**Step 2: Reassign**
1. Click **Reassign**
2. Select the new owner (must be a team member or yourself)
3. Optionally add a note
4. Click **Confirm**

**Best Practices for Reassigning**:
- Communicate with team members before reassigning
- Add context notes explaining the reassignment
- Balance workload across team fairly
- Consider each person's expertise with specific products or industries

### Viewing Team Performance

**As a Manager, you can**:
- View individual team member dashboards
- See their pipeline statistics
- Review their reports
- Monitor task completion rates
- Track conversion rates

**How to View**:
1. The system automatically includes team data in your dashboard
2. Reports show combined team performance
3. Pipeline shows leads from your entire team

**Pro Tip**: Have weekly one-on-ones with team members to review their pipeline and offer coaching.

---

## Understanding User Roles and Permissions

This section provides a quick reference for what each role can do.

### Feature Access Matrix

| Feature | Team Member | Manager | Admin |
|---------|-------------|---------|-------|
| View own leads/clients | ✅ | ✅ | ✅ |
| View team leads/clients | ❌ | ✅ | ✅ |
| View all leads/clients | ❌ | ❌ | ✅ |
| Create leads | ✅ | ✅ | ✅ |
| Edit own leads | ✅ | ✅ | ✅ |
| Edit team leads | ❌ | ✅ | ✅ |
| Delete own leads | ✅ | ✅ | ✅ |
| Delete team leads | ❌ | ✅ | ✅ |
| Reassign to teammates | ✅ | ✅ | ✅ |
| Reassign anyone's leads | ❌ | Team only | ✅ |
| Create tasks | ✅ | ✅ | ✅ |
| View own tasks | ✅ | ✅ | ✅ |
| View team tasks | ❌ | ✅ | ✅ |
| Generate own reports | ✅ | ✅ | ✅ |
| View team reports | ❌ | ✅ | ✅ |
| Create products | ❌ | ❌ | ✅ |
| Edit products | ❌ | ❌ | ✅ |
| Create users | ❌ | Team Members only | All users |
| Manage users | ❌ | Own team only | All users |
| Import/Export leads | ✅ | ✅ | ✅ |

### Permission Details

**Lead and Client Management**:
- Everyone can manage their own leads and clients
- Managers can manage their team's leads and clients
- Admins can manage all leads and clients organization-wide

**Task Management**:
- Tasks can be associated with leads
- You can see tasks for any lead you have access to
- You can create tasks for your own leads
- Managers can create tasks for team leads

**Reporting**:
- Everyone generates reports based on their own data
- Manager reports include team data aggregated
- Admin reports can show organization-wide data

**Product Management**:
- Only Admins can create, edit, or delete products
- Everyone can view active products when creating leads
- Inactive products don't appear in lead creation dropdowns

**User Management**:
- Team Members cannot create or manage users
- Managers can create Team Members and assign them to themselves
- Admins have full user management capabilities

---

## Tips for Success

Based on Alex's journey, here are proven strategies for maximizing your Zurit CRM experience:

### Daily Habits

**Morning Routine** (15 minutes):
1. Check Dashboard for snapshot of yesterday's performance
2. Review tasks due today
3. Prioritize high-value activities
4. Plan your outreach for the day

**Throughout the Day**:
1. Log activities immediately after calls/meetings
2. Move leads on Kanban board as soon as status changes
3. Add notes while details are fresh
4. Create tasks for follow-ups right away

**End of Day** (10 minutes):
1. Review all activities logged today
2. Generate and send EOD report
3. Check tomorrow's tasks
4. Update any lead statuses

### Lead Management Best Practices

**Quality Over Quantity**:
- Add complete information for each lead
- Include context in notes
- Update status regularly
- Don't let leads sit in one stage too long

**Follow-Up Discipline**:
- Create follow-up tasks immediately after every interaction
- Set realistic due dates (2-3 days for hot leads, 7 days for warm leads)
- Never close a call without scheduling the next touchpoint

**Pipeline Hygiene**:
- Review your entire pipeline weekly
- Move or mark as lost any stale leads (30+ days with no activity)
- Keep notes updated with latest conversation points
- Use the notes field to document objections and concerns

### Task Management Best Practices

**Prioritization**:
- High Priority: Hot leads, today's deadlines, urgent requests
- Medium Priority: Warm leads, this week's goals, important but not urgent
- Low Priority: Research, long-term planning, nice-to-haves

**Time Blocking**:
- Block 9-11 AM for high-priority tasks
- Block 2-4 PM for follow-up calls
- Block end of day for administrative tasks (logging, reporting)

**Task Completion**:
- Focus on completing tasks, not just starting them
- Check off completed tasks immediately for motivation
- Review completed tasks weekly to see your progress

### Client Relationship Best Practices

**First 90 Days After Win**:
- Schedule check-in calls at 30, 60, and 90 days
- Create tasks to ensure consistent touchpoints
- Ask for referrals once value is demonstrated
- Log all interactions to build relationship history

**Long-Term Nurturing**:
- Quarterly check-ins minimum
- Share relevant industry insights via email
- Remember important dates (anniversaries, renewals)
- Look for upsell opportunities

### Reporting Best Practices

**Daily**:
- Generate EOD reports consistently
- Don't skip days (it builds a performance record)
- Use reports to reflect on what worked/didn't work
- Share wins with your team or manager

**Weekly**:
- Review EOW reports for patterns
- Identify your most productive days
- Track which lead sources convert best
- Set specific goals for the next week

**Monthly**:
- Compare your metrics month-over-month
- Celebrate improvements
- Identify areas for development
- Share success stories with the team

### Data Quality Best Practices

**Complete Information**:
- Fill in all fields when creating leads (better for reporting)
- Use consistent naming conventions
- Select accurate lead sources
- Keep city and country data clean

**Deduplication**:
- Search before adding new leads to avoid duplicates
- Use the CSV import feature carefully (it checks for duplicates)
- If you find duplicates, keep the one with the most complete data

**Regular Cleanup**:
- Weekly: Mark clearly lost leads as lost (don't let them linger)
- Monthly: Review and update outdated contact information
- Quarterly: Archive very old leads that never converted

### Team Collaboration (for Managers)

**Communication**:
- Share best practices from top performers
- Hold weekly pipeline review meetings
- Celebrate wins publicly
- Coach on losses privately

**Workload Management**:
- Monitor team pipelines for balance
- Reassign leads if someone is overwhelmed
- Ensure fair distribution of hot leads
- Consider expertise when assigning

**Performance Tracking**:
- Review team member reports weekly
- Identify coaching opportunities
- Recognize high performers
- Support struggling team members

### Security and Account Management

**Password Security**:
- Use a strong, unique password
- Don't share your login credentials
- Change password if you suspect compromise
- Log out when leaving your computer

**Data Privacy**:
- Don't share client data outside the CRM
- Be careful with exported CSV files (they contain sensitive data)
- Follow your organization's data handling policies
- Report any suspected security issues immediately

**Session Management**:
- The system will log you out after period of inactivity
- Save your work frequently
- Don't leave the CRM open on shared computers

### Getting Help

**If You're Stuck**:
1. Check this user guide first
2. Ask a colleague or manager
3. Contact your system administrator
4. Document the issue with screenshots if reporting a problem

**Feature Requests**:
- Share ideas with your manager or admin
- Explain how the feature would improve your workflow
- Provide specific examples of the use case

### Keyboard Shortcuts and Efficiency Tips

**Navigation**:
- Use the main menu to jump between sections quickly
- Use browser back button if you need to return to previous page
- Bookmark frequently used pages

**Data Entry**:
- Use Tab key to move between form fields quickly
- Use Enter to submit forms (instead of clicking Submit)
- Copy/paste information when appropriate (but avoid duplicates)

**Search and Filter**:
- Use search before browsing long lists
- Combine search with sorting for best results
- Clear search to see full lists again

---

## Quick Reference Guide

### Common Actions Cheat Sheet

**Add a New Lead:**
Pipeline → + Add Lead → Choose Type → Fill Form → Save

**Move Lead in Pipeline:**
Drag lead card to new column OR Click lead → Edit → Change status → Save

**Log an Activity:**
Click lead → Activities tab → + Add Activity → Fill details → Save

**Create a Task:**
Tasks page → +Add Activity → Fill form → Create Task

**Generate EOD Report:**
Reports → End of Day → Select date → Generate Report → Download/Send

**Import Leads:**
Pipeline/Clients → Import → Choose Type → Upload CSV → Confirm

**Export Leads:**
Pipeline/Clients → Export → File downloads automatically

**Reassign a Lead:**
Click lead → Reassign → Select new owner → Confirm

**Mark Lead as Won:**
Drag to Won column OR Click lead → Mark as Won → Enter details → Confirm

**Mark Lead as Lost:**
Click lead → Mark as Lost → Select reason → Confirm

**View Client Details:**
Clients → Click client row → Modal opens

**Create New User (Admin/Manager):**
Users → Create User → Fill form → Create User

---

## Frequently Asked Questions

**Q: What happens when I mark a lead as "Won"?**
A: The lead automatically becomes a client, disappears from your Pipeline, and appears in the Client Database. The system records the conversion date and updates your revenue statistics.

**Q: Can I see leads from other team members?**
A: It depends on your role. Team Members see only their own leads. Managers see their team's leads. Admins see all leads organization-wide.

**Q: How do I handle a lead interested in multiple products?**
A: Associate the lead with all relevant products when creating them. Each product has its own pipeline status, so you can track different progress for each product.

**Q: What if I accidentally delete something important?**
A: Contact your administrator immediately. Some deletions can be recovered from backups, but prevention is best - always double-check before confirming deletions.

**Q: Can I customize the pipeline stages?**
A: No, the five stages (New Lead, Initial Outreach, Follow-ups, Negotiations, Won) are standard. However, you can use notes and custom statuses within each stage to add more detail.

**Q: How far back does reporting go?**
A: Reports can be generated for any date from when the system was implemented forward. Historical data is preserved indefinitely.

**Q: What happens to my leads if I'm reassigned to a different manager?**
A: Your leads stay with you. The manager assignment affects which manager can see your data, but doesn't change lead ownership.

**Q: Can I recover a lead I marked as lost?**
A: Currently, lost leads are removed from the active pipeline. Contact your administrator if you need to recover a lost lead.

**Q: Why am I asked to select a product before viewing the Pipeline?**
A: Zurit CRM tracks pipeline progress separately for each product. This allows detailed, product-specific reporting and prevents confusion when leads are interested in multiple products.

**Q: What's the difference between "Clients" and leads with status "Won"?**
A: They're the same thing. When a lead's status becomes "Won," they automatically become a client (is_client = true) and move from the Pipeline to the Client Database.

---

## Glossary of Terms

**Activity**: A logged interaction with a lead or client (call, email, meeting, note).

**Client**: A lead that has been successfully converted (status = Won). Clients appear in the Client Database.

**CSV**: Comma-Separated Values file format used for importing/exporting lead data.

**Dashboard**: The home page showing overview statistics and key information.

**EOD Report**: End of Day Report - daily summary of activities and sales.

**EOW Report**: End of Week Report - weekly summary covering Monday through Friday.

**Kanban Board**: Visual board with columns representing pipeline stages.

**Lead**: A potential customer being tracked through the sales process.

**Lost Lead**: A lead that did not convert to a client.

**Manager**: User role with team management capabilities.

**OTP**: One-Time Password sent to new users for first login.

**Pipeline**: The visual representation of your sales process from new lead to won deal.

**Product**: A service or offering that can be sold to leads/clients.

**Reassignment**: Transferring a lead from one salesperson to another.

**Status**: The current stage of a lead in the pipeline (New Lead, Initial Outreach, Follow-ups, Negotiations, Won, Lost).

**Task**: A to-do item with a due date, priority, and optional lead association.

**Team Member**: Standard user role with access to own leads and clients only.

**Won Lead**: A lead that has been successfully converted to a paying client.

---

## Conclusion

Congratulations! You now have a complete understanding of the Zurit CRM system. Like Alex, you're equipped to:

- Manage your leads effectively through the entire sales cycle
- Build and nurture long-term client relationships
- Stay organized with task management
- Generate professional reports to demonstrate your performance
- Work collaboratively with your team

**Remember**: The CRM is a tool to make your job easier, not harder. The more consistently you use it, the more valuable it becomes. Start with the basics:

1. Add leads as you get them
2. Log activities immediately
3. Move leads through the pipeline
4. Complete tasks on time
5. Generate daily reports

Master these fundamentals, and you'll be a CRM power user in no time.

**Welcome to more organized, more successful selling with Zurit CRM!**

---

**Document Version**: 1.0
**Last Updated**: December 2024
**For Support**: Contact your system administrator

---
