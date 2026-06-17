# GET /wp-json/sweetdesk/v1/tickets
    ?status=open
    &priority=urgent
    &assigned_to=4
    &search=login
    &page=2
    &per_page=25
    &sort=created_at
    &order=desc
#### JSON example: 
{
  "data": [],
  "pagination": {
    "page": 1,
    "per_page": 25,
    "total": 1000,
    "total_pages": 40
  },
  "filters": {
    "status": "open",
    "priority": "urgent"
  },
  "sorting": {
    "sort": "created_at",
    "order": "desc"
  }
}

#### SQL Example: 
-- Dynamic where conditions: 
$where = [];
$values = [];

if ($status) {
    $where[] = "t.status = %s";
    $values[] = $status;
}

if ($priority) {
    $where[] = "t.priority = %s";
    $values[] = $priority;
}
-- Build search conditions: 
if ($search) {

    $search_term =
        '%' . $wpdb->esc_like($search) . '%';

    $where[] = "(t.title LIKE %s)";

    $values[] = $search_term;
}

-- Build the query:
$sql = "
SELECT
    t.id,
    t.title,
    t.status,
    t.priority,
    t.created_at,
    t.updated_at

FROM {$wpdb->prefix}sweetdesk_tickets t
";

-- Ad the where:
if (!empty($where)) {

    $sql .= " WHERE " .
        implode(" AND ", $where);
}

-- Whitelist sort:
$allowed_sorts = [
    'created_at',
    'updated_at',
    'priority',
    'status'
];

-- Pagination: 
$offset = ($page - 1) * $per_page;

$sql .= " LIMIT %d OFFSET %d";

$values[] = $per_page;
$values[] = $offset;

## The above route handles basic ticket searching. It will return an array of tickets, accessing the following tables:
- sweetdesk_tickets
### The fields to be returned via this API route are as follows: 
From sweetdesk_tickets:
- id (pk)
- client_id (fk)
- assigned_to (fk)
- created_by (fk)
- title
- status
- priority
- created_at
- updated_at

# POST /wp-json/sweetdesk/v1/tickets
## This route handles the creation of a new ticket. It only returns a status of success or failure, adding the following data to tables:
- sweetdesk_tickets
- sweetdesk_ticket_meta
### The fields to be added to via this API route are as follows: 
To swetdesk_tickets:
- id
- client_id
- assigned_to
- created_by
- title
- status
- priority
- created_at
- updated_at

To sweetdesk_ticket_meta: *Note: We will need to grab the list of sweetdesk_ticket_meta and use those to insert rows into the table.
- meta_id
- ticket_id
- meta_key
- meta_value
*Note, a new row will be added for each custom field found in sweetdesk_ticket_meta. If that data does not exist, allow for null values.

To sweetdesk_ticket_messages:
- reply_id
- ticket_id
- person_id *Note that we will be getting the person data from the sweetdesk_people table.
- reply_type *Some reply types can only be seen by admins, while others may only be visible to employees, while others are completely public. Eventually, I want users to be able to define their reply types and who can see them. For now however, the only reply types will be public and internal.
- body
- is_internal
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

### Expected Request: 
{
  "client_id": 12,
  "assigned_to": 4,
  "created_by": 2,
  "title": "Unable to login",
  "status": "open",
  "priority": "urgent",
  "message": "Customer cannot access account.",
  "custom_fields": {
    "browser": "Chrome",
    "operating_system": "Windows 11",
    "subscription_level": "Pro"
  }
}

#### Example Response:
{
  "success": true,
  "ticket_id": 42,
  "message": "Ticket created successfully."
}

# PUT /wp-json/sweetdesk/v1/tickets/:id
## This route handles editing tickets. Any field can be edited aside from the ticket id. Update the following tables:
- sweetdesk_tickets
- sweetdesk_ticket_meta
### The data to be updated is as follows: 
To sweetdesk_tickets: (use the id of the ticket.)
- client_id
- assigned_to
- created_by
- title
- status
- priority
- updated_at

To sweetdesk_ticket_meta: *Note: We will need to grab the list of sweetdesk_ticket_meta and use those to insert rows into the table.
(Use meta_id and ticket_id to grab the correct field.)
- meta_key
- meta_value

#### Expected request:
{
  "client_id": 15,
  "assigned_to": 6,
  "created_by": 2,
  "title": "Customer cannot login",
  "status": "in_progress",
  "priority": "high",
  "custom_fields": {
    "browser": "Firefox",
    "operating_system": "Windows 11"
  }
}

#### Example Response:
{
  "success": true,
  "ticket_id": 42,
  "message": "Ticket updated successfully."
}

# PUT /wp-json/sweetdesk/v1/tickets/:id/assignee
## This route handles updating the assignee of a ticket quickly. Update the following table:
- sweetdesk_tickets
### Change the following value:
- assigned_to
#### Expected Request: 
{
    "assigned_to": 7
}

# PUT /wp-json/sweetdesk/v1/tickets/:id/status
## This route handles updating the status of a ticket quickly. Update the following table:
- sweetdesk_tickets
### Change the following value:
- status
#### Expected Request:
{
    "status": "closed"
}

# DELETE /wp-json/sweetdesk/v1/tickets/:id
## This route handles the removal of tickets. All data related to the ticket will be removed.

# GET /wp-json/sweetdesk/v1/tickets/:id
## This route handles the retrieval of a ticket based on its ID. It will pull information from the following tables:
- sweetdesk_tickets
- sweetdesk_ticket_meta
- sweetdesk_ticket_messages
### Retrieve the following information from the above tables:
- client_id
- assigned_to
- created_by
- title
- status
- priority
- created_at
- updated_at
Note* the following values for each custom field determined by the ticket's id
- meta_id
- meta_key
- meta_value
Note* the following values for each message determined by the ticket's id.
From sweetdesk_ticket_messages:
- reply_id
- ticket_id
- person_id *Note that we will be getting the person data from the sweetdesk_people table.
- reply_type *Some reply types can only be seen by admins, while others may only be visible to employees, while others are completely public. Eventually, I want users to be able to define their reply types and who can see them. For now however, the only reply types will be public and internal.
- body
- is_internal
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

#### Example Request:
GET /wp-json/sweetdesk/v1/tickets/42 (No request body needed)

### Example Response:
{
  "ticket": {
    "id": 42,
    "client_id": 12,
    "assigned_to": 4,
    "created_by": 2,
    "title": "Unable to login",
    "status": "open",
    "priority": "urgent",
    "created_at": "2026-06-10 14:22:11",
    "updated_at": "2026-06-10 14:22:11"
  },

  "custom_fields": [
    {
      "meta_id": 1,
      "ticket_id": 42,
      "meta_key": "browser",
      "meta_value": "Chrome"
    },
    {
      "meta_id": 2,
      "ticket_id": 42,
      "meta_key": "operating_system",
      "meta_value": "Windows 11"
    }
  ],

  "messages": [
    {
      "reply_id": 1,
      "ticket_id": 42,
      "person_id": 2,
      "reply_type": "public",
      "body": "Customer cannot access account.",
      "visibility": "public",
      "edited": 0,
      "reply_to_id": null,
      "created_at": "2026-06-10 14:22:11",
      "updated_at": "2026-06-10 14:22:11"
    }
  ]
}

# GET /wp-json/sweetdesk/v1/tickets/export
## This route handles the retrieval of all tickets in the given search and exportation of that data into a json file.
- sweetdesk_tickets
- sweetdesk_ticket_meta
- sweetdesk_ticket_messages
### Retrieve the following information from the above tables:
- ticket_id
- client_id
- assigned_to
- created_by
- title
- status
- priority
- created_at
- updated_at
Note* the following values for each custom field determined by the ticket's id
- meta_id
- meta_key
- meta_value
Note* the following values for each message determined by the ticket's id.
From sweetdesk_ticket_messages:
- reply_id
- ticket_id
- person_id *Note that we will be getting the person data from the sweetdesk_people table.
- reply_type *Some reply types can only be seen by admins, while others may only be visible to employees, while others are completely public. Eventually, I want users to be able to define their reply types and who can see them. For now however, the only reply types will be public and internal.
- body
- is_internal
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

#### Expected request:
GET /wp-json/sweetdesk/v1/tickets/export
    ?start_date=2026-01-01
    &end_date=2026-06-30

#### Expected Response:
{
  "exported_at": "2026-06-15 12:00:00",
  "start_date": "2026-01-01",
  "end_date": "2026-06-30",
  "total_tickets": 2,
  "tickets": [
    {
      "ticket": {},
      "custom_fields": [],
      "messages": []
    }
  ]
}

### Future additions:
?download=1

# POST /wp-json/sweetdesk/v1/tickets/import
## This route handles the importation of all tickets in a json file into the database.
- sweetdesk_tickets
- sweetdesk_ticket_meta
- sweetdesk_ticket_messages
### Send the following information to the above tables:
- ticket_id
- client_id
- assigned_to
- created_by
- title
- status
- priority
- created_at
- updated_at
Note* the following values for each custom field determined by the ticket's id
- meta_id
- meta_key
- meta_value
Note* the following values for each message determined by the ticket's id.
From sweetdesk_ticket_messages:
- reply_id
- ticket_id
- person_id *Note that we will be getting the person data from the sweetdesk_people table.
- reply_type *Some reply types can only be seen by admins, while others may only be visible to employees, while others are completely public. Eventually, I want users to be able to define their reply types and who can see them. For now however, the only reply types will be public and internal.
- body
- is_internal
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

#### Expected request:
POST /wp-json/sweetdesk/v1/tickets/import
Content-Type: application/json

{
  "exported_at": "2026-06-15 12:00:00",
  "start_date": "2026-01-01",
  "end_date": "2026-06-30",
  "total_tickets": 2,
  "tickets": [
    {
      "ticket": {
        "ticket_id": 42,
        "client_id": 5,
        "assigned_to": 3,
        "created_by": 1,
        "title": "Cannot login",
        "status": "open",
        "priority": "urgent",
        "created_at": "2026-06-01 08:00:00",
        "updated_at": "2026-06-01 08:00:00"
      },
      "custom_fields": [],
      "messages": []
    }
  ]
}

# POST /wp-json/sweetdesk/v1/tickets/:id/messages
# This route handles the creation of a reply to a ticket. Edit the following tables:
- sweetdesk_ticket_messages
- sweetdesk_tickets
### Send the following data to the above tables:
- client_id
- assigned_to
- status
- priority
- updated_at
Eventually, I want users to be able to define their reply types and who can see them. For now however, the only reply types will be public and internal.
- body
- visibility
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

#### Request Example:
{
  "body": "Have you tried clearing your browser cache?",
  "reply_type": "public",
  "visibility": "public",
  "reply_to_id": null,

  "client_id": 15,
  "assigned_to": 4,
  "status": "pending",
  "priority": "normal"
}

### Response Example:
{
  "success": true,
  "message_id": 82,
  "ticket_id": 14,
  "message": "Reply created successfully."
}

# GET /wp-json/sweetdesk/v1/tickets/:id/messages
## This route handles the retrieval of all messages based on the given ticket ID. Retrieve from the following table:
- sweetdesk_ticket_messages
### Retrieve the following data: 
- body
- visibility
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

#### Example Request:
GET /wp-json/sweetdesk/v1/tickets/42/messages

#### Example Response:
{
    "success": true,
    "ticket_id": 42,
    "total_messages": 3,
    "messages": [
        {
            "reply_id": 1,
            "body": "Customer reports login issue.",
            "visibility": "public",
            "edited": 0,
            "reply_to_id": null,
            "created_at": "2026-06-10 09:00:00",
            "updated_at": "2026-06-10 09:00:00"
        },
        {
            "reply_id": 2,
            "body": "Asked customer for browser details.",
            "visibility": "internal",
            "edited": 0,
            "reply_to_id": 1,
            "created_at": "2026-06-10 09:15:00",
            "updated_at": "2026-06-10 09:15:00"
        },
        {
            "reply_id": 3,
            "body": "Customer confirmed Chrome browser.",
            "visibility": "public",
            "edited": 1,
            "reply_to_id": 1,
            "created_at": "2026-06-10 09:30:00",
            "updated_at": "2026-06-10 09:35:00"
        }
    ]
}

# PUT /wp-json/sweetdesk/v1/messages/:id
## This route handles the editing of a given message based on its ID. Use the following tables:
- sweetdesk_ticket_messages
- sweetdesk_tickets
### Send the following data to the above tables:
- client_id
- assigned_to
- status
- priority
- updated_at
Eventually, I want users to be able to define their reply types and who can see them. For now however, the only reply types will be public and internal.
- body
- is_internal
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- updated_at

#### Example request: 
PUT /wp-json/sweetdesk/v1/messages/17
Content-Type: application/json
{
    "body": "Please clear your browser cache and try again.",
    "visibility": "public",
    "reply_to_id": null,

    "assigned_to": 5,
    "status": "pending",
    "priority": "normal"
}

#### Example Response: 
{
    "success": true,
    "message_id": 17,
    "ticket_id": 42,
    "message": "Message updated successfully."
}

# DELETE /wp-json/sweetdesk/v1/messages/:id
## Delete the reply with the given ID.
