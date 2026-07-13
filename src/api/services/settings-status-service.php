<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Settings_Status_Service
{
    private string $statuses_table;
    private string $tickets_table;

    public function __construct()
    {
        global $wpdb;

        $this->statuses_table = $wpdb->prefix . 'sweetdesk_statuses';
        $this->tickets_table = $wpdb->prefix . 'sweetdesk_tickets';
    }

    /**
     * Retrieve ticket statuses.
     *
     * By default, only active statuses are returned so this endpoint
     * can also be used as a ticket create/edit lookup route.
     */
    public function get_statuses(
        bool $include_inactive = false
    ): array|WP_Error {
        global $wpdb;

        $sql = "
            SELECT
                id,
                name,
                slug,
                is_active,
                sort_order,
                created_at,
                updated_at
            FROM {$this->statuses_table}
        ";

        if (!$include_inactive) {
            $sql .= ' WHERE is_active = 1';
        }

        $sql .= ' ORDER BY sort_order ASC, name ASC';

        $rows = $wpdb->get_results($sql, ARRAY_A);

        if ($wpdb->last_error !== '') {
            return $this->database_error(
                'Unable to retrieve ticket statuses.'
            );
        }

        return array_map(
            [$this, 'format_status'],
            $rows ?: []
        );
    }

    public function create_status(array $data): array|WP_Error
    {
        global $wpdb;

        $name = sanitize_text_field($data['name'] ?? '');
        $slug = sanitize_title($data['slug'] ?? $name);

        if ($name === '') {
            return new WP_Error(
                'sweetdesk_status_name_required',
                'A ticket status name is required.',
                ['status' => 400]
            );
        }

        if ($slug === '') {
            return new WP_Error(
                'sweetdesk_status_slug_required',
                'A valid ticket status slug is required.',
                ['status' => 400]
            );
        }

        if ($this->slug_exists($slug)) {
            return new WP_Error(
                'sweetdesk_duplicate_status_slug',
                'A ticket status with this slug already exists.',
                ['status' => 409]
            );
        }

        $is_active = array_key_exists('is_active', $data)
            ? (int) rest_sanitize_boolean($data['is_active'])
            : 1;

        $sort_order = absint($data['sort_order'] ?? 0);
        $now = current_time('mysql', true);

        $inserted = $wpdb->insert(
            $this->statuses_table,
            [
                'name' => $name,
                'slug' => $slug,
                'is_active' => $is_active,
                'sort_order' => $sort_order,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            return $this->database_error(
                'Unable to create the ticket status.'
            );
        }

        $status = $this->get_status_by_id(
            (int) $wpdb->insert_id
        );

        if (!$status) {
            return new WP_Error(
                'sweetdesk_status_creation_failed',
                'The ticket status was created but could not be retrieved.',
                ['status' => 500]
            );
        }

        return $status;
    }

    public function update_status(
        int $id,
        array $data
    ): array|WP_Error {
        global $wpdb;

        if ($id <= 0) {
            return new WP_Error(
                'sweetdesk_invalid_status_id',
                'A valid ticket status ID is required.',
                ['status' => 400]
            );
        }

        $existing = $this->get_status_by_id($id);

        if (!$existing) {
            return new WP_Error(
                'sweetdesk_status_not_found',
                'The requested ticket status was not found.',
                ['status' => 404]
            );
        }

        if ($data === []) {
            return new WP_Error(
                'sweetdesk_no_status_changes',
                'No status properties were provided for update.',
                ['status' => 400]
            );
        }

        $updates = [];
        $formats = [];

        if (array_key_exists('name', $data)) {
            $name = sanitize_text_field($data['name']);

            if ($name === '') {
                return new WP_Error(
                    'sweetdesk_status_name_required',
                    'The ticket status name cannot be empty.',
                    ['status' => 400]
                );
            }

            $updates['name'] = $name;
            $formats[] = '%s';
        }

        if (array_key_exists('slug', $data)) {
            $slug = sanitize_title($data['slug']);

            if ($slug === '') {
                return new WP_Error(
                    'sweetdesk_status_slug_required',
                    'The ticket status slug cannot be empty.',
                    ['status' => 400]
                );
            }

            if ($slug !== $existing['slug']) {
                if ($this->slug_exists($slug, $id)) {
                    return new WP_Error(
                        'sweetdesk_duplicate_status_slug',
                        'A ticket status with this slug already exists.',
                        ['status' => 409]
                    );
                }

                /*
                 * Tickets currently store status as a VARCHAR.
                 * Changing a slug that is already used could leave
                 * existing tickets referencing an invalid value.
                 */
                if ($this->status_is_used_by_tickets($existing)) {
                    return new WP_Error(
                        'sweetdesk_status_slug_in_use',
                        'The slug cannot be changed because existing tickets use this status.',
                        ['status' => 409]
                    );
                }

                $updates['slug'] = $slug;
                $formats[] = '%s';
            }
        }

        if (array_key_exists('is_active', $data)) {
            $updates['is_active'] = (int) rest_sanitize_boolean(
                $data['is_active']
            );
            $formats[] = '%d';
        }

        if (array_key_exists('sort_order', $data)) {
            $updates['sort_order'] = absint(
                $data['sort_order']
            );
            $formats[] = '%d';
        }

        if ($updates === []) {
            return $existing;
        }

        $updates['updated_at'] = current_time('mysql', true);
        $formats[] = '%s';

        $updated = $wpdb->update(
            $this->statuses_table,
            $updates,
            ['id' => $id],
            $formats,
            ['%d']
        );

        if ($updated === false) {
            return $this->database_error(
                'Unable to update the ticket status.'
            );
        }

        $status = $this->get_status_by_id($id);

        if (!$status) {
            return new WP_Error(
                'sweetdesk_status_update_failed',
                'The ticket status was updated but could not be retrieved.',
                ['status' => 500]
            );
        }

        return $status;
    }

    /**
     * Permanently delete a status.
     *
     * Normal deletion should instead use:
     *
     * PUT /settings/tickets/status/:id
     * {
     *     "is_active": false
     * }
     */
    public function delete_status(int $id): true|WP_Error
    {
        global $wpdb;

        if ($id <= 0) {
            return new WP_Error(
                'sweetdesk_invalid_status_id',
                'A valid ticket status ID is required.',
                ['status' => 400]
            );
        }

        $status = $this->get_status_by_id($id);

        if (!$status) {
            return new WP_Error(
                'sweetdesk_status_not_found',
                'The requested ticket status was not found.',
                ['status' => 404]
            );
        }

        if ($this->status_is_used_by_tickets($status)) {
            return new WP_Error(
                'sweetdesk_status_in_use',
                'This status cannot be permanently deleted because existing tickets use it. Deactivate it instead.',
                ['status' => 409]
            );
        }

        $deleted = $wpdb->delete(
            $this->statuses_table,
            ['id' => $id],
            ['%d']
        );

        if ($deleted === false) {
            return $this->database_error(
                'Unable to permanently delete the ticket status.'
            );
        }

        if ($deleted === 0) {
            return new WP_Error(
                'sweetdesk_status_not_found',
                'The requested ticket status was not found.',
                ['status' => 404]
            );
        }

        return true;
    }

    private function get_status_by_id(int $id): ?array
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT
                    id,
                    name,
                    slug,
                    is_active,
                    sort_order,
                    created_at,
                    updated_at
                FROM {$this->statuses_table}
                WHERE id = %d
                LIMIT 1
                ",
                $id
            ),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return $this->format_status($row);
    }

    private function slug_exists(
        string $slug,
        ?int $exclude_id = null
    ): bool {
        global $wpdb;

        if ($exclude_id !== null) {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT COUNT(*)
                    FROM {$this->statuses_table}
                    WHERE slug = %s
                      AND id != %d
                    ",
                    $slug,
                    $exclude_id
                )
            );
        } else {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT COUNT(*)
                    FROM {$this->statuses_table}
                    WHERE slug = %s
                    ",
                    $slug
                )
            );
        }

        return (int) $count > 0;
    }

    /**
     * Check both slug and name because older SweetDesk tickets may have
     * stored display names such as "Open", while newer tickets should
     * preferably store stable slugs such as "open".
     */
    private function status_is_used_by_tickets(
        array $status
    ): bool {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT COUNT(*)
                FROM {$this->tickets_table}
                WHERE status = %s
                   OR status = %s
                ",
                $status['slug'],
                $status['name']
            )
        );

        return (int) $count > 0;
    }

    private function format_status(array $status): array
    {
        return [
            'id' => (int) $status['id'],
            'name' => (string) $status['name'],
            'slug' => (string) $status['slug'],
            'is_active' => (bool) $status['is_active'],
            'sort_order' => (int) $status['sort_order'],
            'created_at' => (string) $status['created_at'],
            'updated_at' => (string) $status['updated_at'],
        ];
    }

    private function database_error(string $message): WP_Error
    {
        global $wpdb;

        if (
            defined('WP_DEBUG') &&
            WP_DEBUG &&
            $wpdb->last_error !== ''
        ) {
            error_log(
                'SweetDesk status database error: ' .
                $wpdb->last_error
            );
        }

        return new WP_Error(
            'sweetdesk_database_error',
            $message,
            ['status' => 500]
        );
    }
}