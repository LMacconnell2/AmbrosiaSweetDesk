<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Analytics_Service
{
    private wpdb $wpdb;

    private string $tickets_table;
    private string $feedback_table;
    private string $messages_table;
    private string $people_table;
    private string $people_teams_table;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->tickets_table = $wpdb->prefix . 'sweetdesk_tickets';
        $this->feedback_table = $wpdb->prefix . 'sweetdesk_ticket_feedback';
        $this->messages_table = $wpdb->prefix . 'sweetdesk_ticket_messages';
        $this->people_table = $wpdb->prefix . 'sweetdesk_people';
        $this->people_teams_table = $wpdb->prefix . 'sweetdesk_people_teams';
    }

    public function get_summary(array $filters)
    {
        $ticket_metrics = $this->get_ticket_metrics($filters);

        if (is_wp_error($ticket_metrics)) {
            return $ticket_metrics;
        }

        $resolution_metrics = $this->get_resolution_metrics($filters);

        if (is_wp_error($resolution_metrics)) {
            return $resolution_metrics;
        }

        $feedback_metrics = $this->get_feedback_metrics($filters);

        if (is_wp_error($feedback_metrics)) {
            return $feedback_metrics;
        }

        return [
            'scope' => $filters['scope'],
            'person_id' => $filters['person_id'],
            'date_range' => [
                'start' => $filters['date_start'],
                'end' => $filters['date_end'],
            ],
            'tickets' => $ticket_metrics,
            'resolution_time' => $resolution_metrics,
            'feedback' => $feedback_metrics,
        ];
    }

    public function get_oldest_unresolved(array $filters)
    {
        $scope = $filters['scope'];
        $person_id = $filters['person_id'] ?? null;
        $limit = min(100, max(1, (int) ($filters['limit'] ?? 3)));

        $where = [
            't.resolved_at IS NULL',
            "t.status NOT IN ('closed', 'resolved')",
        ];

        $params = [];

        if ($scope === 'user') {
            $where[] = 't.assigned_to = %d';
            $params[] = $person_id;
        }

        $sql = "
            SELECT
                t.id,
                t.title,
                t.status,
                t.created_at,
                TIMESTAMPDIFF(
                    SECOND,
                    t.created_at,
                    UTC_TIMESTAMP()
                ) AS age_seconds,
                p.id AS assignee_id,
                p.first_name AS assignee_first_name,
                p.last_name AS assignee_last_name
            FROM {$this->tickets_table} t
            LEFT JOIN {$this->people_table} p
                ON p.id = t.assigned_to
            WHERE " . implode(' AND ', $where) . "
            ORDER BY t.created_at ASC, t.id ASC
            LIMIT %d
        ";

        $params[] = $limit;

        $rows = $this->wpdb->get_results(
            $this->prepare($sql, $params),
            ARRAY_A
        );

        if ($this->wpdb->last_error) {
            return $this->database_error(
                'Could not retrieve unresolved tickets.'
            );
        }

        return array_map(
            static function (array $row) use ($scope): array {
                $ticket = [
                    'id' => (int) $row['id'],
                    'title' => $row['title'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'age_seconds' => (int) $row['age_seconds'],
                ];

                if ($scope === 'company') {
                    $ticket['assignee'] = $row['assignee_id']
                        ? [
                            'id' => (int) $row['assignee_id'],
                            'first_name' => $row['assignee_first_name'],
                            'last_name' => $row['assignee_last_name'],
                        ]
                        : null;
                }

                return $ticket;
            },
            $rows ?: []
        );
    }

    public function get_recent_messages(array $filters)
    {
        $person_id = $filters['person_id'] ?? null;
        $source = $filters['source'] ?? 'all';
        $limit = min(100, max(1, (int) ($filters['limit'] ?? 5)));

        $where = ['1 = 1'];
        $params = [];

        /*
         * For employee-specific analytics, include:
         * - messages on tickets assigned to the employee
         * - messages authored by the employee
         */
        if ($person_id) {
            $where[] = '(t.assigned_to = %d OR m.person_id = %d)';
            $params[] = $person_id;
            $params[] = $person_id;
        }

        if (!empty($filters['date_start'])) {
            $where[] = 'm.created_at >= %s';
            $params[] = $filters['date_start'] . ' 00:00:00';
        }

        if (!empty($filters['date_end_exclusive'])) {
            $where[] = 'm.created_at < %s';
            $params[] = $filters['date_end_exclusive'];
        }

        /*
         * Internal people are identified by wp_user_id.
         * External people without wp_user_id are treated as customers.
         */
        if ($source === 'customer') {
            $where[] = 'p.wp_user_id IS NULL';
        } elseif ($source === 'staff') {
            $where[] = 'p.wp_user_id IS NOT NULL';
        }

        $sql = "
            SELECT
                m.id,
                m.body,
                m.created_at,
                m.person_id,

                t.id AS ticket_id,
                t.title AS ticket_title,

                p.first_name,
                p.last_name,

                CASE
                    WHEN p.wp_user_id IS NULL THEN 'customer'
                    ELSE 'staff'
                END AS message_source

            FROM {$this->messages_table} m

            INNER JOIN {$this->tickets_table} t
                ON t.id = m.ticket_id

            LEFT JOIN {$this->people_table} p
                ON p.id = m.person_id

            WHERE " . implode(' AND ', $where) . "

            ORDER BY m.created_at DESC, m.id DESC
            LIMIT %d
        ";

        $params[] = $limit;

        $rows = $this->wpdb->get_results(
            $this->prepare($sql, $params),
            ARRAY_A
        );

        if ($this->wpdb->last_error) {
            return $this->database_error(
                'Could not retrieve recent messages.'
            );
        }

        return array_map(
            static function (array $row): array {
                $display_name = trim(
                    ($row['first_name'] ?? '') .
                    ' ' .
                    ($row['last_name'] ?? '')
                );

                return [
                    'id' => (int) $row['id'],
                    'ticket' => [
                        'id' => (int) $row['ticket_id'],
                        'title' => $row['ticket_title'],
                    ],
                    'author' => [
                        'id' => $row['person_id']
                            ? (int) $row['person_id']
                            : null,
                        'display_name' => $display_name !== ''
                            ? $display_name
                            : __('Unknown author', 'sweetdesk'),
                    ],
                    'source' => $row['message_source'],
                    'body' => $row['body'],
                    'created_at' => $row['created_at'],
                ];
            },
            $rows ?: []
        );
    }

    public function build_csv_export(array $export_data): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        /*
         * UTF-8 BOM improves compatibility with Microsoft Excel.
         */
        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, ['section', 'metric', 'value']);

        $this->write_csv_values(
            $stream,
            'summary',
            $export_data['summary'] ?? []
        );

        $this->write_csv_values(
            $stream,
            'oldest_unresolved',
            $export_data['oldest_unresolved'] ?? []
        );

        $this->write_csv_values(
            $stream,
            'recent_messages',
            $export_data['recent_messages'] ?? []
        );

        rewind($stream);

        $contents = stream_get_contents($stream);

        fclose($stream);

        return $contents !== false ? $contents : '';
    }

    private function get_ticket_metrics(array $filters)
    {
        $member_count = $this->get_member_count($filters);

        if (is_wp_error($member_count)) {
            return $member_count;
        }

        $received_where = [
            't.created_at >= %s',
            't.created_at < %s',
        ];

        $received_params = [
            $filters['date_start_sql'],
            $filters['date_end_exclusive'],
        ];

        $cleared_where = [
            't.resolved_at IS NOT NULL',
            't.resolved_at >= %s',
            't.resolved_at < %s',
        ];

        $cleared_params = [
            $filters['date_start_sql'],
            $filters['date_end_exclusive'],
        ];

        $assigned_where = [
            't.assigned_to = %d',
            't.created_at >= %s',
            't.created_at < %s',
        ];

        $assigned_params = [
            $filters['person_id'],
            $filters['date_start_sql'],
            $filters['date_end_exclusive'],
        ];

        if (!empty($filters['team_id'])) {
            $team_clause = "
                EXISTS (
                    SELECT 1
                    FROM {$this->people_teams_table} pt
                    WHERE pt.person_id = t.assigned_to
                      AND pt.team_id = %d
                )
            ";

            $received_where[] = $team_clause;
            $received_params[] = $filters['team_id'];

            $cleared_where[] = $team_clause;
            $cleared_params[] = $filters['team_id'];

            $assigned_where[] = $team_clause;
            $assigned_params[] = $filters['team_id'];
        }

        if ($filters['scope'] === 'user') {
            $cleared_where[] = 't.assigned_to = %d';
            $cleared_params[] = $filters['person_id'];
        }

        $received = $this->count_tickets(
            $received_where,
            $received_params
        );

        $cleared = $this->count_tickets(
            $cleared_where,
            $cleared_params
        );

        if (is_wp_error($received)) {
            return $received;
        }

        if (is_wp_error($cleared)) {
            return $cleared;
        }

        if ($filters['scope'] === 'user') {
            $assigned = $this->count_tickets(
                $assigned_where,
                $assigned_params
            );

            if (is_wp_error($assigned)) {
                return $assigned;
            }

            return [
                'assigned' => $assigned,
                'cleared' => $cleared,
                'received' => null,
                'average_received_per_member' => null,
                'average_cleared_per_member' => null,
            ];
        }

        return [
            'received' => $received,
            'cleared' => $cleared,
            'assigned' => null,
            'average_received_per_member' => $member_count > 0
                ? round($received / $member_count, 2)
                : null,
            'average_cleared_per_member' => $member_count > 0
                ? round($cleared / $member_count, 2)
                : null,
        ];
    }

    private function get_resolution_metrics(array $filters)
    {
        $where = [
            't.resolved_at IS NOT NULL',
            't.resolved_at >= %s',
            't.resolved_at < %s',
        ];

        $params = [
            $filters['date_start_sql'],
            $filters['date_end_exclusive'],
        ];

        if ($filters['scope'] === 'user') {
            $where[] = 't.assigned_to = %d';
            $params[] = $filters['person_id'];
        }

        if (!empty($filters['team_id'])) {
            $where[] = "
                EXISTS (
                    SELECT 1
                    FROM {$this->people_teams_table} pt
                    WHERE pt.person_id = t.assigned_to
                      AND pt.team_id = %d
                )
            ";
            $params[] = $filters['team_id'];
        }

        $sql = "
            SELECT
                t.id,
                t.title,
                t.assigned_to,
                TIMESTAMPDIFF(
                    SECOND,
                    t.created_at,
                    t.resolved_at
                ) AS resolution_seconds
            FROM {$this->tickets_table} t
            WHERE " . implode(' AND ', $where) . "
            ORDER BY resolution_seconds ASC, t.id ASC
        ";

        $rows = $this->wpdb->get_results(
            $this->prepare($sql, $params),
            ARRAY_A
        );

        if ($this->wpdb->last_error) {
            return $this->database_error(
                'Could not calculate resolution metrics.'
            );
        }

        $rows = $rows ?: [];

        $values = array_map(
            static fn(array $row): int =>
                max(0, (int) $row['resolution_seconds']),
            $rows
        );

        return [
            'count' => count($values),
            'median_seconds' => $this->median($values),
            'average_seconds' => $this->average($values),
            'average_per_member_seconds' =>
                $this->average_grouped_by_person(
                    $rows,
                    'assigned_to',
                    'resolution_seconds'
                ),
            'minimum' => $this->resolution_extreme($rows, 'minimum'),
            'maximum' => $this->resolution_extreme($rows, 'maximum'),
        ];
    }

    private function get_feedback_metrics(array $filters)
    {
        $where = [
            'f.submitted_at >= %s',
            'f.submitted_at < %s',
        ];

        $params = [
            $filters['date_start_sql'],
            $filters['date_end_exclusive'],
        ];

        if ($filters['scope'] === 'user') {
            $where[] = 't.assigned_to = %d';
            $params[] = $filters['person_id'];
        }

        if (!empty($filters['team_id'])) {
            $where[] = "
                EXISTS (
                    SELECT 1
                    FROM {$this->people_teams_table} pt
                    WHERE pt.person_id = t.assigned_to
                      AND pt.team_id = %d
                )
            ";
            $params[] = $filters['team_id'];
        }

        $sql = "
            SELECT
                f.id,
                f.score,
                t.id AS ticket_id,
                t.title AS ticket_title,
                t.assigned_to
            FROM {$this->feedback_table} f
            INNER JOIN {$this->tickets_table} t
                ON t.id = f.ticket_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY f.score ASC, f.id ASC
        ";

        $rows = $this->wpdb->get_results(
            $this->prepare($sql, $params),
            ARRAY_A
        );

        if ($this->wpdb->last_error) {
            return $this->database_error(
                'Could not calculate feedback metrics.'
            );
        }

        $rows = $rows ?: [];

        $scores = array_map(
            static fn(array $row): int => (int) $row['score'],
            $rows
        );

        return [
            'count' => count($scores),
            'median' => $this->median($scores),
            'average' => $this->average($scores),
            'average_per_member' =>
                $this->average_grouped_by_person(
                    $rows,
                    'assigned_to',
                    'score'
                ),
            'minimum' => $this->feedback_extreme($rows, 'minimum'),
            'maximum' => $this->feedback_extreme($rows, 'maximum'),
        ];
    }

    private function get_member_count(array $filters)
    {
        $where = [
            'p.wp_user_id IS NOT NULL',
            'p.is_active = 1',
        ];

        $params = [];

        if (!empty($filters['team_id'])) {
            $where[] = "
                EXISTS (
                    SELECT 1
                    FROM {$this->people_teams_table} pt
                    WHERE pt.person_id = p.id
                      AND pt.team_id = %d
                )
            ";
            $params[] = $filters['team_id'];
        }

        $sql = "
            SELECT COUNT(DISTINCT p.id)
            FROM {$this->people_table} p
            WHERE " . implode(' AND ', $where);

        $query = $params
            ? $this->prepare($sql, $params)
            : $sql;

        $count = $this->wpdb->get_var($query);

        if ($this->wpdb->last_error) {
            return $this->database_error(
                'Could not count active SweetDesk members.'
            );
        }

        return (int) $count;
    }

    private function count_tickets(array $where, array $params)
    {
        $sql = "
            SELECT COUNT(*)
            FROM {$this->tickets_table} t
            WHERE " . implode(' AND ', $where);

        $count = $this->wpdb->get_var(
            $this->prepare($sql, $params)
        );

        if ($this->wpdb->last_error) {
            return $this->database_error(
                'Could not calculate ticket totals.'
            );
        }

        return (int) $count;
    }

    private function resolution_extreme(
        array $rows,
        string $type
    ): ?array {
        if (empty($rows)) {
            return null;
        }

        $row = $type === 'maximum'
            ? $rows[array_key_last($rows)]
            : $rows[0];

        return [
            'seconds' => max(0, (int) $row['resolution_seconds']),
            'ticket' => [
                'id' => (int) $row['id'],
                'title' => $row['title'],
            ],
        ];
    }

    private function feedback_extreme(
        array $rows,
        string $type
    ): ?array {
        if (empty($rows)) {
            return null;
        }

        $row = $type === 'maximum'
            ? $rows[array_key_last($rows)]
            : $rows[0];

        return [
            'score' => (int) $row['score'],
            'ticket' => [
                'id' => (int) $row['ticket_id'],
                'title' => $row['ticket_title'],
            ],
        ];
    }

    private function average_grouped_by_person(
        array $rows,
        string $person_key,
        string $value_key
    ): ?float {
        $groups = [];

        foreach ($rows as $row) {
            $person_id = isset($row[$person_key])
                ? (int) $row[$person_key]
                : 0;

            /*
             * Unassigned tickets are not treated as an employee.
             */
            if ($person_id < 1) {
                continue;
            }

            $groups[$person_id][] = (float) $row[$value_key];
        }

        if (empty($groups)) {
            return null;
        }

        $member_averages = [];

        foreach ($groups as $values) {
            $member_averages[] = array_sum($values) / count($values);
        }

        return round(
            array_sum($member_averages) / count($member_averages),
            2
        );
    }

    private function median(array $values): ?float
    {
        if (empty($values)) {
            return null;
        }

        sort($values, SORT_NUMERIC);

        $count = count($values);
        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return round((float) $values[$middle], 2);
        }

        return round(
            ((float) $values[$middle - 1] +
                (float) $values[$middle]) / 2,
            2
        );
    }

    private function average(array $values): ?float
    {
        if (empty($values)) {
            return null;
        }

        return round(array_sum($values) / count($values), 2);
    }

    private function write_csv_values(
        $stream,
        string $section,
        array $values,
        string $prefix = ''
    ): void {
        foreach ($values as $key => $value) {
            $metric = $prefix === ''
                ? (string) $key
                : $prefix . '.' . $key;

            if (is_array($value)) {
                $this->write_csv_values(
                    $stream,
                    $section,
                    $value,
                    $metric
                );

                continue;
            }

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = '';
            }

            fputcsv($stream, [
                $section,
                $metric,
                $value,
            ]);
        }
    }

    private function prepare(string $sql, array $params): string
    {
        if (empty($params)) {
            return $sql;
        }

        return $this->wpdb->prepare($sql, ...$params);
    }

    private function database_error(string $message): WP_Error
    {
        if (
            defined('WP_DEBUG') &&
            WP_DEBUG &&
            $this->wpdb->last_error
        ) {
            error_log(
                'SweetDesk analytics database error: ' .
                $this->wpdb->last_error
            );
        }

        return new WP_Error(
            'sweetdesk_analytics_database_error',
            __($message, 'sweetdesk'),
            ['status' => 500]
        );
    }
}