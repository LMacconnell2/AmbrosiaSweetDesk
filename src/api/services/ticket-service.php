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
        ORDER BY {$sort} {$order}
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
}