/*
|--------------------------------------------------------------------------
| SWEETDESK TICKETS
|--------------------------------------------------------------------------
|
| Core ticket data
| Frequently queried fields belong here
|
*/

CREATE TABLE sweetdesk_tickets (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    client_id BIGINT UNSIGNED NULL,
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,

    title VARCHAR(255) NOT NULL,
    description LONGTEXT NULL,

    status VARCHAR(50) NOT NULL DEFAULT 'open',
    priority VARCHAR(50) NOT NULL DEFAULT 'normal',

    source VARCHAR(50) NULL,
    due_date DATETIME NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_client_id (client_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_created_by (created_by),

    INDEX idx_status (status),
    INDEX idx_priority (priority),

    INDEX idx_created_at (created_at)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK TICKET META
|--------------------------------------------------------------------------
|
| Stores dynamic/custom ticket fields
|
*/

CREATE TABLE sweetdesk_ticket_meta (

    meta_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ticket_id BIGINT UNSIGNED NOT NULL,

    meta_key VARCHAR(191) NOT NULL,
    meta_value LONGTEXT NULL,

    INDEX idx_ticket_id (ticket_id),
    INDEX idx_meta_key (meta_key),

    UNIQUE KEY unique_ticket_meta (
        ticket_id,
        meta_key
    )

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sweetdesk_ticket_messages (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ticket_id BIGINT UNSIGNED NOT NULL,

    person_id BIGINT UNSIGNED NULL,

    reply_type VARCHAR(50) NOT NULL DEFAULT 'comment',

    body LONGTEXT NOT NULL,

    is_internal TINYINT(1) NOT NULL DEFAULT 0,

    edited TINYINT(1) NOT NULL DEFAULT 0,

    reply_to_id BIGINT UNSIGNED NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_ticket_id (ticket_id),

    INDEX idx_person_id (person_id),

    INDEX idx_reply_to_id (reply_to_id),

    INDEX idx_created_at (created_at)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*
|--------------------------------------------------------------------------
| SWEETDESK CLIENTS
|--------------------------------------------------------------------------
*/

CREATE TABLE sweetdesk_clients (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255) NOT NULL,

    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,

    website VARCHAR(255) NULL,

    notes LONGTEXT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_name (name),
    INDEX idx_email (email)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK CLIENT META
|--------------------------------------------------------------------------
*/

CREATE TABLE sweetdesk_client_meta (

    meta_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    client_id BIGINT UNSIGNED NOT NULL,

    meta_key VARCHAR(191) NOT NULL,
    meta_value LONGTEXT NULL,

    INDEX idx_client_id (client_id),
    INDEX idx_meta_key (meta_key),

    UNIQUE KEY unique_client_meta (
        client_id,
        meta_key
    )

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK PEOPLE
|--------------------------------------------------------------------------
|
| Internal users / agents / support staff
|
*/

CREATE TABLE sweetdesk_people (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    wp_user_id BIGINT UNSIGNED NULL,

    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,

    email VARCHAR(255) NULL,

    role VARCHAR(50) NULL,

    avatar_url VARCHAR(500) NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_wp_user_id (wp_user_id),
    INDEX idx_email (email),
    INDEX idx_role (role)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK PEOPLE META
|--------------------------------------------------------------------------
*/

CREATE TABLE sweetdesk_people_meta (

    meta_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    person_id BIGINT UNSIGNED NOT NULL,

    meta_key VARCHAR(191) NOT NULL,
    meta_value LONGTEXT NULL,

    INDEX idx_person_id (person_id),
    INDEX idx_meta_key (meta_key),

    UNIQUE KEY unique_people_meta (
        person_id,
        meta_key
    )

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK TEAMS
|--------------------------------------------------------------------------
*/

CREATE TABLE sweetdesk_teams (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255) NOT NULL,

    description TEXT NULL,

    color VARCHAR(20) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_name (name)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK TEAM META
|--------------------------------------------------------------------------
*/

CREATE TABLE sweetdesk_team_meta (

    meta_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    team_id BIGINT UNSIGNED NOT NULL,

    meta_key VARCHAR(191) NOT NULL,
    meta_value LONGTEXT NULL,

    INDEX idx_team_id (team_id),
    INDEX idx_meta_key (meta_key),

    UNIQUE KEY unique_team_meta (
        team_id,
        meta_key
    )

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK PEOPLE ↔ TEAMS
|--------------------------------------------------------------------------
|
| Many-to-many relationship table
|
*/

CREATE TABLE sweetdesk_people_teams (

    person_id BIGINT UNSIGNED NOT NULL,
    team_id BIGINT UNSIGNED NOT NULL,

    assigned_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        person_id,
        team_id
    ),

    INDEX idx_team_id (team_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK CUSTOM FIELDS
|--------------------------------------------------------------------------
|
| Defines configurable fields for:
| - tickets
| - clients
| - people
| - teams
|
*/

CREATE TABLE sweetdesk_custom_fields (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    entity_type VARCHAR(50) NOT NULL,

    field_key VARCHAR(100) NOT NULL,

    label VARCHAR(255) NOT NULL,

    field_type VARCHAR(50) NOT NULL,

    is_required TINYINT(1) NOT NULL DEFAULT 0,

    default_value LONGTEXT NULL,

    field_options LONGTEXT NULL,

    placeholder VARCHAR(255) NULL,

    help_text TEXT NULL,

    sort_order INT NOT NULL DEFAULT 0,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_entity_type (entity_type),
    INDEX idx_field_key (field_key),

    UNIQUE KEY unique_entity_field (
        entity_type,
        field_key
    )

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK TICKET ACTIVITY
|--------------------------------------------------------------------------
|
| Audit log/history system
|
*/

CREATE TABLE sweetdesk_ticket_activity (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ticket_id BIGINT UNSIGNED NOT NULL,

    person_id BIGINT UNSIGNED NULL,

    activity_type VARCHAR(100) NOT NULL,

    activity_message TEXT NULL,

    old_value LONGTEXT NULL,
    new_value LONGTEXT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ticket_id (ticket_id),
    INDEX idx_person_id (person_id),
    INDEX idx_activity_type (activity_type)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK COMMENTS
|--------------------------------------------------------------------------
|
| Ticket conversations / notes
|
*/

CREATE TABLE sweetdesk_comments (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ticket_id BIGINT UNSIGNED NOT NULL,

    person_id BIGINT UNSIGNED NULL,

    comment LONGTEXT NOT NULL,

    is_internal TINYINT(1) NOT NULL DEFAULT 0,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_ticket_id (ticket_id),
    INDEX idx_person_id (person_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/*
|--------------------------------------------------------------------------
| SWEETDESK ATTACHMENTS
|--------------------------------------------------------------------------
*/

CREATE TABLE sweetdesk_attachments (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ticket_id BIGINT UNSIGNED NOT NULL,

    uploaded_by BIGINT UNSIGNED NULL,

    file_name VARCHAR(255) NOT NULL,
    file_url VARCHAR(500) NOT NULL,

    mime_type VARCHAR(100) NULL,

    file_size BIGINT UNSIGNED NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ticket_id (ticket_id),
    INDEX idx_uploaded_by (uploaded_by)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;