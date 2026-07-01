# SweetDesk Clients API

Base path:

```txt
/wp-json/sweetdesk/v1
```

## Schema notes

### Fix `sweetdesk_client_meta` SQL

Your current SQL has an extra `(`:

```php
CREATE TABLE {$table} ( (
```

It should be:

```php
CREATE TABLE {$table} (
```

### Add `client_id` to `sweetdesk_people`

```sql
client_id BIGINT UNSIGNED NULL,
KEY idx_client_id (client_id)
```

This allows people to be associated with a client.

---

# GET `/clients`

Retrieve a searchable list of clients for the Clients page.

## Accessed tables

* `sweetdesk_clients`
* `sweetdesk_tickets`

## Query params

```txt
?q=string
&page=1
&per_page=25
&sort=name
&order=asc
```

## Returned fields

From `sweetdesk_clients`:

* `id`
* `name`
* `email`
* `phone`
* `website`

Calculated from `sweetdesk_tickets`:

* `total_tickets`
* `open_tickets`
* `closed_tickets`

## Example request

```http
GET /wp-json/sweetdesk/v1/clients?q=acme&page=1&per_page=25
```

## Example response

```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "name": "Acme Inc.",
      "email": "support@acme.com",
      "phone": "555-123-4567",
      "website": "https://acme.com",
      "total_tickets": 18,
      "open_tickets": 4,
      "closed_tickets": 14
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 25,
    "total": 1,
    "total_pages": 1
  }
}
```

---

# GET `/clients/:id`

Retrieve one client, including custom metadata, recent tickets, and related people.

## Accessed tables

* `sweetdesk_clients`
* `sweetdesk_client_meta`
* `sweetdesk_tickets`
* `sweetdesk_people`

## Returned fields

From `sweetdesk_clients`:

* `id`
* `name`
* `email`
* `phone`
* `website`
* `notes`
* `created_at`
* `updated_at`

From `sweetdesk_client_meta`:

* `meta_id`
* `client_id`
* `meta_key`
* `meta_value`

From `sweetdesk_tickets`:

* Five most recent tickets, newest first
* `id`
* `assigned_to`
* `created_by`
* `title`
* `status`
* `priority`
* `created_at`

From `sweetdesk_people`:

* `id`
* `first_name`
* `last_name`
* `email`
* `role`

## Example request

```http
GET /wp-json/sweetdesk/v1/clients/3
```

## Example response

```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Acme Inc.",
    "email": "support@acme.com",
    "phone": "555-123-4567",
    "website": "https://acme.com",
    "notes": "Enterprise client with priority support.",
    "created_at": "2026-07-01 09:30:00",
    "updated_at": "2026-07-01 09:30:00",
    "meta": [
      {
        "meta_id": 12,
        "client_id": 3,
        "meta_key": "industry",
        "meta_value": "Manufacturing"
      }
    ],
    "recent_tickets": [
      {
        "id": 44,
        "assigned_to": 2,
        "created_by": 9,
        "title": "Cannot access dashboard",
        "status": "open",
        "priority": "high",
        "created_at": "2026-07-01 08:15:00"
      }
    ],
    "people": [
      {
        "id": 9,
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane@acme.com",
        "role": "client"
      }
    ]
  }
}
```

---

# POST `/clients`

Create a new client.

## Affected tables

* `sweetdesk_clients`
* `sweetdesk_client_meta`

This route should not directly create people unless you intentionally add a `people` array to the request body. Client contacts are better handled through the People routes.

## Accepted fields

To `sweetdesk_clients`:

* `name`
* `email`
* `phone`
* `website`
* `notes`

Do not send manually:

* `id`
* `created_at`
* `updated_at`

To `sweetdesk_client_meta`:

* `meta_key`
* `meta_value`

Do not send manually:

* `meta_id`
* `client_id`

## Example request

```http
POST /wp-json/sweetdesk/v1/clients
Content-Type: application/json
```

```json
{
  "name": "Acme Inc.",
  "email": "support@acme.com",
  "phone": "555-123-4567",
  "website": "https://acme.com",
  "notes": "Enterprise client with priority support.",
  "meta": {
    "industry": "Manufacturing",
    "account_status": "active"
  }
}
```

## Example response

```json
{
  "success": true,
  "message": "Client created successfully.",
  "data": {
    "id": 3,
    "name": "Acme Inc.",
    "email": "support@acme.com",
    "phone": "555-123-4567",
    "website": "https://acme.com",
    "notes": "Enterprise client with priority support.",
    "meta": {
      "industry": "Manufacturing",
      "account_status": "active"
    }
  }
}
```

---

# PUT `/clients/:id`

Update an existing client.

## Affected tables

* `sweetdesk_clients`
* `sweetdesk_client_meta`

This route should not directly update related people. People should be updated through:

```txt
PUT /wp-json/sweetdesk/v1/people/:id
```

## Accepted fields

To `sweetdesk_clients`:

* `name`
* `email`
* `phone`
* `website`
* `notes`

Do not send manually:

* `id`
* `created_at`
* `updated_at`

To `sweetdesk_client_meta`:

* `meta_key`
* `meta_value`

Recommended behavior:

* Upsert meta by `client_id + meta_key`
* Only delete meta when explicitly requested

## Example request

```http
PUT /wp-json/sweetdesk/v1/clients/3
Content-Type: application/json
```

```json
{
  "name": "Acme Corporation",
  "email": "support@acme.com",
  "phone": "555-987-6543",
  "website": "https://acme.com",
  "notes": "Updated account notes.",
  "meta": {
    "industry": "Manufacturing",
    "account_status": "priority"
  }
}
```

## Example response

```json
{
  "success": true,
  "message": "Client updated successfully.",
  "data": {
    "id": 3,
    "name": "Acme Corporation",
    "email": "support@acme.com",
    "phone": "555-987-6543",
    "website": "https://acme.com",
    "notes": "Updated account notes.",
    "meta": {
      "industry": "Manufacturing",
      "account_status": "priority"
    }
  }
}
```

---

# DELETE `/clients/:id`

Delete a client.

## Affected tables

* `sweetdesk_clients`
* `sweetdesk_client_meta`
* optionally `sweetdesk_people`
* optionally `sweetdesk_tickets`

## Recommended behavior

Do not delete related tickets automatically.

Instead:

1. Delete client meta.
2. Set related people `client_id` to `NULL`.
3. Set related tickets `client_id` to `NULL`.
4. Delete the client.

This preserves historical tickets and people records.

## Example request

```http
DELETE /wp-json/sweetdesk/v1/clients/3
```

## Example response

```json
{
  "success": true,
  "message": "Client deleted successfully.",
  "data": {
    "id": 3,
    "people_unlinked": 4,
    "tickets_unlinked": 18
  }
}
```

---

# GET `/clients/export`

Export client data.

Despite the original wording saying CSV and JSON, choose one format per route. Recommended:

```txt
GET /clients/export
```

returns JSON.

Later, if needed:

```txt
GET /clients/export.csv
```

can return CSV.

## Accessed tables

* `sweetdesk_clients`
* `sweetdesk_client_meta`
* `sweetdesk_tickets`
* `sweetdesk_people`

## Query params

```txt
?q=string
&include_people=true
&include_recent_tickets=true
```

## Example request

```http
GET /wp-json/sweetdesk/v1/clients/export?include_people=true&include_recent_tickets=true
```

## Example response

```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "name": "Acme Inc.",
      "email": "support@acme.com",
      "phone": "555-123-4567",
      "website": "https://acme.com",
      "notes": "Enterprise client with priority support.",
      "created_at": "2026-07-01 09:30:00",
      "updated_at": "2026-07-01 09:30:00",
      "meta": {
        "industry": "Manufacturing",
        "account_status": "active"
      },
      "recent_tickets": [
        {
          "id": 44,
          "assigned_to": 2,
          "created_by": 9,
          "title": "Cannot access dashboard",
          "status": "open",
          "priority": "high",
          "created_at": "2026-07-01 08:15:00"
        }
      ],
      "people": [
        {
          "id": 9,
          "first_name": "Jane",
          "last_name": "Smith",
          "email": "jane@acme.com",
          "role": "client"
        }
      ]
    }
  ]
}
```

---

# POST `/clients/import`

Import client data as JSON.

## Affected tables

* `sweetdesk_clients`
* `sweetdesk_client_meta`

Optional future behavior:

* Create/update related people if a `people` array is provided

## Example request

```http
POST /wp-json/sweetdesk/v1/clients/import
Content-Type: application/json
```

```json
{
  "clients": [
    {
      "name": "Acme Inc.",
      "email": "support@acme.com",
      "phone": "555-123-4567",
      "website": "https://acme.com",
      "notes": "Enterprise client with priority support.",
      "meta": {
        "industry": "Manufacturing",
        "account_status": "active"
      }
    }
  ]
}
```

## Example response

```json
{
  "success": true,
  "message": "Clients imported successfully.",
  "data": {
    "created": 1,
    "updated": 0,
    "skipped": 0,
    "errors": []
  }
}
```

---

# Final route list

```txt
GET    /wp-json/sweetdesk/v1/clients
GET    /wp-json/sweetdesk/v1/clients/:id
POST   /wp-json/sweetdesk/v1/clients
PUT    /wp-json/sweetdesk/v1/clients/:id
DELETE /wp-json/sweetdesk/v1/clients/:id
GET    /wp-json/sweetdesk/v1/clients/export
POST   /wp-json/sweetdesk/v1/clients/import
```

# Recommended permissions

For now:

```php
current_user_can('read')
```

Eventually:

```php
current_user_can('sweetdesk_clients_view')
current_user_can('sweetdesk_clients_create')
current_user_can('sweetdesk_clients_edit')
current_user_can('sweetdesk_clients_delete')
current_user_can('sweetdesk_clients_export')
current_user_can('sweetdesk_clients_import')
```
