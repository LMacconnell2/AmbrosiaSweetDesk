# GET api/settings/email
## Description:
This route handles the retrieval of data concerning the CURRENT user's email notification settings.
Available settings:
- Email Provider
- Connected Email(s). (A user can specify one more more email address in addition to the one linked with their worpdress account.)
- Send Email on Ticket updates?
- Send Email on Team mentions?

## Example Request:
GET /wp-json/sweetdesk/v1/settings/email

## Example Response:
{
  "data": {
    "wordpress_email": "logan@example.com",
    "additional_emails": [
      "work@example.com",
      "support@example.com"
    ],
    "send_ticket_updates": true,
    "send_team_mentions": true
  }
}


# PUT /api/settings/email
## Description:
This api handles the setting of data in regards to email notification settings.
Available settings:
- Email Provider
- Connected Email(s). (A user can specify one more more email address in addition to the one linked with their worpdress account.)
- Send Email on Ticket updates?
- Send Email on Team mentions?

## Example Request:
PUT /wp-json/sweetdesk/v1/settings/email
Content-Type: application/json
X-WP-Nonce: your-rest-nonce
{
  "additional_emails": [
    "work@example.com",
    "support@example.com"
  ],
  "send_ticket_updates": true,
  "send_team_mentions": false
}

(The following disables ticket update emails)
{
  "send_ticket_updates": false
}
(The following removes all additional addresses)
{
  "additional_emails": []
}

# GET api/settings/tickets/status
## Description:
- This route handles the retrieval of data concerning ticket statuses. 
- Key value pairs with id and name. (NOTE, status is currently saved in tickets as a varchar() This will be maintained for now.)
## Example Request:
GET /wp-json/sweetdesk/v1/settings/tickets/status
## Example Response:
{
  "data": [
    {
      "id": 1,
      "name": "Open",
      "slug": "open",
      "is_active": true,
      "sort_order": 10,
      "created_at": "2026-07-13 16:00:00",
      "updated_at": "2026-07-13 16:00:00"
    }
  ],
  "count": 1
}

## Query request version:
GET /wp-json/sweetdesk/v1/settings/tickets/status?include_inactive=true

- This returns the list of available statuses as set by the user.

# POST api/settings/tickets/status
## Description:
- This route handles the creation of a new status to be added to the list of available statuses. ID, name is_active.

## Example Request:
POST /wp-json/sweetdesk/v1/settings/tickets/status
{
  "name": "Waiting on Customer",
  "slug": "waiting-on-customer",
  "is_active": true,
  "sort_order": 30
}

# PUT api/settings/tickets/status/:id
## Description
- This route handles the editing of a status based on the id passed in the url. ID, name, is_active.
- This route is alsot the one used for deletion. (We will be doing soft deletes in most cases.)
# Example request:
PUT /wp-json/sweetdesk/v1/settings/tickets/status/5
{
  "name": "Awaiting Customer",
  "sort_order": 40
}

## Soft Delete Example Request:
PUT /wp-json/sweetdesk/v1/settings/tickets/status/5
{
  "is_active": false
}


# DELETE /api/settings/tickets/status/:id
## Description
- This route handles the HARD deletion of status based on the id passed into the URL. 
- This route will likely be unused for now.


# GET /api/settings/tickets/fields
## Descripction: 
- This route handles the retrieval of data concerning custom fields. (returns an array of entries.)
- May need to create a new table in the DB for this.

*NOTE: This route will be used to display the current list of custom statuses available when viewing through the settings menu AS WELL as function as a lookup route for when a user is creating/editing a ticket.

Each custom field specified will have the following data:
- id
- name
- data type? (Not sure on this one, we may leave everything as a varchar)
- is_active
- created_at
- updated_at

*NOTE: This route will be used to display the current list of custom fields available when viewing through the settings menu AS WELL as function as a lookup route for when a user is creating/editing a ticket.

## Example Request: Retrieve Active Fields:
GET /wp-json/sweetdesk/v1/settings/tickets/fields

## Example Request: REtrieve all fields for settings page:
GET /wp-json/sweetdesk/v1/settings/tickets/fields?include_inactive=true


# POST /api/settings/tickets/fields
## Description:
- This route handles the creation of data concerning custom fields. (Creates a single entry.)

Each custom field specified will need the following data inserted into the DB:
- id (provided by backend)
- name (provided by user)
- data type? (Not sure on this one, we may leave everything as a varchar) (provided by user)
- is_active (provided by user)
- created_at (provided by API)
- updated_at (provided by API)

## Example Request:
POST /wp-json/sweetdesk/v1/settings/tickets/fields
(The following creates a text field)
{
  "name": "Device Model",
  "field_key": "device_model",
  "field_type": "text",
  "is_required": false,
  "is_active": true,
  "sort_order": 10
}
(the following creates a select field)
{
  "name": "Issue Category",
  "field_key": "issue_category",
  "field_type": "select",
  "is_required": true,
  "is_active": true,
  "sort_order": 20,
  "options": [
    "Billing",
    "Technical",
    "Account"
  ]
}

# PUT /api/settings/tickets/fields/:id
## Description:
- This route handles the edition of the data pertaining to the cusomt field with the given id.
-Each custom field specified will need the following data inserted into the DB:
- id (provided by request)
- name (provided by user)
- data type? (Not sure on this one, we may leave everything as a varchar) (provided by user)
- is_active (provided by user)
- created_at (provided by DB - Should not change)
- updated_at (provided by API)

## Example Request:
PUT /wp-json/sweetdesk/v1/settings/tickets/fields/3
(This is a text field example)
{
  "name": "Customer Device Model",
  "is_required": true,
  "sort_order": 15
}

(This is a select option example)
{
  "options": [
    "Billing",
    "Technical",
    "Account",
    "General"
  ]
}

(The following is a soft delete:)
PUT /wp-json/sweetdesk/v1/settings/tickets/fields/3
{
  "is_active": false
}

(The following is a field reactivation:)
{
  "is_active": true
}

# DELETE /api/settings/tickets/fields/:id
- This route handles the hard deletion of a custom field.