# GET /wp-json/sweetdesk/v1/teams
    ?q=string
    &team_member_id=0,2,5 (an array of ID's)
    &pagination.
## Description:
- This route handles the retrieval of team data from the database.
## Request Example:
- GET /wp-json/sweetdesk/v1/teams?q=support&team_member_id=2,5&page=1&per_page=25
## Tables and Fields to Access:
FROM sweetdesk_teams:
- id
- name
- color
- created_at
- updated_at

FROM sweetdesk_team_meta: (search on team_id)
- meta_id
- team_id
- meta_key
- meta_value

FROM sweetdesk_people_teams: (search on team_id)
- person_id
- team_id
- assigned_at

FROM sweetdesk_people:
- id
- first_name
- last_name
- email
- role

## Example response:
{
  "data": [
    {
      "id": 1,
      "name": "Support Team",
      "description": "Handles incoming customer support tickets.",
      "color": "#2563eb",
      "created_at": "2026-07-06 10:15:00",
      "updated_at": "2026-07-06 10:15:00",
      "meta": [
        {
          "meta_id": 1,
          "team_id": 1,
          "meta_key": "default_priority",
          "meta_value": "normal"
        }
      ],
      "members": [
        {
          "person_id": 2,
          "team_id": 1,
          "assigned_at": "2026-07-06 10:20:00",
          "person": {
            "id": 2,
            "first_name": "Jane",
            "last_name": "Smith",
            "email": "jane@example.com",
            "role": "agent"
          }
        }
      ]
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 25,
    "total": 1,
    "total_pages": 1
  }
}


# POST /wp-json/sweetdesk/v1/teams
## Description:
- This route handles the creation of a new team.
## Request Data Sent to DB:
A json object sending the following data:
TO sweetdesk_teams:
- id (assigned by API)
- name
- description
- color
- created_at (current moment)
- updated_at (will be same as created_at)

TO sweetdesk_team_meta: For now these will probably be null, but we will create the infrastucture for when we are ready to add this.
- meta_id (assigned by API)
- team_id
- meta_key
- meta_value

TO sweetdesk_people_teams:
- person_id
- team_id
- assigned_at

## Example Request Body:
{
  "name": "Support Team",
  "description": "Handles incoming customer support tickets.",
  "color": "#2563eb",
  "meta": [
    {
      "meta_key": "default_priority",
      "meta_value": "normal"
    }
  ],
  "people": [
    {
      "person_id": 2
    },
    {
      "person_id": 5
    }
  ]
}

## Response Example:
{
  "success": true,
  "message": "Team created successfully.",
  "data": {
    "id": 1
  }
}

# GET /wp-json/sweetdesk/v1/teams/:id
## Description: 
- This route handles the retrieval of a given team's data based on that team's :id
## Example Request:
GET /wp-json/sweetdesk/v1/teams/1
## Data to Retrieve: 
A json object sending the following data:
FROM sweetdesk_teams:
- id (assigned by API)
- name
- description
- color
- created_at (current moment)
- updated_at (will be same as created_at)

FROM sweetdesk_team_meta: For now these will probably be null, but we will create the infrastucture for when we are ready to add this.
- meta_id (assigned by API)
- team_id
- meta_key
- meta_value

FROM sweetdesk_people_teams:
- person_id
- team_id
- assigned_at

FROM sweetdesk_people:
- id
- first_name
- last_name
- email
- role

## Example response: 
{
  "id": 1,
  "name": "Support Team",
  "description": "Handles incoming customer support tickets.",
  "color": "#2563eb",
  "created_at": "2026-07-06 10:15:00",
  "updated_at": "2026-07-06 10:15:00",
  "meta": [
    {
      "meta_id": 1,
      "team_id": 1,
      "meta_key": "default_priority",
      "meta_value": "normal"
    }
  ],
  "members": [
    {
      "person_id": 2,
      "team_id": 1,
      "assigned_at": "2026-07-06 10:20:00",
      "person": {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane@example.com",
        "role": "agent"
      }
    }
  ]
}

# PUT /wp-json/sweetdesk/v1/teams/:id
## Description: 
- This route handles the updating of a team's data. The :id specifies to WHICH team edits will be applied.
## Data from DB to access:
A json object sending the following data:
TO sweetdesk_teams:
- id (assigned by API)
- name
- description
- color
- created_at (current moment)
- updated_at (will be same as created_at)

TO sweetdesk_team_meta: For now these will probably be null, but we will create the infrastucture for when we are ready to add this.
- meta_id (assigned by API)
- team_id
- meta_key
- meta_value

TO sweetdesk_people_teams:
- person_id
- team_id
- assigned_at

## Request Example: 
{
  "name": "Tier 1 Support",
  "description": "Handles first-response customer support tickets.",
  "color": "#16a34a",
  "meta": [
    {
      "meta_key": "default_priority",
      "meta_value": "high"
    }
  ],
  "people": [
    {
      "person_id": 2
    },
    {
      "person_id": 8
    }
  ]
}

## Response Example:
{
  "success": true,
  "message": "Team updated successfully.",
  "data": {
    "id": 1
  }
}


# DELETE /wp-json/sweetdesk/v1/teams/:id
## Description:
- This route handles the deletion of a given team.
## Example Request:
DELETE /wp-json/sweetdesk/v1/teams/1
## Example Response:
{
  "success": true,
  "message": "Team deleted successfully."
}

NOTE: Deleted related rows from:
sweetdesk_team_meta
sweetdesk_people_teams
sweetdesk_teams (USE team_id)

# PUT /wp-json/sweetdesk/v1/teams/:id/people
## Description: 
- This route handles the updating of the members for a given team based upon that team's ID.
## Data to Send to DB:
A JSON object including the following data:
TO sweetdesk_people_teams
- person_id
- team_id
- assigned_at

## Example Request:
{
  "people": [
    {
      "person_id": 2
    },
    {
      "person_id": 5
    },
    {
      "person_id": 8
    }
  ]
}

## Example Response:
{
  "success": true,
  "message": "Team members updated successfully.",
  "data": {
    "team_id": 1,
    "people": [
      {
        "person_id": 2,
        "team_id": 1,
        "assigned_at": "2026-07-06 10:30:00"
      },
      {
        "person_id": 5,
        "team_id": 1,
        "assigned_at": "2026-07-06 10:30:00"
      },
      {
        "person_id": 8,
        "team_id": 1,
        "assigned_at": "2026-07-06 10:30:00"
      }
    ]
  }
}

# GET /wp-json/sweetdesk/v1/teams/people
    ?q=string
    &role=1,4,5 (an array of ID's)
## Description:
- This route is a lookup route to search for employees which can be assigned to a team. It is meant to be used as part of the team creation/editing process. By default, no one will be displayed, however, once the user starts typing, different names related to the query will appear.
## Example Request:
GET /wp-json/sweetdesk/v1/teams/people?q=jan&role=agent,manager
## Data to retrieve from DB:
A JSON object with the following:
FROM sweetdesk_people
- id
- first_name
- last_name
- email
- role

## Example Response: 
{
  "data": [
    {
      "id": 2,
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane@example.com",
      "role": "agent"
    },
    {
      "id": 5,
      "first_name": "Janet",
      "last_name": "Miller",
      "email": "janet@example.com",
      "role": "manager"
    }
  ]
}


# DELETE /wp-json/sweetdesk/v1/teams/:id/people/:person_id
## Description:
- This route removes the person with the given ID from the given team based upon that team's ID.

