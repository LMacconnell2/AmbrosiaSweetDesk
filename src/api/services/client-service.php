<?php

if (!defined('ABSPATH')) exit;

class SweetDesk_Client_Service {

    private wpdb $db;
    private string $clients_table;
    private string $client_meta_table;
    private string $tickets_table;
    private string $people_table;

    public function __construct() {
        global $wpdb;

        $this->db = $wpdb;
        $this->clients_table = $wpdb->prefix . 'sweetdesk_clients';
        $this->client_meta_table = $wpdb->prefix . 'sweetdesk_client_meta';
        $this->tickets_table = $wpdb->prefix . 'sweetdesk_tickets';
        $this->people_table = $wpdb->prefix . 'sweetdesk_people';
    }

    public function get_clients(array $args): array {
        $page = max(1, (int) $args['page']);
        $per_page = min(100, max(1, (int) $args['per_page']));
        $offset = ($page - 1) * $per_page;

        $allowed_sort = ['id', 'name', 'email', 'created_at', 'updated_at'];
        $sort = in_array($args['sort'], $allowed_sort, true) ? $args['sort'] : 'name';
        $order = strtolower($args['order']) === 'desc' ? 'DESC' : 'ASC';

        $where = 'WHERE 1=1';
        $params = [];

        if (!empty($args['q'])) {
            $like = '%' . $this->db->esc_like($args['q']) . '%';
            $where .= ' AND (c.name LIKE %s OR c.email LIKE %s OR c.phone LIKE %s OR c.website LIKE %s)';
            array_push($params, $like, $like, $like, $like);
        }

        $total_sql = "SELECT COUNT(*) FROM {$this->clients_table} c {$where}";
        $total = !empty($params)
            ? (int) $this->db->get_var($this->db->prepare($total_sql, ...$params))
            : (int) $this->db->get_var($total_sql);

        $sql = "
            SELECT
                c.id,
                c.name,
                c.email,
                c.phone,
                c.website,
                COUNT(t.id) AS total_tickets,
                SUM(CASE WHEN t.status != 'closed' THEN 1 ELSE 0 END) AS open_tickets,
                SUM(CASE WHEN t.status = 'closed' THEN 1 ELSE 0 END) AS closed_tickets
            FROM {$this->clients_table} c
            LEFT JOIN {$this->tickets_table} t ON t.client_id = c.id
            {$where}
            GROUP BY c.id
            ORDER BY c.{$sort} {$order}
            LIMIT %d OFFSET %d
        ";

        $prepared = $this->db->prepare($sql, ...array_merge($params, [$per_page, $offset]));
        $rows = $this->db->get_results($prepared, ARRAY_A);

        return [
            'success' => true,
            'data' => array_map(function ($row) {
                $client = $this->cast_client_list_row($row);
                $client_id = (int) $client['id'];
                $client['meta'] = $this->get_client_meta_assoc($client_id);
                $client['people'] = $this->get_client_people($client_id);

                return $client;
            }, $rows),
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => (int) ceil($total / $per_page),
            ],
        ];
    }

    public function get_client(int $id): array|WP_Error {
        $client = $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->clients_table} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$client) {
            return new WP_Error('sweetdesk_client_not_found', 'Client not found.', ['status' => 404]);
        }

        $client['id'] = (int) $client['id'];
        $client['meta'] = $this->get_client_meta_rows($id);
        $client['recent_tickets'] = $this->get_recent_tickets($id);
        $client['people'] = $this->get_client_people($id);

        return [
            'success' => true,
            'data' => $client,
        ];
    }

    public function create_client(array $data): array|WP_Error {
        $client_data = $this->sanitize_client_data($data, true);

        if (is_wp_error($client_data)) {
            return $client_data;
        }

        $inserted = $this->db->insert($this->clients_table, $client_data);

        if (!$inserted) {
            return new WP_Error('sweetdesk_client_create_failed', 'Client could not be created.', ['status' => 500]);
        }

        $client_id = (int) $this->db->insert_id;

        if (!empty($data['meta']) && is_array($data['meta'])) {
            $this->upsert_client_meta($client_id, $data['meta']);
        }

        return [
            'success' => true,
            'message' => 'Client created successfully.',
            'data' => $this->get_client_payload($client_id),
        ];
    }

    public function update_client(int $id, array $data): array|WP_Error {
        if (!$this->client_exists($id)) {
            return new WP_Error('sweetdesk_client_not_found', 'Client not found.', ['status' => 404]);
        }

        $client_data = $this->sanitize_client_data($data, false);

        if (is_wp_error($client_data)) {
            return $client_data;
        }

        if (!empty($client_data)) {
            $this->db->update(
                $this->clients_table,
                $client_data,
                ['id' => $id]
            );
        }

        if (isset($data['meta']) && is_array($data['meta'])) {
            $this->upsert_client_meta($id, $data['meta']);
        }

        return [
            'success' => true,
            'message' => 'Client updated successfully.',
            'data' => $this->get_client_payload($id),
        ];
    }

    public function delete_client(int $id): array|WP_Error {
        if (!$this->client_exists($id)) {
            return new WP_Error('sweetdesk_client_not_found', 'Client not found.', ['status' => 404]);
        }

        $this->db->delete($this->client_meta_table, ['client_id' => $id]);

        $people_unlinked = $this->db->update(
            $this->people_table,
            ['client_id' => null],
            ['client_id' => $id]
        );

        $tickets_unlinked = $this->db->update(
            $this->tickets_table,
            ['client_id' => null],
            ['client_id' => $id]
        );

        $deleted = $this->db->delete($this->clients_table, ['id' => $id]);

        if (!$deleted) {
            return new WP_Error('sweetdesk_client_delete_failed', 'Client could not be deleted.', ['status' => 500]);
        }

        return [
            'success' => true,
            'message' => 'Client deleted successfully.',
            'data' => [
                'id' => $id,
                'people_unlinked' => (int) $people_unlinked,
                'tickets_unlinked' => (int) $tickets_unlinked,
            ],
        ];
    }

    public function export_clients(array $args): array {
        $clients_response = $this->get_clients([
            'q' => $args['q'] ?? '',
            'page' => 1,
            'per_page' => 100,
            'sort' => 'name',
            'order' => 'asc',
        ]);

        $data = [];

        foreach ($clients_response['data'] as $client_row) {
            $client_id = (int) $client_row['id'];

            $client = $this->get_client_payload($client_id);
            $client['meta'] = $this->get_client_meta_assoc($client_id);

            if (empty($args['include_people'])) {
                unset($client['people']);
            }

            if (empty($args['include_recent_tickets'])) {
                unset($client['recent_tickets']);
            }

            $data[] = $client;
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    public function import_clients(array $data): array|WP_Error {
        if (empty($data['clients']) || !is_array($data['clients'])) {
            return new WP_Error('sweetdesk_invalid_import', 'Import requires a clients array.', ['status' => 400]);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($data['clients'] as $index => $client) {
            if (empty($client['name'])) {
                $skipped++;
                $errors[] = [
                    'index' => $index,
                    'message' => 'Client name is required.',
                ];
                continue;
            }

            $existing_id = null;

            if (!empty($client['email'])) {
                $existing_id = $this->db->get_var(
                    $this->db->prepare(
                        "SELECT id FROM {$this->clients_table} WHERE email = %s LIMIT 1",
                        sanitize_email($client['email'])
                    )
                );
            }

            if ($existing_id) {
                $result = $this->update_client((int) $existing_id, $client);

                if (is_wp_error($result)) {
                    $skipped++;
                    $errors[] = [
                        'index' => $index,
                        'message' => $result->get_error_message(),
                    ];
                } else {
                    $updated++;
                }
            } else {
                $result = $this->create_client($client);

                if (is_wp_error($result)) {
                    $skipped++;
                    $errors[] = [
                        'index' => $index,
                        'message' => $result->get_error_message(),
                    ];
                } else {
                    $created++;
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Clients imported successfully.',
            'data' => [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors,
            ],
        ];
    }

    private function sanitize_client_data(array $data, bool $require_name): array|WP_Error {
        if ($require_name && empty($data['name'])) {
            return new WP_Error('sweetdesk_client_name_required', 'Client name is required.', ['status' => 400]);
        }

        $clean = [];

        if (array_key_exists('name', $data)) {
            $name = sanitize_text_field($data['name']);

            if ($require_name && $name === '') {
                return new WP_Error('sweetdesk_client_name_required', 'Client name is required.', ['status' => 400]);
            }

            $clean['name'] = $name;
        }

        if (array_key_exists('email', $data)) {
            $clean['email'] = $data['email'] ? sanitize_email($data['email']) : null;
        }

        if (array_key_exists('phone', $data)) {
            $clean['phone'] = $data['phone'] ? sanitize_text_field($data['phone']) : null;
        }

        if (array_key_exists('website', $data)) {
            $clean['website'] = $data['website'] ? esc_url_raw($data['website']) : null;
        }

        if (array_key_exists('notes', $data)) {
            $clean['notes'] = $data['notes'] ? wp_kses_post($data['notes']) : null;
        }

        return $clean;
    }

    private function upsert_client_meta(int $client_id, array $meta): void {
        foreach ($meta as $key => $value) {
            $meta_key = sanitize_key($key);

            if ($meta_key === '') {
                continue;
            }

            $this->db->query(
                $this->db->prepare(
                    "
                    INSERT INTO {$this->client_meta_table}
                        (client_id, meta_key, meta_value)
                    VALUES
                        (%d, %s, %s)
                    ON DUPLICATE KEY UPDATE
                        meta_value = VALUES(meta_value)
                    ",
                    $client_id,
                    $meta_key,
                    maybe_serialize($value)
                )
            );
        }
    }

    private function get_client_payload(int $id): array {
        $client = $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->clients_table} WHERE id = %d", $id),
            ARRAY_A
        );

        $client['id'] = (int) $client['id'];
        $client['meta'] = $this->get_client_meta_assoc($id);

        return $client;
    }

    private function get_client_meta_rows(int $client_id): array {
        $rows = $this->db->get_results(
            $this->db->prepare(
                "SELECT meta_id, client_id, meta_key, meta_value FROM {$this->client_meta_table} WHERE client_id = %d ORDER BY meta_key ASC",
                $client_id
            ),
            ARRAY_A
        );

        return array_map(function ($row) {
            $row['meta_id'] = (int) $row['meta_id'];
            $row['client_id'] = (int) $row['client_id'];
            $row['meta_value'] = maybe_unserialize($row['meta_value']);
            return $row;
        }, $rows);
    }

    private function get_client_meta_assoc(int $client_id): array {
        $rows = $this->get_client_meta_rows($client_id);
        $meta = [];

        foreach ($rows as $row) {
            $meta[$row['meta_key']] = $row['meta_value'];
        }

        return $meta;
    }

    private function get_recent_tickets(int $client_id): array {
        $rows = $this->db->get_results(
            $this->db->prepare(
                "
                SELECT id, assigned_to, created_by, title, status, priority, created_at
                FROM {$this->tickets_table}
                WHERE client_id = %d
                ORDER BY created_at DESC
                LIMIT 5
                ",
                $client_id
            ),
            ARRAY_A
        );

        return array_map(function ($row) {
            $row['id'] = (int) $row['id'];
            $row['assigned_to'] = $row['assigned_to'] !== null ? (int) $row['assigned_to'] : null;
            $row['created_by'] = $row['created_by'] !== null ? (int) $row['created_by'] : null;
            return $row;
        }, $rows);
    }

    private function get_client_people(int $client_id): array {
        $rows = $this->db->get_results(
            $this->db->prepare(
                "
                SELECT id, first_name, last_name, email, role
                FROM {$this->people_table}
                WHERE client_id = %d
                ORDER BY last_name ASC, first_name ASC
                ",
                $client_id
            ),
            ARRAY_A
        );

        return array_map(function ($row) {
            $row['id'] = (int) $row['id'];
            return $row;
        }, $rows);
    }

    private function client_exists(int $id): bool {
        return (bool) $this->db->get_var(
            $this->db->prepare("SELECT id FROM {$this->clients_table} WHERE id = %d", $id)
        );
    }

    private function cast_client_list_row(array $row): array {
        $row['id'] = (int) $row['id'];
        $row['total_tickets'] = (int) $row['total_tickets'];
        $row['open_tickets'] = (int) $row['open_tickets'];
        $row['closed_tickets'] = (int) $row['closed_tickets'];

        return $row;
    }

    public function get_clients_lookup(array $filters = []): array
    {
        global $wpdb;

        $clients_table =
            $wpdb->prefix . 'sweetdesk_clients';

        $where = [];
        $values = [];

        $search = isset($filters['q'])
            ? sanitize_text_field($filters['q'])
            : '';

        if ($search !== '') {
            $search_term =
                '%' . $wpdb->esc_like($search) . '%';

            $where[] = 'name LIKE %s';
            $values[] = $search_term;
        }

        $limit = isset($filters['limit'])
            ? absint($filters['limit'])
            : 100;

        $limit = max(
            1,
            min($limit, 500)
        );

        $sql = "
            SELECT
                id,
                name
            FROM {$clients_table}
        ";

        if (!empty($where)) {
            $sql .=
                ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= '
            ORDER BY
                name ASC,
                id ASC
            LIMIT %d
        ';

        $values[] = $limit;

        $prepared_sql = $wpdb->prepare(
            $sql,
            $values
        );

        $results = $wpdb->get_results(
            $prepared_sql,
            ARRAY_A
        );

        if ($wpdb->last_error) {
            throw new RuntimeException(
                'Database error while retrieving the client lookup.'
            );
        }

        return array_map(
            static function (array $client): array {
                return [
                    'id' => (int) $client['id'],
                    'name' => $client['name'] ?? '',
                ];
            },
            $results ?: []
        );
    }
}