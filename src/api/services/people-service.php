<?php

if (!defined('ABSPATH')) exit;

class SweetDesk_People_Service {

    private wpdb $db;
    private string $people_table;
    private string $people_meta_table;
    private string $people_teams_table;
    private string $teams_table;

    public function __construct() {
        global $wpdb;

        $this->db = $wpdb;
        $this->people_table = $wpdb->prefix . 'sweetdesk_people';
        $this->people_meta_table = $wpdb->prefix . 'sweetdesk_people_meta';
        $this->people_teams_table = $wpdb->prefix . 'sweetdesk_people_teams';
        $this->teams_table = $wpdb->prefix . 'sweetdesk_teams';
    }

    public function get_people(array $args): array {
        $page = max(1, (int) ($args['page'] ?? 1));
        $per_page = min(100, max(1, (int) ($args['per_page'] ?? 25)));
        $offset = ($page - 1) * $per_page;

        $roles = $this->csv_strings($args['roles'] ?? '');
        $team_ids = $this->csv_ints($args['team_ids'] ?? '');
        $client_ids = $this->csv_ints($args['client_ids'] ?? '');

        $allowed_sort = [
            'first_name',
            'last_name',
            'email',
            'role',
            'created_at',
            'updated_at',
        ];

        $requested_sort = $args['sort'] ?? 'last_name';
        $sort = in_array($requested_sort, $allowed_sort, true)
            ? $requested_sort
            : 'last_name';

        $order = strtolower($args['order'] ?? 'asc') === 'desc'
            ? 'DESC'
            : 'ASC';

        $joins = '';
        $where = 'WHERE 1=1';
        $params = [];

        /*
        * This join is only used when filtering by teams.
        *
        * Teams returned with each person are loaded separately after pagination.
        * That prevents a person with multiple teams from creating duplicate rows
        * or interfering with LIMIT/OFFSET pagination.
        */
        if (!empty($team_ids)) {
            $team_placeholders = implode(
                ',',
                array_fill(0, count($team_ids), '%d')
            );

            $joins .= "
                INNER JOIN {$this->people_teams_table} pt_filter
                    ON pt_filter.person_id = p.id
            ";

            $where .= "
                AND pt_filter.team_id IN ({$team_placeholders})
            ";

            $params = array_merge($params, $team_ids);
        }

        if (!empty($args['q'])) {
            $like = '%' . $this->db->esc_like($args['q']) . '%';

            $where .= '
                AND (
                    p.first_name LIKE %s
                    OR p.last_name LIKE %s
                    OR p.email LIKE %s
                )
            ';

            array_push($params, $like, $like, $like);
        }

        if (!empty($roles)) {
            $role_placeholders = implode(
                ',',
                array_fill(0, count($roles), '%s')
            );

            $where .= "
                AND p.role IN ({$role_placeholders})
            ";

            $params = array_merge($params, $roles);
        }

        if (!empty($client_ids)) {
            $client_placeholders = implode(
                ',',
                array_fill(0, count($client_ids), '%d')
            );

            $where .= "
                AND p.client_id IN ({$client_placeholders})
            ";

            $params = array_merge($params, $client_ids);
        }

        $internal = null;

        if (
            array_key_exists('internal', $args)
            && $args['internal'] !== null
            && $args['internal'] !== ''
        ) {
            $internal = filter_var(
                $args['internal'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($internal === true) {
                $where .= ' AND p.wp_user_id IS NOT NULL';
            } elseif ($internal === false) {
                $where .= ' AND p.wp_user_id IS NULL';
            }
        }

        /*
        * Count distinct people because the optional filtering join may match
        * multiple team assignments for the same person.
        */
        $total_sql = "
            SELECT COUNT(DISTINCT p.id)
            FROM {$this->people_table} p
            {$joins}
            {$where}
        ";

        $total = !empty($params)
            ? (int) $this->db->get_var(
                $this->db->prepare($total_sql, ...$params)
            )
            : (int) $this->db->get_var($total_sql);

        /*
        * Retrieve only the paginated people here.
        */
        $people_sql = "
            SELECT DISTINCT
                p.id,
                p.wp_user_id,
                p.client_id,
                p.first_name,
                p.last_name,
                p.email,
                p.role,
                p.avatar_url,
                p.is_active
            FROM {$this->people_table} p
            {$joins}
            {$where}
            ORDER BY p.{$sort} {$order}, p.id ASC
            LIMIT %d OFFSET %d
        ";

        $people_params = array_merge(
            $params,
            [$per_page, $offset]
        );

        $rows = $this->db->get_results(
            $this->db->prepare($people_sql, ...$people_params),
            ARRAY_A
        );

        $people = array_map(
            [$this, 'cast_person_row'],
            $rows ?: []
        );

        /*
        * Create a team collection for every returned person.
        *
        * This also ensures that people without teams return `"teams": []`.
        */
        $teams_by_person = [];

        foreach ($people as $person) {
            $teams_by_person[(int) $person['id']] = [];
        }

        $person_ids = array_keys($teams_by_person);

        if (!empty($person_ids)) {
            $person_placeholders = implode(
                ',',
                array_fill(0, count($person_ids), '%d')
            );

            $teams_sql = "
                SELECT
                    pt.person_id,
                    t.id AS team_id,
                    t.name,
                    t.color
                FROM {$this->people_teams_table} pt
                INNER JOIN {$this->teams_table} t
                    ON t.id = pt.team_id
                WHERE pt.person_id IN ({$person_placeholders})
                ORDER BY
                    pt.person_id ASC,
                    t.name ASC,
                    t.id ASC
            ";

            $team_rows = $this->db->get_results(
                $this->db->prepare($teams_sql, ...$person_ids),
                ARRAY_A
            );

            foreach ($team_rows ?: [] as $team_row) {
                $person_id = (int) $team_row['person_id'];

                if (!isset($teams_by_person[$person_id])) {
                    continue;
                }

                $teams_by_person[$person_id][] = [
                    'team_id' => (int) $team_row['team_id'],
                    'name'    => (string) $team_row['name'],
                    'color'   => $team_row['color'] !== null
                        ? (string) $team_row['color']
                        : null,
                ];
            }
        }

        /*
        * Attach teams to each person.
        */
        return [
            'success' => true,
            'data' => array_map(function ($row) use ($teams_by_person) {
                $person = $this->cast_person_row($row);
                $person_id = (int) $person['id'];
                $meta = $this->get_person_meta_assoc($person_id);

                $person['meta'] = [
                    ['meta_key' => 'phone', 'meta_value' => $meta['phone'] ?? ''],
                    ['meta_key' => 'notes', 'meta_value' => $meta['notes'] ?? ''],
                ];
                $person['teams'] = $teams_by_person[$person_id] ?? [];

                return $person;
            }, $rows ?: []),
            'pagination' => [
                'page'        => $page,
                'per_page'    => $per_page,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $per_page),
            ],
            'filters' => [
                'q'          => $args['q'] ?? '',
                'roles'      => $roles,
                'team_ids'   => $team_ids,
                'client_ids' => $client_ids,
                'internal'   => $internal,
            ],
            'sorting' => [
                'sort'  => $sort,
                'order' => strtolower($order),
            ],
        ];
    }

    public function get_person(int $id): array|WP_Error {
        $person = $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->people_table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$person) {
            return new WP_Error('sweetdesk_person_not_found', 'Person not found.', ['status' => 404]);
        }

        $person = $this->cast_person_row($person);
        $person['created_at'] = $person['created_at'] ?? null;
        $person['updated_at'] = $person['updated_at'] ?? null;
        $person['meta'] = $this->get_person_meta_rows($id);
        $person['teams'] = $this->get_person_teams($id);

        return [
            'success' => true,
            'data' => $person,
        ];
    }

    public function create_person(array $data): array|WP_Error {
        $person_data = $this->sanitize_person_data($data);

        if (is_wp_error($person_data)) {
            return $person_data;
        }

        $inserted = $this->db->insert($this->people_table, $person_data);

        if (!$inserted) {
            return new WP_Error('sweetdesk_person_create_failed', 'Person could not be created.', ['status' => 500]);
        }

        $person_id = (int) $this->db->insert_id;

        if (!empty($data['meta']) && is_array($data['meta'])) {
            $this->upsert_person_meta($person_id, $data['meta']);
        }

        if (isset($data['team_ids']) && is_array($data['team_ids'])) {
            $this->replace_person_teams($person_id, $data['team_ids']);
        }

        return [
            'success' => true,
            'message' => 'Person created successfully.',
            'data' => $this->get_person_payload($person_id),
        ];
    }

    public function update_person(int $id, array $data): array|WP_Error {
        if (!$this->person_exists($id)) {
            return new WP_Error('sweetdesk_person_not_found', 'Person not found.', ['status' => 404]);
        }

        $person_data = $this->sanitize_person_data($data, false);

        if (is_wp_error($person_data)) {
            return $person_data;
        }

        if (!empty($person_data)) {
            $this->db->update($this->people_table, $person_data, ['id' => $id]);
        }

        if (isset($data['meta']) && is_array($data['meta'])) {
            $this->upsert_person_meta($id, $data['meta']);
        }

        if (isset($data['team_ids']) && is_array($data['team_ids'])) {
            $this->replace_person_teams($id, $data['team_ids']);
        }

        return [
            'success' => true,
            'message' => 'Person updated successfully.',
            'data' => $this->get_person_payload($id),
        ];
    }

    public function delete_person(int $id): array|WP_Error {
        if (!$this->person_exists($id)) {
            return new WP_Error('sweetdesk_person_not_found', 'Person not found.', ['status' => 404]);
        }

        $this->db->delete($this->people_teams_table, ['person_id' => $id]);
        $this->db->delete($this->people_meta_table, ['person_id' => $id]);

        $deleted = $this->db->delete($this->people_table, ['id' => $id]);

        if (!$deleted) {
            return new WP_Error('sweetdesk_person_delete_failed', 'Person could not be deleted.', ['status' => 500]);
        }

        return [
            'success' => true,
            'message' => 'Person deleted successfully.',
            'data' => [
                'id' => $id,
            ],
        ];
    }

    public function export_people(array $args): WP_REST_Response {
        $people = $this->get_people(array_merge($args, [
            'page' => 1,
            'per_page' => 100,
            'sort' => 'last_name',
            'order' => 'asc',
        ]));

        $rows = [];
        $meta_keys = [];

        foreach ($people['data'] as $person) {
            $person_id = (int) $person['id'];
            $meta = $this->get_person_meta_assoc($person_id);

            foreach (array_keys($meta) as $key) {
                $meta_keys[$key] = true;
            }

            $teams = $this->get_person_teams($person_id);

            $rows[] = [
                'person' => $person,
                'meta' => $meta,
                'team_ids' => implode(',', array_column($teams, 'team_id')),
                'team_names' => implode(',', array_column($teams, 'name')),
            ];
        }

        $meta_columns = array_keys($meta_keys);

        $headers = array_merge([
            'id',
            'wp_user_id',
            'client_id',
            'first_name',
            'last_name',
            'email',
            'role',
            'avatar_url',
            'is_active',
            'team_ids',
            'team_names',
            'created_at',
            'updated_at',
        ], $meta_columns);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            $person = $row['person'];

            $csv_row = [
                $person['id'] ?? '',
                $person['wp_user_id'] ?? '',
                $person['client_id'] ?? '',
                $person['first_name'] ?? '',
                $person['last_name'] ?? '',
                $person['email'] ?? '',
                $person['role'] ?? '',
                $person['avatar_url'] ?? '',
                !empty($person['is_active']) ? 1 : 0,
                $row['team_ids'],
                $row['team_names'],
                $person['created_at'] ?? '',
                $person['updated_at'] ?? '',
            ];

            foreach ($meta_columns as $key) {
                $value = $row['meta'][$key] ?? '';
                $csv_row[] = is_scalar($value) ? $value : wp_json_encode($value);
            }

            fputcsv($handle, $csv_row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return new WP_REST_Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=' . get_option('blog_charset'),
            'Content-Disposition' => 'attachment; filename="sweetdesk-people-export.csv"',
        ]);
    }

    public function import_people(WP_REST_Request $request): array|WP_Error {
        $files = $request->get_file_params();

        if (empty($files['file']['tmp_name'])) {
            return new WP_Error('sweetdesk_missing_import_file', 'CSV file is required.', ['status' => 400]);
        }

        $handle = fopen($files['file']['tmp_name'], 'r');

        if (!$handle) {
            return new WP_Error('sweetdesk_import_open_failed', 'Could not open CSV file.', ['status' => 400]);
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            return new WP_Error('sweetdesk_import_empty_file', 'CSV file is empty.', ['status' => 400]);
        }

        $headers = array_map('sanitize_key', $headers);

        $core_fields = [
            'wp_user_id',
            'client_id',
            'first_name',
            'last_name',
            'email',
            'role',
            'avatar_url',
            'is_active',
            'team_ids',
        ];

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $row_number = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $row_number++;

            $record = array_combine($headers, $row);

            if (!$record) {
                $skipped++;
                $errors[] = [
                    'row' => $row_number,
                    'message' => 'Invalid CSV row.',
                ];
                continue;
            }

            if (empty($record['email']) && empty($record['first_name']) && empty($record['last_name'])) {
                $skipped++;
                $errors[] = [
                    'row' => $row_number,
                    'message' => 'Missing email and name.',
                ];
                continue;
            }

            $person_data = [];
            $meta = [];

            foreach ($record as $key => $value) {
                if (in_array($key, $core_fields, true)) {
                    $person_data[$key] = $value;
                } elseif ($value !== '') {
                    $meta[$key] = $value;
                }
            }

            $team_ids = [];

            if (!empty($person_data['team_ids'])) {
                $team_ids = $this->csv_ints($person_data['team_ids']);
                unset($person_data['team_ids']);
            }

            $person_data['meta'] = $meta;
            $person_data['team_ids'] = $team_ids;

            $existing_id = null;

            if (!empty($person_data['email'])) {
                $existing_id = $this->db->get_var(
                    $this->db->prepare(
                        "SELECT id FROM {$this->people_table} WHERE email = %s LIMIT 1",
                        sanitize_email($person_data['email'])
                    )
                );
            }

            $result = $existing_id
                ? $this->update_person((int) $existing_id, $person_data)
                : $this->create_person($person_data);

            if (is_wp_error($result)) {
                $skipped++;
                $errors[] = [
                    'row' => $row_number,
                    'message' => $result->get_error_message(),
                ];
                continue;
            }

            $existing_id ? $updated++ : $created++;
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => 'People imported successfully.',
            'data' => [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors,
            ],
        ];
    }

    private function sanitize_person_data(array $data, bool $allow_empty = true): array|WP_Error {
        $clean = [];

        if (array_key_exists('wp_user_id', $data)) {
            $clean['wp_user_id'] = $data['wp_user_id'] !== '' && $data['wp_user_id'] !== null
                ? absint($data['wp_user_id'])
                : null;
        }

        if (array_key_exists('client_id', $data)) {
            $clean['client_id'] = $data['client_id'] !== '' && $data['client_id'] !== null
                ? absint($data['client_id'])
                : null;
        }

        if (array_key_exists('first_name', $data)) {
            $clean['first_name'] = sanitize_text_field($data['first_name']);
        }

        if (array_key_exists('last_name', $data)) {
            $clean['last_name'] = sanitize_text_field($data['last_name']);
        }

        if (array_key_exists('email', $data)) {
            $clean['email'] = $data['email'] ? sanitize_email($data['email']) : null;
        }

        if (array_key_exists('role', $data)) {
            $clean['role'] = $data['role'] ? sanitize_key($data['role']) : null;
        }

        if (array_key_exists('avatar_url', $data)) {
            $clean['avatar_url'] = $data['avatar_url'] ? esc_url_raw($data['avatar_url']) : null;
        }

        if (array_key_exists('is_active', $data)) {
            $active = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $clean['is_active'] = $active ? 1 : 0;
        }

        if (!$allow_empty && empty($clean)) {
            return [];
        }

        return $clean;
    }

    private function upsert_person_meta(int $person_id, array $meta): void {
        foreach ($meta as $key => $value) {
            $meta_key = sanitize_key($key);

            if ($meta_key === '') {
                continue;
            }

            $this->db->query(
                $this->db->prepare(
                    "
                    INSERT INTO {$this->people_meta_table}
                        (person_id, meta_key, meta_value)
                    VALUES
                        (%d, %s, %s)
                    ON DUPLICATE KEY UPDATE
                        meta_value = VALUES(meta_value)
                    ",
                    $person_id,
                    $meta_key,
                    maybe_serialize($value)
                )
            );
        }
    }

    private function replace_person_teams(int $person_id, array $team_ids): void {
        $team_ids = array_values(array_unique(array_filter(array_map('absint', $team_ids))));

        $this->db->delete($this->people_teams_table, ['person_id' => $person_id]);

        foreach ($team_ids as $team_id) {
            $this->db->insert($this->people_teams_table, [
                'person_id' => $person_id,
                'team_id' => $team_id,
            ]);
        }
    }

    private function get_person_payload(int $id): array {
        $person = $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->people_table} WHERE id = %d", $id),
            ARRAY_A
        );

        $person = $this->cast_person_row($person);
        $person['meta'] = $this->get_person_meta_assoc($id);
        $person['team_ids'] = array_map(
            'intval',
            array_column($this->get_person_teams($id), 'team_id')
        );

        return $person;
    }

    private function get_person_meta_rows(int $person_id): array {
        $rows = $this->db->get_results(
            $this->db->prepare(
                "
                SELECT meta_id, meta_key, meta_value
                FROM {$this->people_meta_table}
                WHERE person_id = %d
                ORDER BY meta_key ASC
                ",
                $person_id
            ),
            ARRAY_A
        );

        return array_map(function ($row) {
            $row['meta_id'] = (int) $row['meta_id'];
            $row['meta_value'] = maybe_unserialize($row['meta_value']);
            return $row;
        }, $rows);
    }

    private function get_person_meta_assoc(int $person_id): array {
        $rows = $this->get_person_meta_rows($person_id);
        $meta = [];

        foreach ($rows as $row) {
            $meta[$row['meta_key']] = $row['meta_value'];
        }

        return $meta;
    }

    private function get_person_teams(int $person_id): array {
        $rows = $this->db->get_results(
            $this->db->prepare(
                "
                SELECT
                    pt.person_id,
                    pt.team_id,
                    pt.assigned_at,
                    t.name,
                    t.description,
                    t.color
                FROM {$this->people_teams_table} pt
                INNER JOIN {$this->teams_table} t ON t.id = pt.team_id
                WHERE pt.person_id = %d
                ORDER BY t.name ASC
                ",
                $person_id
            ),
            ARRAY_A
        );

        return array_map(function ($row) {
            $row['person_id'] = (int) $row['person_id'];
            $row['team_id'] = (int) $row['team_id'];
            return $row;
        }, $rows);
    }

    private function person_exists(int $id): bool {
        return (bool) $this->db->get_var(
            $this->db->prepare("SELECT id FROM {$this->people_table} WHERE id = %d", $id)
        );
    }

    private function cast_person_row(array $row): array {
        $row['id'] = isset($row['id']) ? (int) $row['id'] : null;
        $row['wp_user_id'] = !empty($row['wp_user_id']) ? (int) $row['wp_user_id'] : null;
        $row['client_id'] = !empty($row['client_id']) ? (int) $row['client_id'] : null;
        $row['is_active'] = !empty($row['is_active']);

        return $row;
    }

    private function csv_ints(string $value): array {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('absint', explode(',', $value))));
    }

    private function csv_strings(string $value): array {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'sanitize_key',
            array_map('trim', explode(',', $value))
        )));
    }

    public function get_people_lookup(array $filters = []): array
    {
        global $wpdb;

        $people_table = $wpdb->prefix . 'sweetdesk_people';

        $where = [];
        $values = [];

        /*
        * Only return active people by default.
        *
        * The current People API describes is_active as a legacy field, but
        * excluding inactive records is appropriate for an assignment lookup.
        */
        $where[] = 'is_active = 1';

        /*
        * Search first name, last name, or the concatenated display name.
        */
        $search = isset($filters['q'])
            ? sanitize_text_field($filters['q'])
            : '';

        if ($search !== '') {
            $search_term = '%' . $wpdb->esc_like($search) . '%';

            $where[] = '
                (
                    first_name LIKE %s
                    OR last_name LIKE %s
                    OR CONCAT_WS(" ", first_name, last_name) LIKE %s
                )
            ';

            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        /*
        * Internal people have a linked WordPress user ID.
        */
        if (
            array_key_exists('internal', $filters) &&
            $filters['internal'] !== null
        ) {
            if ($filters['internal']) {
                $where[] = 'wp_user_id IS NOT NULL';
            } else {
                $where[] = 'wp_user_id IS NULL';
            }
        }

        /*
        * Optional role filtering.
        *
        * Expected format:
        * roles=staff,manager
        */
        $roles = $this->parse_lookup_strings(
            $filters['roles'] ?? null
        );

        if (!empty($roles)) {
            $role_placeholders = implode(
                ', ',
                array_fill(0, count($roles), '%s')
            );

            $where[] = "role IN ({$role_placeholders})";

            foreach ($roles as $role) {
                $values[] = $role;
            }
        }

        /*
        * Optional client filtering.
        *
        * Expected format:
        * client_ids=1,4,8
        */
        $client_ids = $this->parse_lookup_ids(
            $filters['client_ids'] ?? null
        );

        if (!empty($client_ids)) {
            $client_placeholders = implode(
                ', ',
                array_fill(0, count($client_ids), '%d')
            );

            $where[] = "client_id IN ({$client_placeholders})";

            foreach ($client_ids as $client_id) {
                $values[] = $client_id;
            }
        }

        $limit = isset($filters['limit'])
            ? absint($filters['limit'])
            : 100;

        $limit = max(1, min($limit, 500));

        $sql = "
            SELECT
                id,
                first_name,
                last_name,
                TRIM(
                    CONCAT_WS(
                        ' ',
                        NULLIF(first_name, ''),
                        NULLIF(last_name, '')
                    )
                ) AS display_name
            FROM {$people_table}
        ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= '
            ORDER BY
                last_name ASC,
                first_name ASC,
                id ASC
            LIMIT %d
        ';

        $values[] = $limit;

        $prepared_sql = $wpdb->prepare($sql, $values);

        $results = $wpdb->get_results(
            $prepared_sql,
            ARRAY_A
        );

        if ($wpdb->last_error) {
            throw new RuntimeException(
                'Database error while retrieving people lookup.'
            );
        }

        return array_map(
            static function (array $person): array {
                return [
                    'id' => (int) $person['id'],
                    'first_name' => $person['first_name'] ?? '',
                    'last_name' => $person['last_name'] ?? '',
                    'display_name' => $person['display_name'] ?? '',
                ];
            },
            $results ?: []
        );
    }

    private function parse_lookup_ids($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value)
            ? $value
            : explode(',', (string) $value);

        $ids = array_map(
            'absint',
            $items
        );

        $ids = array_filter(
            $ids,
            static fn(int $id): bool => $id > 0
        );

        return array_values(
            array_unique($ids)
        );
    }

    private function parse_lookup_strings($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value)
            ? $value
            : explode(',', (string) $value);

        $items = array_map(
            static function ($item): string {
                return sanitize_key(
                    trim((string) $item)
                );
            },
            $items
        );

        $items = array_filter(
            $items,
            static fn(string $item): bool => $item !== ''
        );

        return array_values(
            array_unique($items)
        );
    }
}