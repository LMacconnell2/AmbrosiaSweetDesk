# SweetDesk People API

Base path:

```txt
/wp-json/sweetdesk/v1
```

## Important schema notes

Your API map references `client_id`, `role_ids`, and `team_ids`, but the current tables do not fully support all of those yet.

Recommended updates:

### Add `client_id` to `sweetdesk_people`

```sql
client_id BIGINT UNSIGNED NULL,
KEY idx_client_id (client_id)
```

### Keep `role` as a string for now, or create a roles table later

Your current table has:

```sql
role VARCHAR(50) NULL
```

So the filter should probably be:

```txt
?roles=staff,client,manager
```

instead of:

```txt
?role_ids=4,5,6
```

Unless you plan to create a `sweetdesk_roles` table.

### Fix typo in query params

This:

```txt
&roteam_ids=1,2,3le_id=4,5,6
```

Should be:

```txt
&team_ids=1,2,3
```

### Recommended list query params

```txt
GET /wp-json/sweetdesk/v1/people
    ?q=john
    &roles=staff,client
    &team_ids=1,2,3
    &client_ids=1,2,4
    &internal=true
    &is_active=1
    &page=1
    &per_page=25
    &sort=last_name
    &order=asc
```

---

# GET `/people`

Retrieve a paginated/searchable list of people.

## Accessed tables

* `sweetdesk_people`
* optionally `sweetdesk_people_teams` if filtering by `team_ids`

## Query params

| Param        |         Type | Description                                                              |
| ------------ | -----------: | ------------------------------------------------------------------------ |
| `q`          |       string | Searches first name, last name, email                                    |
| `roles`      |   CSV string | Example: `staff,client,manager`                                          |
| `team_ids`   | CSV integers | Filters people assigned to one or more teams                             |
| `client_ids` | CSV integers | Filters people linked to one or more clients                             |
| `internal`   |      boolean | `true` = people with `wp_user_id`; `false` = people without `wp_user_id` |
| `is_active`  |  boolean/int | `1`, `0`, `true`, or `false`                                             |
| `page`       |      integer | Default `1`                                                              |
| `per_page`   |      integer | Default `25`                                                             |
| `sort`       |       string | `first_name`, `last_name`, `email`, `role`, `created_at`, `updated_at`   |
| `order`      |       string | `asc` or `desc`                                                          |

## Returned fields

From `sweetdesk_people`:

* `id`
* `wp_user_id`
* `client_id`
* `first_name`
* `last_name`
* `email`
* `role`
* `avatar_url`
* `is_active`

## Example request

```http
GET /wp-json/sweetdesk/v1/people?q=john&roles=staff&team_ids=1,2&internal=true&page=1&per_page=25
```

## Example response

```json
{
  "success": true,
  "data": [
    {
      "id": 14,
      "wp_user_id": 7,
      "client_id": null,
      "first_name": "John",
      "last_name": "Miller",
      "email": "john@example.com",
      "role": "staff",
      "avatar_url": "https://example.com/avatar.jpg",
      "is_active": true
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 25,
    "total": 1,
    "total_pages": 1
  },
  "filters": {
    "q": "john",
    "roles": ["staff"],
    "team_ids": [1, 2],
    "internal": true
  },
  "sorting": {
    "sort": "last_name",
    "order": "asc"
  }
}
```

---

# GET `/people/:id`

Retrieve a single person, including metadata and team assignments.

## Accessed tables

* `sweetdesk_people`
* `sweetdesk_people_meta`
* `sweetdesk_people_teams`
* `sweetdesk_teams`

## Returned fields

From `sweetdesk_people`:

* `id`
* `wp_user_id`
* `client_id`
* `first_name`
* `last_name`
* `email`
* `role`
* `avatar_url`
* `is_active`
* `created_at`
* `updated_at`

From `sweetdesk_people_meta`:

* `meta_id`
* `meta_key`
* `meta_value`

From `sweetdesk_people_teams` and `sweetdesk_teams`:

* `person_id`
* `team_id`
* `assigned_at`
* `name`
* `description`
* `color`

## Example request

```http
GET /wp-json/sweetdesk/v1/people/14
```

## Example response

```json
{
  "success": true,
  "data": {
    "id": 14,
    "wp_user_id": 7,
    "client_id": null,
    "first_name": "John",
    "last_name": "Miller",
    "email": "john@example.com",
    "role": "staff",
    "avatar_url": "https://example.com/avatar.jpg",
    "is_active": true,
    "created_at": "2026-07-01 09:30:00",
    "updated_at": "2026-07-01 09:30:00",
    "meta": [
      {
        "meta_id": 3,
        "meta_key": "phone",
        "meta_value": "555-123-4567"
      },
      {
        "meta_id": 4,
        "meta_key": "job_title",
        "meta_value": "Support Manager"
      }
    ],
    "teams": [
      {
        "person_id": 14,
        "team_id": 1,
        "assigned_at": "2026-07-01 09:30:00",
        "name": "Support",
        "description": "Handles customer support tickets",
        "color": "#3b82f6"
      }
    ]
  }
}
```

---

# POST `/people`

Create a new person.

## Affected tables

* `sweetdesk_people`
* `sweetdesk_people_meta`
* `sweetdesk_people_teams`

## Fields accepted

To `sweetdesk_people`:

* `wp_user_id`
* `client_id`
* `first_name`
* `last_name`
* `email`
* `role`
* `avatar_url`
* `is_active`

Do not manually send:

* `id`
* `created_at`
* `updated_at`

To `sweetdesk_people_meta`:

* `meta_key`
* `meta_value`

Do not manually send:

* `meta_id`
* `person_id`

To `sweetdesk_people_teams`:

* `team_id`

Do not manually send:

* `person_id`
* `assigned_at`

## Example request

```http
POST /wp-json/sweetdesk/v1/people
Content-Type: application/json
```

```json
{
  "wp_user_id": 7,
  "client_id": null,
  "first_name": "John",
  "last_name": "Miller",
  "email": "john@example.com",
  "role": "staff",
  "avatar_url": "https://example.com/avatar.jpg",
  "is_active": true,
  "meta": {
    "phone": "555-123-4567",
    "job_title": "Support Manager"
  },
  "team_ids": [1, 3]
}
```

## Example response

```json
{
  "success": true,
  "message": "Person created successfully.",
  "data": {
    "id": 14,
    "wp_user_id": 7,
    "client_id": null,
    "first_name": "John",
    "last_name": "Miller",
    "email": "john@example.com",
    "role": "staff",
    "avatar_url": "https://example.com/avatar.jpg",
    "is_active": true,
    "meta": {
      "phone": "555-123-4567",
      "job_title": "Support Manager"
    },
    "team_ids": [1, 3]
  }
}
```

---

# PUT `/people/:id`

Update an existing person.

This updates SweetDesk people data only. It should not directly update the linked WordPress user record.

## Affected tables

* `sweetdesk_people`
* `sweetdesk_people_meta`
* `sweetdesk_people_teams`

## Fields accepted

To `sweetdesk_people`:

* `wp_user_id`
* `client_id`
* `first_name`
* `last_name`
* `email`
* `role`
* `avatar_url`
* `is_active`

Do not manually update:

* `id`
* `created_at`
* `updated_at`

To `sweetdesk_people_meta`:

* `meta_key`
* `meta_value`

Recommended behavior:

* Upsert meta by `person_id + meta_key`
* Delete meta keys only if explicitly requested

To `sweetdesk_people_teams`:

* `team_ids`

Recommended behavior:

* Treat `team_ids` as replacement list
* Delete existing team assignments not in the new array
* Insert new team assignments

## Example request

```http
PUT /wp-json/sweetdesk/v1/people/14
Content-Type: application/json
```

```json
{
  "client_id": null,
  "first_name": "John",
  "last_name": "Miller",
  "email": "john.miller@example.com",
  "role": "manager",
  "avatar_url": "https://example.com/new-avatar.jpg",
  "is_active": true,
  "meta": {
    "phone": "555-987-6543",
    "job_title": "Support Lead"
  },
  "team_ids": [1, 2]
}
```

## Example response

```json
{
  "success": true,
  "message": "Person updated successfully.",
  "data": {
    "id": 14,
    "wp_user_id": 7,
    "client_id": null,
    "first_name": "John",
    "last_name": "Miller",
    "email": "john.miller@example.com",
    "role": "manager",
    "avatar_url": "https://example.com/new-avatar.jpg",
    "is_active": true,
    "meta": {
      "phone": "555-987-6543",
      "job_title": "Support Lead"
    },
    "team_ids": [1, 2]
  }
}
```

---

# DELETE `/people/:id`

Delete a person.

## Affected tables

* `sweetdesk_people`
* `sweetdesk_people_meta`
* `sweetdesk_people_teams`

## Recommended behavior

Before deleting the person:

1. Delete from `sweetdesk_people_teams`
2. Delete from `sweetdesk_people_meta`
3. Delete from `sweetdesk_people`

You may eventually want soft deletes instead:

```sql
is_active = 0
```

For now, this route can perform a hard delete.

## Example request

```http
DELETE /wp-json/sweetdesk/v1/people/14
```

## Example response

```json
{
  "success": true,
  "message": "Person deleted successfully.",
  "data": {
    "id": 14
  }
}
```

---

# POST `/people/import`

Import people from CSV.

## Affected tables

* `sweetdesk_people`
* `sweetdesk_people_meta`
* `sweetdesk_people_teams`

## Recommended CSV columns

```csv
wp_user_id,client_id,first_name,last_name,email,role,avatar_url,is_active,team_ids,phone,job_title
```

Example:

```csv
7,,John,Miller,john@example.com,staff,https://example.com/avatar.jpg,1,"1,3",555-123-4567,Support Manager
```

## Recommended behavior

* Insert people into `sweetdesk_people`
* Store unknown/custom columns in `sweetdesk_people_meta`
* Parse `team_ids` into `sweetdesk_people_teams`
* Skip or update duplicates based on email
* Return import summary

## Example request

```http
POST /wp-json/sweetdesk/v1/people/import
Content-Type: multipart/form-data
```

```json
{
  "file": "people.csv"
}
```

## Example response

```json
{
  "success": true,
  "message": "People imported successfully.",
  "data": {
    "created": 12,
    "updated": 3,
    "skipped": 1,
    "errors": [
      {
        "row": 8,
        "message": "Missing email and name."
      }
    ]
  }
}
```

---

# GET `/people/export`

Export people as CSV.

## Accessed tables

* `sweetdesk_people`
* `sweetdesk_people_meta`
* `sweetdesk_people_teams`
* optionally `sweetdesk_teams`

## Query params

```txt
GET /wp-json/sweetdesk/v1/people/export
    ?q=string
    &roles=staff,client
    &team_ids=1,2,3
    &client_ids=1,2,4
    &internal=true
    &is_active=1
```

## Exported fields

From `sweetdesk_people`:

* `id`
* `wp_user_id`
* `client_id`
* `first_name`
* `last_name`
* `email`
* `role`
* `avatar_url`
* `is_active`
* `created_at`
* `updated_at`

From `sweetdesk_people_meta`:

* exported as dynamic columns, for example:

  * `phone`
  * `job_title`

From `sweetdesk_people_teams` / `sweetdesk_teams`:

* `team_ids`
* `team_names`

## Example request

```http
GET /wp-json/sweetdesk/v1/people/export?roles=staff&team_ids=1,2
```

## Example CSV response

```csv
id,wp_user_id,client_id,first_name,last_name,email,role,avatar_url,is_active,team_ids,team_names,phone,job_title,created_at,updated_at
14,7,,John,Miller,john@example.com,staff,https://example.com/avatar.jpg,1,"1,3","Support,Billing",555-123-4567,Support Manager,"2026-07-01 09:30:00","2026-07-01 09:30:00"
```

---

# Final recommended route list

```txt
GET    /wp-json/sweetdesk/v1/people
GET    /wp-json/sweetdesk/v1/people/:id
POST   /wp-json/sweetdesk/v1/people
PUT    /wp-json/sweetdesk/v1/people/:id
DELETE /wp-json/sweetdesk/v1/people/:id
POST   /wp-json/sweetdesk/v1/people/import
GET    /wp-json/sweetdesk/v1/people/export
```

# Recommended permissions

For now:

```php
current_user_can('read')
```

Eventually:

```php
current_user_can('sweetdesk_people_view')
current_user_can('sweetdesk_people_create')
current_user_can('sweetdesk_people_edit')
current_user_can('sweetdesk_people_delete')
current_user_can('sweetdesk_people_import')
current_user_can('sweetdesk_people_export')
```



