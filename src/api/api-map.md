# GET /wp-json/sweetdesk/v1/tickets
    ?status=open
    &priority=urgent
    &assigned_to=4
    &search=login
    &page=2
    &per_page=25
    &sort=created_at
    &order=desc
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

# POST /wp-json/sweetdesk/v1/new-ticket
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

To sweetdesk_ticket_meta: *Note: We will need to grab the list of sweetdesk_custom_ticket_fields and use those to insert rows into the table.
- meta_id
- ticket_id
- meta_key
- meta_value
*Note, a new row will be added for each custom field found in sweetdesk_custom_ticket_fields. If that data does not exist, allow for null values.

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

# PUT /wp-json/sweetdesk/v1/edit-ticket/:id
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

To sweetdesk_ticket_meta: *Note: We will need to grab the list of sweetdesk_custom_ticket_fields and use those to insert rows into the table.
(Use meta_id and ticket_id to grab the correct field.)
- meta_key
- meta_value

# PUT /wp-json/sweetdesk/v1/ticket-assignee
## This route handles updating the assignee of a ticket quickly. Update the following table:
- sweetdesk_tickets
### Change the following value:
- assigned_to

# PUT /wp-json/sweetdesk/v1/ticket-status
## This route handles updating the status of a ticket quickly. Update the following table:
- sweetdesk_tickets
### Change the following value:
- status

# DELETE /wp-json/sweetdesk/v1/:id
## This route handles the removal of tickets. All data related to the ticket will be removed.

# GET /wp-json/sweetdesk/v1/:id
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

# GET /wp-json/sweetdesk/v1/ticket/bulk
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

# POST /wp-json/sweetdesk/v1/ticket/bulk
## This route handles the importation of all tickets in a json file into the database.
- sweetdesk_tickets
- sweetdesk_ticket_meta
- sweetdesk_ticket_messages
### Send the following information from the above tables:
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
- is_internal
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

# GET /wp-json/sweetdesk/v1/tickets/:id/messages
## This route handles the retrieval of all messages based on the given ticket ID. Retrieve from the following table:
- sweetdesk_ticket_messages
### Retrieve the following data: 
- body
- is_internal
- edited
- reply_to_id *note that this value will reference a previous reply on this ticket. If this value is null, this message will simply appear in the main thread. In the current API route, this value will be null as we are only adding the first "reply."
- created_at
- updated_at

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

# DELETE /wp-json/sweetdesk/v1/messages/:id
## Delete the reply with the given ID.
