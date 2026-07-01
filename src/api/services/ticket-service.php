<?php

class SweetDesk_Ticket_Service {

    public function search_tickets(
        array $params
    ) {

        global $wpdb;

        $table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $status =
            sanitize_text_field(
                $params['status'] ?? ''
            );

        $priority =
            sanitize_text_field(
                $params['priority'] ?? ''
            );

        $assigned_to =
            absint(
                $params['assigned_to'] ?? 0
            );

        $search =
            sanitize_text_field(
                $params['search'] ?? ''
            );

        $page =
            max(
                1,
                absint(
                    $params['page'] ?? 1
                )
            );

        $per_page =
            max(
                1,
                absint(
                    $params['per_page'] ?? 25
                )
            );

        $sort =
            sanitize_text_field(
                $params['sort']
                ?? 'created_at'
            );

        $order =
            strtoupper(
                sanitize_text_field(
                    $params['order']
                    ?? 'DESC'
                )
            );

        $allowed_sorts = [
            'created_at',
            'updated_at',
            'priority',
            'status'
        ];

        if (
            !in_array(
                $sort,
                $allowed_sorts,
                true
            )
        ) {
            $sort = 'created_at';
        }

        $order =
            $order === 'ASC'
                ? 'ASC'
                : 'DESC';

        $where = [];
        $values = [];

        if ($status) {

            $where[] =
                't.status = %s';

            $values[] =
                $status;
        }

        if ($priority) {

            $where[] =
                't.priority = %s';

            $values[] =
                $priority;
        }

        if ($assigned_to) {

            $where[] =
                't.assigned_to = %d';

            $values[] =
                $assigned_to;
        }

        if ($search) {

            $where[] =
                '(t.title LIKE %s)';

            $values[] =
                '%' .
                $wpdb->esc_like(
                    $search
                ) .
                '%';
        }

        $sql = "
        SELECT
            t.id,
            t.client_id,
            t.assigned_to,
            t.created_by,
            t.title,
            t.status,
            t.priority,
            t.created_at,
            t.updated_at

        FROM {$table} t
        ";

        if (!empty($where)) {

            $sql .=
                ' WHERE ' .
                implode(
                    ' AND ',
                    $where
                );
        }

        $sql .= "
        ORDER BY " . (
            $sort === 'priority'
                ? "FIELD(t.priority, 'urgent', 'high', 'normal', 'low')"
                : $sort
        ) . " {$order}
        ";

        $offset =
            ($page - 1)
            * $per_page;

        $sql .= "
        LIMIT %d
        OFFSET %d
        ";

        $values[] =
            $per_page;

        $values[] =
            $offset;

        $prepared_sql =
            $wpdb->prepare(
                $sql,
                $values
            );

        $tickets =
            $wpdb->get_results(
                $prepared_sql,
                ARRAY_A
            );

        /*
         * Total count query
         */

        $count_sql = "
        SELECT COUNT(*)
        FROM {$table} t
        ";

        if (!empty($where)) {

            $count_sql .=
                ' WHERE ' .
                implode(
                    ' AND ',
                    $where
                );
        }

        $total =
            (int) $wpdb->get_var(
                $wpdb->prepare(
                    $count_sql,
                    array_slice(
                        $values,
                        0,
                        count($values) - 2
                    )
                )
            );

        return [

            'data' => $tickets,

            'pagination' => [

                'page' => $page,

                'per_page' => $per_page,

                'total' => $total,

                'total_pages' =>
                    ceil(
                        $total
                        / $per_page
                    )

            ],

            'filters' => [

                'status' =>
                    $status,

                'priority' =>
                    $priority,

                'assigned_to' =>
                    $assigned_to

            ],

            'sorting' => [

                'sort' =>
                    $sort,

                'order' =>
                    strtolower(
                        $order
                    )

            ]
        ];
    }

    public function create_ticket(
        array $data
    ) {

        global $wpdb;

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $meta_table =
            $wpdb->prefix .
            'sweetdesk_ticket_meta';

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        $client_id =
            absint(
                $data['client_id'] ?? 0
            );

        $assigned_to =
            absint(
                $data['assigned_to'] ?? 0
            );

        $created_by =
            absint(
                $data['created_by'] ?? get_current_user_id()
            );

        $title =
            sanitize_text_field(
                $data['title'] ?? ''
            );

        $status =
            sanitize_text_field(
                $data['status'] ?? 'open'
            );

        $priority =
            sanitize_text_field(
                $data['priority'] ?? 'normal'
            );

        $message =
            wp_kses_post(
                $data['message'] ?? ''
            );

        $custom_fields =
            $data['custom_fields'] ?? [];

        if (empty($title)) {

            return [
                'success' => false,
                'message' => 'Title is required.'
            ];
        }

        $wpdb->insert(
            $tickets_table,
            [
                'client_id'   => $client_id,
                'assigned_to' => $assigned_to,
                'created_by'  => $created_by,
                'title'       => $title,
                'status'      => $status,
                'priority'    => $priority
            ],
            [
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s'
            ]
        );

        $ticket_id =
            $wpdb->insert_id;

        if (!$ticket_id) {

            return [
                'success' => false,
                'message' => 'Failed to create ticket.'
            ];
        }

        foreach ($custom_fields as $key => $value) {

            $wpdb->insert(
                $meta_table,
                [
                    'ticket_id' => $ticket_id,
                    'meta_key'  => sanitize_key($key),
                    'meta_value'=> maybe_serialize($value)
                ],
                [
                    '%d',
                    '%s',
                    '%s'
                ]
            );
        }

        $wpdb->insert(
            $messages_table,
            [
                'ticket_id'   => $ticket_id,
                'person_id'   => $created_by,
                'reply_type'  => 'public',
                'body'        => $message,
                'visibility'  => 'public',
                'edited'      => 0,
                'reply_to_id' => null
            ],
            [
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d'
            ]
        );

        return [
            'success' => true,
            'ticket_id' => $ticket_id,
            'message' => 'Ticket created successfully.'
        ];
    }

    public function update_ticket(
        int $ticket_id,
        array $data
    ) {

        global $wpdb;

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $meta_table =
            $wpdb->prefix .
            'sweetdesk_ticket_meta';

        $ticket_exists =
            $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT id
                    FROM {$tickets_table}
                    WHERE id = %d
                    ",
                    $ticket_id
                )
            );

        if (!$ticket_exists) {

            return [
                'success' => false,
                'message' => 'Ticket not found.'
            ];
        }

        $update_data = [];

        $update_format = [];

        if (isset($data['client_id'])) {

            $update_data['client_id'] =
                absint(
                    $data['client_id']
                );

            $update_format[] = '%d';
        }

        if (isset($data['assigned_to'])) {

            $update_data['assigned_to'] =
                absint(
                    $data['assigned_to']
                );

            $update_format[] = '%d';
        }

        if (isset($data['created_by'])) {

            $update_data['created_by'] =
                absint(
                    $data['created_by']
                );

            $update_format[] = '%d';
        }

        if (isset($data['title'])) {

            $update_data['title'] =
                sanitize_text_field(
                    $data['title']
                );

            $update_format[] = '%s';
        }

        if (isset($data['status'])) {

            $update_data['status'] =
                sanitize_text_field(
                    $data['status']
                );

            $update_format[] = '%s';
        }

        if (isset($data['priority'])) {

            $update_data['priority'] =
                sanitize_text_field(
                    $data['priority']
                );

            $update_format[] = '%s';
        }

        if (!empty($update_data)) {

            $wpdb->update(
                $tickets_table,
                $update_data,
                [
                    'id' => $ticket_id
                ],
                $update_format,
                [
                    '%d'
                ]
            );
        }

        /*
        * Custom fields
        */

        if (
            !empty($data['custom_fields']) &&
            is_array(
                $data['custom_fields']
            )
        ) {

            foreach (
                $data['custom_fields']
                as $key => $value
            ) {

                $meta_key =
                    sanitize_key(
                        $key
                    );

                $meta_exists =
                    $wpdb->get_var(
                        $wpdb->prepare(
                            "
                            SELECT meta_id
                            FROM {$meta_table}
                            WHERE ticket_id = %d
                            AND meta_key = %s
                            ",
                            $ticket_id,
                            $meta_key
                        )
                    );

                if ($meta_exists) {

                    $wpdb->update(
                        $meta_table,
                        [
                            'meta_value' =>
                                maybe_serialize(
                                    $value
                                )
                        ],
                        [
                            'meta_id' =>
                                $meta_exists
                        ],
                        [
                            '%s'
                        ],
                        [
                            '%d'
                        ]
                    );

                } else {

                    $wpdb->insert(
                        $meta_table,
                        [
                            'ticket_id' =>
                                $ticket_id,

                            'meta_key' =>
                                $meta_key,

                            'meta_value' =>
                                maybe_serialize(
                                    $value
                                )
                        ],
                        [
                            '%d',
                            '%s',
                            '%s'
                        ]
                    );
                }
            }
        }

        return [
            'success' => true,
            'ticket_id' => $ticket_id,
            'message' => 'Ticket updated successfully.'
        ];
    }

    public function update_assignee(
        int $ticket_id,
        array $data
    ) {

        global $wpdb;

        $table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $assigned_to =
            absint(
                $data['assigned_to'] ?? 0
            );

        if (!$assigned_to) {

            return [
                'success' => false,
                'message' => 'assigned_to is required.'
            ];
        }

        $updated =
            $wpdb->update(
                $table,
                [
                    'assigned_to' => $assigned_to
                ],
                [
                    'id' => $ticket_id
                ],
                [
                    '%d'
                ],
                [
                    '%d'
                ]
            );

        if ($updated === false) {

            return [
                'success' => false,
                'message' => 'Failed to update assignee.'
            ];
        }

        return [
            'success' => true,
            'ticket_id' => $ticket_id,
            'assigned_to' => $assigned_to
        ];
    }

    public function update_status(
        int $ticket_id,
        array $data
    ) {

        global $wpdb;

        $table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $status =
            sanitize_text_field(
                $data['status'] ?? ''
            );

        if (empty($status)) {

            return [
                'success' => false,
                'message' => 'status is required.'
            ];
        }

        $updated =
            $wpdb->update(
                $table,
                [
                    'status' => $status
                ],
                [
                    'id' => $ticket_id
                ],
                [
                    '%s'
                ],
                [
                    '%d'
                ]
            );

        if ($updated === false) {

            return [
                'success' => false,
                'message' => 'Failed to update status.'
            ];
        }

        return [
            'success' => true,
            'ticket_id' => $ticket_id,
            'status' => $status
        ];
    }

    public function delete_ticket(
        int $ticket_id
    ) {

        global $wpdb;

        $tickets =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $meta =
            $wpdb->prefix .
            'sweetdesk_ticket_meta';

        $messages =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        $attachments =
            $wpdb->prefix .
            'sweetdesk_attachments';

        $activity =
            $wpdb->prefix .
            'sweetdesk_ticket_activity';

        $exists =
            $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT id
                    FROM {$tickets}
                    WHERE id = %d
                    ",
                    $ticket_id
                )
            );

        if (!$exists) {

            return [
                'success' => false,
                'message' => 'Ticket not found.'
            ];
        }

        $wpdb->delete(
            $meta,
            [
                'ticket_id' => $ticket_id
            ],
            [
                '%d'
            ]
        );

        $wpdb->delete(
            $messages,
            [
                'ticket_id' => $ticket_id
            ],
            [
                '%d'
            ]
        );

        $wpdb->delete(
            $attachments,
            [
                'ticket_id' => $ticket_id
            ],
            [
                '%d'
            ]
        );

        $wpdb->delete(
            $activity,
            [
                'ticket_id' => $ticket_id
            ],
            [
                '%d'
            ]
        );

        $wpdb->delete(
            $tickets,
            [
                'id' => $ticket_id
            ],
            [
                '%d'
            ]
        );

        return [
            'success' => true,
            'ticket_id' => $ticket_id,
            'message' => 'Ticket deleted successfully.'
        ];
    }

    public function get_ticket(
        int $ticket_id
    ) {

        global $wpdb;

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $meta_table =
            $wpdb->prefix .
            'sweetdesk_ticket_meta';

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        /*
        * Ticket
        */

        $ticket =
            $wpdb->get_row(
                $wpdb->prepare(
                    "
                    SELECT
                        id,
                        client_id,
                        assigned_to,
                        created_by,
                        title,
                        status,
                        priority,
                        created_at,
                        updated_at

                    FROM {$tickets_table}

                    WHERE id = %d
                    ",
                    $ticket_id
                ),
                ARRAY_A
            );

        if (!$ticket) {

            return [
                'success' => false,
                'message' => 'Ticket not found.'
            ];
        }

        /*
        * Custom Fields
        */

        $custom_fields =
            $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT
                        meta_id,
                        ticket_id,
                        meta_key,
                        meta_value

                    FROM {$meta_table}

                    WHERE ticket_id = %d

                    ORDER BY meta_key ASC
                    ",
                    $ticket_id
                ),
                ARRAY_A
            );

        /*
        * Messages
        */

        $messages =
            $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT
                        id AS reply_id,
                        ticket_id,
                        person_id,
                        reply_type,
                        body,
                        visibility,
                        edited,
                        reply_to_id,
                        created_at,
                        updated_at

                    FROM {$messages_table}

                    WHERE ticket_id = %d

                    ORDER BY created_at ASC
                    ",
                    $ticket_id
                ),
                ARRAY_A
            );

        return [

            'ticket' => $ticket,

            'custom_fields' =>
                $custom_fields,

            'messages' =>
                $messages

        ];
    }

    public function export_tickets(
        array $params
    ) {

        global $wpdb;

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $meta_table =
            $wpdb->prefix .
            'sweetdesk_ticket_meta';

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        $start_date =
            sanitize_text_field(
                $params['start_date'] ?? ''
            );

        $end_date =
            sanitize_text_field(
                $params['end_date'] ?? ''
            );

        if (
            empty($start_date)
            || empty($end_date)
        ) {

            return [
                'success' => false,
                'message' =>
                    'start_date and end_date are required.'
            ];
        }

        $tickets =
            $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT
                        id,
                        client_id,
                        assigned_to,
                        created_by,
                        title,
                        status,
                        priority,
                        created_at,
                        updated_at

                    FROM {$tickets_table}

                    WHERE DATE(created_at)
                        BETWEEN %s AND %s

                    ORDER BY created_at ASC
                    ",
                    $start_date,
                    $end_date
                ),
                ARRAY_A
            );

        $export_data = [];

        foreach (
            $tickets as $ticket
        ) {

            $ticket_id =
                (int) $ticket['id'];

            $custom_fields =
                $wpdb->get_results(
                    $wpdb->prepare(
                        "
                        SELECT
                            meta_id,
                            ticket_id,
                            meta_key,
                            meta_value

                        FROM {$meta_table}

                        WHERE ticket_id = %d
                        ",
                        $ticket_id
                    ),
                    ARRAY_A
                );

            $messages =
                $wpdb->get_results(
                    $wpdb->prepare(
                        "
                        SELECT
                            id AS reply_id,
                            ticket_id,
                            person_id,
                            reply_type,
                            body,
                            visibility,
                            edited,
                            reply_to_id,
                            created_at,
                            updated_at

                        FROM {$messages_table}

                        WHERE ticket_id = %d

                        ORDER BY created_at ASC
                        ",
                        $ticket_id
                    ),
                    ARRAY_A
                );

            $export_data[] = [

                'ticket' =>
                    $ticket,

                'custom_fields' =>
                    $custom_fields,

                'messages' =>
                    $messages

            ];
        }

        return [

            'exported_at' =>
                current_time(
                    'mysql'
                ),

            'start_date' =>
                $start_date,

            'end_date' =>
                $end_date,

            'total_tickets' =>
                count(
                    $export_data
                ),

            'tickets' =>
                $export_data

        ];
    }

    public function import_tickets(
        array $payload
    ) {

        global $wpdb;

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $meta_table =
            $wpdb->prefix .
            'sweetdesk_ticket_meta';

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        $tickets =
            $payload['tickets']
            ?? [];

        if (
            empty($tickets)
            || !is_array($tickets)
        ) {

            return [
                'success' => false,
                'message' => 'No tickets found.'
            ];
        }

        $imported = 0;

        foreach ($tickets as $ticket_data) {

            $ticket =
                $ticket_data['ticket']
                ?? [];

            $custom_fields =
                $ticket_data['custom_fields']
                ?? [];

            $messages =
                $ticket_data['messages']
                ?? [];

            /*
            * Create ticket
            */

            $wpdb->insert(
                $tickets_table,
                [
                    'client_id' =>
                        $ticket['client_id']
                        ?? null,

                    'assigned_to' =>
                        $ticket['assigned_to']
                        ?? null,

                    'created_by' =>
                        $ticket['created_by']
                        ?? null,

                    'title' =>
                        $ticket['title']
                        ?? '',

                    'status' =>
                        $ticket['status']
                        ?? 'open',

                    'priority' =>
                        $ticket['priority']
                        ?? 'normal'
                ],
                [
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%s'
                ]
            );

            $new_ticket_id =
                $wpdb->insert_id;

            /*
            * Custom Fields
            */

            foreach (
                $custom_fields as $field
            ) {

                $wpdb->insert(
                    $meta_table,
                    [
                        'ticket_id' =>
                            $new_ticket_id,

                        'meta_key' =>
                            $field['meta_key']
                            ?? '',

                        'meta_value' =>
                            $field['meta_value']
                            ?? null
                    ],
                    [
                        '%d',
                        '%s',
                        '%s'
                    ]
                );
            }

            /*
            * Messages
            */

            $reply_map = [];

            foreach (
                $messages as $message
            ) {

                $old_reply_id =
                    $message['reply_id']
                    ?? null;

                $wpdb->insert(
                    $messages_table,
                    [
                        'ticket_id' =>
                            $new_ticket_id,

                        'person_id' =>
                            $message['person_id']
                            ?? null,

                        'reply_type' =>
                            $message['reply_type']
                            ?? 'public',

                        'body' =>
                            $message['body']
                            ?? '',

                        'visibility' =>
                            $message['visibility']
                            ?? 'public',

                        'edited' =>
                            $message['edited']
                            ?? 0,

                        'reply_to_id' =>
                            null
                    ],
                    [
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%d'
                    ]
                );

                if ($old_reply_id) {

                    $reply_map[
                        $old_reply_id
                    ] =
                        $wpdb->insert_id;
                }
            }

            /*
            * Second pass:
            * rebuild reply_to_id links
            */

            foreach (
                $messages as $index => $message
            ) {

                if (
                    empty(
                        $message['reply_to_id']
                    )
                ) {
                    continue;
                }

                $old_reply_id =
                    $message['reply_id'];

                $new_reply_id =
                    $reply_map[
                        $old_reply_id
                    ] ?? null;

                $new_parent_id =
                    $reply_map[
                        $message['reply_to_id']
                    ] ?? null;

                if (
                    $new_reply_id
                    && $new_parent_id
                ) {

                    $wpdb->update(
                        $messages_table,
                        [
                            'reply_to_id' =>
                                $new_parent_id
                        ],
                        [
                            'id' =>
                                $new_reply_id
                        ],
                        [
                            '%d'
                        ],
                        [
                            '%d'
                        ]
                    );
                }
            }

            $imported++;
        }

        return [

            'success' => true,

            'tickets_imported' =>
                $imported,

            'message' =>
                'Import completed successfully.'

        ];
    }

    public function create_message(
        int $ticket_id,
        array $data
    ) {

        global $wpdb;

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        /*
        * Verify ticket exists
        */

        $ticket_exists =
            $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT id
                    FROM {$tickets_table}
                    WHERE id = %d
                    ",
                    $ticket_id
                )
            );

        if (!$ticket_exists) {

            return [
                'success' => false,
                'message' => 'Ticket not found.'
            ];
        }

        /*
        * Message fields
        */

        $person_id =
            get_current_user_id();

        $body =
            wp_kses_post(
                $data['body'] ?? ''
            );

        $reply_type =
            sanitize_text_field(
                $data['reply_type']
                ?? 'public'
            );

        $visibility =
            sanitize_text_field(
                $data['visibility']
                ?? 'public'
            );

        $reply_to_id =
            !empty($data['reply_to_id'])
                ? absint(
                    $data['reply_to_id']
                )
                : null;

        if (empty(trim(wp_strip_all_tags($body)))) {

            return [
                'success' => false,
                'message' => 'Message body is required.'
            ];
        }

        /*
        * Insert message
        */

        $inserted =
            $wpdb->insert(
                $messages_table,
                [
                    'ticket_id' =>
                        $ticket_id,

                    'person_id' =>
                        $person_id,

                    'reply_type' =>
                        $reply_type,

                    'body' =>
                        $body,

                    'visibility' =>
                        $visibility,

                    'edited' => 0,

                    'reply_to_id' =>
                        $reply_to_id,

                    'created_at' =>
                        current_time('mysql'),

                    'updated_at' =>
                        current_time('mysql')
                ],
                [
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%d',
                    '%s',
                    '%s'
                ]
            );

        if (!$inserted) {

            return [
                'success' => false,
                'message' => 'Unable to create message.'
            ];
        }

        /*
        * Update ticket fields
        */

        $ticket_updates = [];

        if (isset($data['client_id'])) {

            $ticket_updates['client_id'] =
                absint(
                    $data['client_id']
                );
        }

        if (isset($data['assigned_to'])) {

            $ticket_updates['assigned_to'] =
                absint(
                    $data['assigned_to']
                );
        }

        if (isset($data['status'])) {

            $ticket_updates['status'] =
                sanitize_text_field(
                    $data['status']
                );
        }

        if (isset($data['priority'])) {

            $ticket_updates['priority'] =
                sanitize_text_field(
                    $data['priority']
                );
        }

        $ticket_updates['updated_at'] =
            current_time('mysql');

        $wpdb->update(
            $tickets_table,
            $ticket_updates,
            [
                'id' => $ticket_id
            ]
        );

        return [

            'success' => true,

            'message_id' =>
                $wpdb->insert_id,

            'ticket_id' =>
                $ticket_id,

            'message' =>
                'Reply created successfully.'

        ];
    }

    public function get_messages(
        int $ticket_id
    ) {

        global $wpdb;

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        /*
        * Verify ticket exists
        */

        $ticket_exists =
            $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT id
                    FROM {$tickets_table}
                    WHERE id = %d
                    ",
                    $ticket_id
                )
            );

        if (!$ticket_exists) {

            return [
                'success' => false,
                'message' => 'Ticket not found.'
            ];
        }

        $messages =
            $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT
                        id AS reply_id,
                        body,
                        visibility,
                        edited,
                        reply_to_id,
                        created_at,
                        updated_at

                    FROM {$messages_table}

                    WHERE ticket_id = %d

                    ORDER BY created_at ASC
                    ",
                    $ticket_id
                ),
                ARRAY_A
            );

        return [

            'success' => true,

            'ticket_id' =>
                $ticket_id,

            'total_messages' =>
                count($messages),

            'messages' =>
                $messages

        ];
    }

    public function update_message(
        int $message_id,
        array $data
    ) {

        global $wpdb;

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        /*
        * Verify message exists
        */

        $message =
            $wpdb->get_row(
                $wpdb->prepare(
                    "
                    SELECT
                        id,
                        ticket_id

                    FROM {$messages_table}

                    WHERE id = %d
                    ",
                    $message_id
                ),
                ARRAY_A
            );

        if (!$message) {

            return [
                'success' => false,
                'message' => 'Message not found.'
            ];
        }

        $ticket_id =
            (int) $message['ticket_id'];

        /*
        * Update message
        */

        $message_updates = [];

        if (isset($data['body'])) {

            $message_updates['body'] =
                wp_kses_post(
                    $data['body']
                );
        }

        if (isset($data['visibility'])) {

            $message_updates['visibility'] =
                sanitize_text_field(
                    $data['visibility']
                );
        }

        if (isset($data['reply_to_id'])) {

            $message_updates['reply_to_id'] =
                !empty($data['reply_to_id'])
                    ? absint(
                        $data['reply_to_id']
                    )
                    : null;
        }

        /*
        * Message was edited
        */

        $message_updates['edited'] = 1;

        $message_updates['updated_at'] =
            current_time('mysql');

        if (!empty($message_updates)) {

            $wpdb->update(
                $messages_table,
                $message_updates,
                [
                    'id' => $message_id
                ]
            );
        }

        /*
        * Optional ticket updates
        */

        $ticket_updates = [];

        if (isset($data['client_id'])) {

            $ticket_updates['client_id'] =
                absint(
                    $data['client_id']
                );
        }

        if (isset($data['assigned_to'])) {

            $ticket_updates['assigned_to'] =
                absint(
                    $data['assigned_to']
                );
        }

        if (isset($data['status'])) {

            $ticket_updates['status'] =
                sanitize_text_field(
                    $data['status']
                );
        }

        if (isset($data['priority'])) {

            $ticket_updates['priority'] =
                sanitize_text_field(
                    $data['priority']
                );
        }

        $ticket_updates['updated_at'] =
            current_time('mysql');

        $wpdb->update(
            $tickets_table,
            $ticket_updates,
            [
                'id' => $ticket_id
            ]
        );

        return [

            'success' => true,

            'message_id' =>
                $message_id,

            'ticket_id' =>
                $ticket_id,

            'message' =>
                'Message updated successfully.'

        ];
    }

    public function delete_message(
        int $message_id
    ) {

        global $wpdb;

        $messages_table =
            $wpdb->prefix .
            'sweetdesk_ticket_messages';

        $tickets_table =
            $wpdb->prefix .
            'sweetdesk_tickets';

        /*
        * Find message
        */

        $message =
            $wpdb->get_row(
                $wpdb->prepare(
                    "
                    SELECT
                        id,
                        ticket_id
                    FROM {$messages_table}
                    WHERE id = %d
                    ",
                    $message_id
                ),
                ARRAY_A
            );

        if (!$message) {

            return [
                'success' => false,
                'message' => 'Message not found.'
            ];
        }

        $ticket_id =
            (int) $message['ticket_id'];

        /*
        * Prevent orphaned replies
        *
        * Optional safeguard:
        * if replies exist, block deletion.
        */

        $child_count =
            (int) $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT COUNT(*)
                    FROM {$messages_table}
                    WHERE reply_to_id = %d
                    ",
                    $message_id
                )
            );

        if ($child_count > 0) {

            return [
                'success' => false,
                'message' =>
                    'Cannot delete a message that has replies.'
            ];
        }

        /*
        * Delete message
        */

        $deleted =
            $wpdb->delete(
                $messages_table,
                [
                    'id' => $message_id
                ],
                [
                    '%d'
                ]
            );

        if (!$deleted) {

            return [
                'success' => false,
                'message' =>
                    'Failed to delete message.'
            ];
        }

        /*
        * Update ticket timestamp
        */

        $wpdb->update(
            $tickets_table,
            [
                'updated_at' =>
                    current_time('mysql')
            ],
            [
                'id' => $ticket_id
            ],
            [
                '%s'
            ],
            [
                '%d'
            ]
        );

        return [

            'success' => true,

            'message_id' =>
                $message_id,

            'ticket_id' =>
                $ticket_id,

            'message' =>
                'Message deleted successfully.'

        ];
    }
}