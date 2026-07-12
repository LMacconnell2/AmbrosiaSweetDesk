<?php
if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Team_Service
{
    private wpdb $wpdb;
    private string $teams_table;
    private string $team_meta_table;
    private string $people_teams_table;
    private string $people_table;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->teams_table = $wpdb->prefix . 'sweetdesk_teams';
        $this->team_meta_table = $wpdb->prefix . 'sweetdesk_team_meta';
        $this->people_teams_table = $wpdb->prefix . 'sweetdesk_people_teams';
        $this->people_table = $wpdb->prefix . 'sweetdesk_people';
    }

    public function get_teams(array $params): array
    {
        $page = $params['page'];
        $per_page = $params['per_page'];
        $offset = ($page - 1) * $per_page;

        $where = ['1=1'];
        $values = [];

        if (!empty($params['q'])) {
            $like = '%' . $this->wpdb->esc_like($params['q']) . '%';
            $where[] = '(t.name LIKE %s OR t.description LIKE %s)';
            $values[] = $like;
            $values[] = $like;
        }

        if (!empty($params['team_member_id'])) {
            $ids = array_filter(array_map('absint', explode(',', $params['team_member_id'])));

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
                $where[] = "t.id IN (
                    SELECT team_id 
                    FROM {$this->people_teams_table} 
                    WHERE person_id IN ($placeholders)
                )";

                foreach ($ids as $id) {
                    $values[] = $id;
                }
            }
        }

        $where_sql = implode(' AND ', $where);

        $count_sql = "SELECT COUNT(*) FROM {$this->teams_table} t WHERE {$where_sql}";
        $total = (int) $this->wpdb->get_var($this->wpdb->prepare($count_sql, $values));

        $query_values = array_merge($values, [$per_page, $offset]);

        $teams_sql = "
            SELECT 
                t.id,
                t.name,
                t.description,
                t.color,
                t.created_at,
                t.updated_at
            FROM {$this->teams_table} t
            WHERE {$where_sql}
            ORDER BY t.created_at DESC
            LIMIT %d OFFSET %d
        ";

        $teams = $this->wpdb->get_results(
            $this->wpdb->prepare($teams_sql, $query_values),
            ARRAY_A
        );

        foreach ($teams as &$team) {
            $team['id'] = (int) $team['id'];
            $team['meta'] = $this->get_team_meta($team['id']);
            $team['members'] = $this->get_team_members($team['id']);
        }

        return [
            'data' => $teams,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => (int) ceil($total / $per_page),
            ],
        ];
    }

    public function create_team(array $data)
    {
        $name = sanitize_text_field($data['name'] ?? '');

        if ($name === '') {
            return new WP_Error(
                'sweetdesk_team_name_required',
                'Team name is required.',
                ['status' => 400]
            );
        }

        $description = sanitize_textarea_field($data['description'] ?? '');
        $color = sanitize_hex_color($data['color'] ?? '') ?: null;

        $inserted = $this->wpdb->insert(
            $this->teams_table,
            [
                'name' => $name,
                'description' => $description,
                'color' => $color,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );

        if (!$inserted) {
            return new WP_Error(
                'sweetdesk_team_create_failed',
                'Failed to create team.',
                ['status' => 500]
            );
        }

        $team_id = (int) $this->wpdb->insert_id;

        $this->replace_team_meta($team_id, $data['meta'] ?? []);
        $this->replace_team_people($team_id, $data['people'] ?? []);

        return $team_id;
    }

    public function get_team(int $id): ?array
    {
        $team = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "
                SELECT 
                    id,
                    name,
                    description,
                    color,
                    created_at,
                    updated_at
                FROM {$this->teams_table}
                WHERE id = %d
                ",
                $id
            ),
            ARRAY_A
        );

        if (!$team) {
            return null;
        }

        $team['id'] = (int) $team['id'];
        $team['meta'] = $this->get_team_meta($id);
        $team['members'] = $this->get_team_members($id);

        return $team;
    }

    public function update_team(int $id, array $data)
    {
        if (!$this->team_exists($id)) {
            return new WP_Error(
                'sweetdesk_team_not_found',
                'Team not found.',
                ['status' => 404]
            );
        }

        $update_data = [
            'updated_at' => current_time('mysql'),
        ];

        $formats = ['%s'];

        if (isset($data['name'])) {
            $name = sanitize_text_field($data['name']);

            if ($name === '') {
                return new WP_Error(
                    'sweetdesk_team_name_required',
                    'Team name is required.',
                    ['status' => 400]
                );
            }

            $update_data['name'] = $name;
            $formats[] = '%s';
        }

        if (array_key_exists('description', $data)) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
            $formats[] = '%s';
        }

        if (array_key_exists('color', $data)) {
            $update_data['color'] = sanitize_hex_color($data['color'] ?? '') ?: null;
            $formats[] = '%s';
        }

        $updated = $this->wpdb->update(
            $this->teams_table,
            $update_data,
            ['id' => $id],
            $formats,
            ['%d']
        );

        if ($updated === false) {
            return new WP_Error(
                'sweetdesk_team_update_failed',
                'Failed to update team.',
                ['status' => 500]
            );
        }

        if (isset($data['meta']) && is_array($data['meta'])) {
            $this->replace_team_meta($id, $data['meta']);
        }

        if (isset($data['people']) && is_array($data['people'])) {
            $this->replace_team_people($id, $data['people']);
        }

        return true;
    }

    public function delete_team(int $id)
    {
        if (!$this->team_exists($id)) {
            return new WP_Error(
                'sweetdesk_team_not_found',
                'Team not found.',
                ['status' => 404]
            );
        }

        $this->wpdb->delete($this->team_meta_table, ['team_id' => $id], ['%d']);
        $this->wpdb->delete($this->people_teams_table, ['team_id' => $id], ['%d']);

        $deleted = $this->wpdb->delete($this->teams_table, ['id' => $id], ['%d']);

        if (!$deleted) {
            return new WP_Error(
                'sweetdesk_team_delete_failed',
                'Failed to delete team.',
                ['status' => 500]
            );
        }

        return true;
    }

    public function update_team_people(int $team_id, array $people)
    {
        if (!$this->team_exists($team_id)) {
            return new WP_Error(
                'sweetdesk_team_not_found',
                'Team not found.',
                ['status' => 404]
            );
        }

        return $this->replace_team_people($team_id, $people);
    }

    public function remove_team_person(int $team_id, int $person_id)
    {
        if (!$this->team_exists($team_id)) {
            return new WP_Error(
                'sweetdesk_team_not_found',
                'Team not found.',
                ['status' => 404]
            );
        }

        $deleted = $this->wpdb->delete(
            $this->people_teams_table,
            [
                'team_id' => $team_id,
                'person_id' => $person_id,
            ],
            ['%d', '%d']
        );

        if ($deleted === false) {
            return new WP_Error(
                'sweetdesk_team_person_remove_failed',
                'Failed to remove person from team.',
                ['status' => 500]
            );
        }

        return true;
    }

    public function search_people(array $params): array
    {
        if (empty($params['q'])) {
            return [];
        }

        $where = [];
        $values = [];

        $like = '%' . $this->wpdb->esc_like($params['q']) . '%';

        $where[] = '(
            first_name LIKE %s 
            OR last_name LIKE %s 
            OR email LIKE %s
            OR CONCAT(first_name, " ", last_name) LIKE %s
        )';

        $values[] = $like;
        $values[] = $like;
        $values[] = $like;
        $values[] = $like;

        if (!empty($params['role'])) {
            $roles = array_filter(array_map('sanitize_text_field', explode(',', $params['role'])));

            if (!empty($roles)) {
                $placeholders = implode(',', array_fill(0, count($roles), '%s'));
                $where[] = "role IN ($placeholders)";

                foreach ($roles as $role) {
                    $values[] = $role;
                }
            }
        }

        $where_sql = implode(' AND ', $where);

        $sql = "
            SELECT 
                id,
                first_name,
                last_name,
                email,
                role
            FROM {$this->people_table}
            WHERE {$where_sql}
            ORDER BY first_name ASC, last_name ASC
            LIMIT 20
        ";

        $people = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $values),
            ARRAY_A
        );

        return array_map(function ($person) {
            $person['id'] = (int) $person['id'];
            return $person;
        }, $people);
    }

    private function get_team_meta(int $team_id): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "
                SELECT 
                    meta_id,
                    team_id,
                    meta_key,
                    meta_value
                FROM {$this->team_meta_table}
                WHERE team_id = %d
                ORDER BY meta_id ASC
                ",
                $team_id
            ),
            ARRAY_A
        );

        return array_map(function ($row) {
            $row['meta_id'] = (int) $row['meta_id'];
            $row['team_id'] = (int) $row['team_id'];
            return $row;
        }, $rows);
    }

    private function get_team_members(int $team_id): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "
                SELECT
                    pt.person_id,
                    pt.team_id,
                    pt.assigned_at,
                    p.id,
                    p.first_name,
                    p.last_name,
                    p.email,
                    p.role
                FROM {$this->people_teams_table} pt
                INNER JOIN {$this->people_table} p
                    ON p.id = pt.person_id
                WHERE pt.team_id = %d
                ORDER BY p.first_name ASC, p.last_name ASC
                ",
                $team_id
            ),
            ARRAY_A
        );

        return array_map(function ($row) {
            return [
                'person_id' => (int) $row['person_id'],
                'team_id' => (int) $row['team_id'],
                'assigned_at' => $row['assigned_at'],
                'person' => [
                    'id' => (int) $row['id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                ],
            ];
        }, $rows);
    }

    private function replace_team_meta(int $team_id, array $meta): void
    {
        $this->wpdb->delete($this->team_meta_table, ['team_id' => $team_id], ['%d']);

        foreach ($meta as $item) {
            $meta_key = sanitize_key($item['meta_key'] ?? '');
            $meta_value = sanitize_text_field($item['meta_value'] ?? '');

            if ($meta_key === '') {
                continue;
            }

            $this->wpdb->insert(
                $this->team_meta_table,
                [
                    'team_id' => $team_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value,
                ],
                ['%d', '%s', '%s']
            );
        }
    }

    private function replace_team_people(int $team_id, array $people): array
    {
        $this->wpdb->delete($this->people_teams_table, ['team_id' => $team_id], ['%d']);

        $inserted_people = [];
        $now = current_time('mysql');
        $seen = [];

        foreach ($people as $person) {
            $person_id = absint($person['person_id'] ?? 0);

            if ($person_id <= 0 || isset($seen[$person_id])) {
                continue;
            }

            $seen[$person_id] = true;

            $this->wpdb->insert(
                $this->people_teams_table,
                [
                    'person_id' => $person_id,
                    'team_id' => $team_id,
                    'assigned_at' => $now,
                ],
                ['%d', '%d', '%s']
            );

            $inserted_people[] = [
                'person_id' => $person_id,
                'team_id' => $team_id,
                'assigned_at' => $now,
            ];
        }

        return $inserted_people;
    }

    private function team_exists(int $id): bool
    {
        return (bool) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->teams_table} WHERE id = %d",
                $id
            )
        );
    }
}