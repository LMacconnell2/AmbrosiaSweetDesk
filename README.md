# Ambrosia SweetDesk

**Ambrosia SweetDesk** is a modern, lightweight help desk and ticket management plugin for WordPress. It is designed to provide businesses with a centralized platform for managing customer support requests, tracking employee performance, organizing clients and teams, and generating actionable analytics—all from within the WordPress admin dashboard.

Unlike traditional help desk systems that rely on third-party SaaS platforms, Ambrosia SweetDesk is designed to operate entirely within WordPress, using the site's own database and authentication system while remaining modular and highly extensible.

---

# Developers
- Logan MacConnell
- Matthew C.

# Features

## Ticket Management

The ticketing system is the core of Ambrosia SweetDesk.

Current capabilities include:

- Create tickets
- Edit tickets
- Delete tickets
- Search tickets
- Filter tickets
- Sort tickets
- Pagination
- Assign tickets to staff members
- Prioritize tickets
- Track ticket status
- Store ticket descriptions
- View ticket conversations
- Support custom ticket metadata
- Support attachments (database layer complete)

### Ticket Fields

Each ticket contains:

- Client
- Assigned employee
- Created by
- Title
- Description
- Status
- Priority
- Source
- Due date
- Resolution date
- Creation timestamp
- Last updated timestamp

---

## Ticket Conversations

Every ticket contains a threaded message history.

Each reply stores:

- Author
- Reply type
- Visibility
- Message body
- Parent reply
- Edited status
- Creation date
- Update date

The architecture already supports:

- Public replies
- Internal notes
- Nested replies
- Future custom reply types

---

## Clients

Clients can be managed directly inside WordPress.

Current functionality:

- Create clients
- Edit clients
- Delete clients
- Search clients
- Import clients
- Export clients

Client records include:

- Name
- Email
- Phone
- Website
- Notes
- Custom metadata

Clients automatically display:

- Total tickets
- Open ticket count

---

## People

Ambrosia SweetDesk separates **People** from WordPress users.

A person may be:

- Internal employee
- Customer
- Contractor
- Manager

This allows tickets to remain associated with historical people even if WordPress accounts change.

People support:

- First name
- Last name
- Email
- Avatar
- Active status
- Role
- WordPress account association
- Custom metadata

---

## Teams

Employees can belong to one or more teams.

Current features:

- Create teams
- Edit teams
- Delete teams
- Search teams

Each team stores:

- Name
- Color
- Description
- Metadata
- Team membership

Future plans include:

- Team analytics
- Team dashboards
- Team assignment rules
- Team notifications

---

# Analytics Dashboard

Ambrosia SweetDesk includes an analytics dashboard designed to provide insight into support performance.

Current metrics include:

## Tickets

Company-wide:

- Tickets received
- Tickets cleared
- Average received per employee
- Average cleared per employee

Individual:

- Tickets assigned
- Tickets cleared

---

## Resolution Metrics

- Average resolution time
- Average resolution time per employee
- Fastest resolved ticket
- Slowest resolved ticket

---

## Customer Feedback

Each ticket may receive a customer satisfaction score.

Metrics include:

- Average score
- Average score per employee
- Highest-rated ticket
- Lowest-rated ticket

---

## Action Items

Dashboard highlights:

- Oldest unresolved tickets
- Recent ticket messages

---

## Exporting

Analytics may be exported as:

- JSON
- CSV

The export system intentionally reuses the same service methods used by the dashboard to guarantee consistency.

---

# REST API

Ambrosia SweetDesk is built around a REST API.

Every major feature communicates through custom WordPress REST endpoints.

Current endpoint groups include:

```
Tickets
Clients
People
Teams
Analytics
Messages
```

The frontend communicates exclusively through these endpoints.

---

# Database Architecture

Ambrosia SweetDesk uses a normalized relational database structure.

Major tables include:

```
sweetdesk_tickets
sweetdesk_ticket_messages
sweetdesk_ticket_feedback
sweetdesk_ticket_activity
sweetdesk_ticket_meta

sweetdesk_clients
sweetdesk_client_meta

sweetdesk_people
sweetdesk_people_meta
sweetdesk_people_teams

sweetdesk_teams
sweetdesk_team_meta

sweetdesk_attachments
```

The design intentionally separates frequently-changing metadata into dedicated meta tables to allow future expansion without requiring schema changes.

---

# Plugin Architecture

The plugin follows a modular architecture.

```
Plugin
│
├── Admin
│     ├── Views
│     ├── CSS
│     ├── JavaScript
│
├── API
│     ├── Routes
│     ├── Controllers
│     ├── Services
│
├── Database
│     ├── Schema
│     ├── Installation
│     ├── Updates
│
├── Helpers
├── Utilities
└── Assets
```

Each REST endpoint follows the same layered pattern:

```
Route
    ↓
Controller
    ↓
Service
    ↓
Database
```

### Routes

Routes define:

- URL
- HTTP method
- Permissions
- Parameter validation

Routes contain no business logic.

---

### Controllers

Controllers are responsible for:

- Sanitization
- Validation
- Permission checking
- Calling services
- Returning REST responses

Controllers remain intentionally thin.

---

### Services

Services contain all business logic.

Examples include:

- Database queries
- Analytics calculations
- Ticket creation
- Ticket updates
- Export generation

This separation makes the plugin significantly easier to test and maintain.

---

# Frontend

The admin interface is built using:

- Vanilla JavaScript
- Modern CSS
- Responsive layouts
- REST API requests
- Dynamic rendering

Current pages include:

- Dashboard
- Tickets
- Clients
- People
- Teams
- Analytics
- Settings

All pages are designed to load data asynchronously from the REST API.

---

# Security

Ambrosia SweetDesk follows standard WordPress security practices.

Current protections include:

- REST nonces
- Permission callbacks
- Input sanitization
- Output escaping
- Prepared SQL statements
- Capability checks

Business logic is isolated from routing to reduce attack surface.

---

# Performance

Database queries make extensive use of:

- Indexed columns
- Composite indexes
- Pagination
- Filtered retrieval
- Lazy loading

Analytics calculations are performed in SQL wherever practical to reduce PHP processing.

---

# Planned Features

The architecture has been designed to support future expansion.

Planned additions include:

## Workflow

- Custom ticket statuses
- SLA management
- Ticket automation
- Assignment rules
- Saved filters

## Notifications

- Email notifications
- Internal notifications
- Slack integration
- Discord integration

## Customer Portal

- Public ticket submission
- Customer login
- Ticket history
- File uploads

## Analytics

- Team dashboards
- Trend graphs
- Historical reporting
- Forecasting
- Custom reports

## Administration

- Debug mode
- Custom permissions
- Role management
- Plugin settings
- Import/export improvements

---

# Design Goals

Ambrosia SweetDesk is built around several core principles:

- Fast
- Modular
- Extensible
- Secure
- WordPress-native
- API-first
- Easy to maintain

Rather than relying on large frameworks, Ambrosia SweetDesk uses WordPress' native APIs wherever possible while maintaining a clean separation between presentation, business logic, and persistence.

The long-term goal is to provide a professional-grade help desk solution that feels like a natural extension of WordPress while remaining lightweight, highly customizable, and developer-friendly.
