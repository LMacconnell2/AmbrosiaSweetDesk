# GET /wp-json/sweetdesk/v1/analytics/summary
    ?scope=company
    &person_id=4
    &date_start=2026-07-01
    &date_end=2026-07-13
## Description: 
- This route handles the retrieval of summary data concerning tickets and employees.

## | Parameter    |        Required | Description                  |
   | ------------ | --------------: | ---------------------------- |
   | `scope`      |             Yes | `company` or `user`          |
   | `person_id`  | User scope only | SweetDesk person ID          |
   | `date_start` |             Yes | Beginning of reporting range |
   | `date_end`   |             Yes | End of reporting range       |
   | `team_id`    |              No | Future team-level filtering  |

## Example Response:
{
  "scope": "company",
  "person_id": null,
  "date_range": {
    "start": "2026-07-01",
    "end": "2026-07-13"
  },
  "tickets": {
    "received": 87,
    "cleared": 63,
    "assigned": null,
    "average_received_per_member": 29,
    "average_cleared_per_member": 21
  },
  "resolution_time": {
    "count": 63,
    "median_seconds": 198720,
    "average_seconds": 207360,
    "average_per_member_seconds": 224640,
    "minimum": {
      "seconds": 95040,
      "ticket": {
        "id": 14,
        "title": "Login page throws 500 error on submit"
      }
    },
    "maximum": {
      "seconds": 587520,
      "ticket": {
        "id": 31,
        "title": "Newly-created users can't access settings"
      }
    }
  },
  "feedback": {
    "count": 48,
    "median": 4.1,
    "average": 3.9,
    "average_per_member": 3.7,
    "minimum": {
      "score": 1,
      "ticket": {
        "id": 22,
        "title": "Billing page crash on checkout"
      }
    },
    "maximum": {
      "score": 5,
      "ticket": {
        "id": 14,
        "title": "Login page throws 500 error on submit"
      }
    }
  }
}

## User Scope Example Response:
{
  "tickets": {
    "assigned": 24,
    "cleared": 19,
    "received": null,
    "average_received_per_member": null,
    "average_cleared_per_member": null
  }
}


# GET /wp-json/sweetdesk/v1/analytics/oldest-unresolved
    ?scope=company
    &person_id=12
    &limit=3
## Description: This route handles the retrieval of data concerning the eldest unresolved tickets.
- NOTE: The amount of returned tickets will be configureable. The "limit" paramter defines the number of tickets to show, from older to newer.
## Example Company Wide Response:
{
  "data": [
    {
      "id": 29,
      "title": "Newly-created users can't access settings",
      "status": "open",
      "created_at": "2026-06-19 08:15:00",
      "age_seconds": 2073600,
      "assignee": {
        "id": 8,
        "first_name": "Alice",
        "last_name": "Williams"
      }
    }
  ]
}

## Example user-scope response:
{
  "data": [
    {
      "id": 14,
      "title": "Login page throws 500 error on submit",
      "status": "pending",
      "created_at": "2026-06-25 11:40:00",
      "age_seconds": 1555200
    }
  ]
}


# GET /wp-json/sweetdesk/v1/analytics/recent-messages
    ?person_id=12
    &source=all
    &limit=5
## Description: This route handles the retrieval of data concerning the most recent messages sent to/from tickets.
## Example Response:
{
  "data": [
    {
      "id": 91,
      "ticket": {
        "id": 17,
        "title": "Login page throws 500 error on submit"
      },
      "author": {
        "id": 28,
        "display_name": "Bob Johnson"
      },
      "source": "customer",
      "body": "Just wanted to say the fix worked perfectly...",
      "created_at": "2026-07-13 12:30:00"
    }
  ]
}

# GET /wp-json/sweetdesk/v1/analytics/export
  ?scope=company
  &user_id=12
  &date_start=2026-07-01
  &date_end=2026-07-13
  &format=csv|json
# Description: This endpoint returns the analytics data displayed on the screen. The export controller should call the same analytics service methods as the dashboard endpoint. Do not duplicate analytics calculations inside the export controller.

