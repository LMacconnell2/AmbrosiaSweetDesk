<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Settings_Fields_Service
{
    private string $fields_table;
    private string $ticket_meta_table;

    private const FIELD_TYPES = [
        'text',
        'textarea',
        'number',
        'email',
        'url',
        'date',
        'datetime',
        'checkbox',
        'select',
    ];

    public function __construct()
    {
        global $wpdb;

        $this->fields_table =
            $wpdb->prefix . 'sweetdesk_ticket_fields';

        $this->ticket_meta_table =
            $wpdb->prefix . 'sweetdesk_ticket_meta';
    }

    public function get_fields(
        bool $include_inactive = false
    ): array|WP_Error {
        global $wpdb;

        $sql = "
            SELECT
                id,
                name,
                field_key,
                field_type,
                is_required,
                is_active,
                sort_order,
                options,
                created_at,
                updated_at
            FROM {$this->fields_table}
        ";

        if (!$include_inactive) {
            $sql .= ' WHERE is_active = 1';
        }

        $sql .= ' ORDER BY sort_order ASC, name ASC';

        $rows = $wpdb->get_results($sql, ARRAY_A);

        if ($wpdb->last_error !== '') {
            return $this->database_error(
                'Unable to retrieve ticket fields.'
            );
        }

        return array_map(
            [$this, 'format_field'],
            $rows ?: []
        );
    }

    public function create_field(array $data): array|WP_Error
    {
        global $wpdb;

        $name = sanitize_text_field($data['name'] ?? '');

        $field_key = sanitize_key(
            $data['field_key'] ??
            sanitize_title($name)
        );

        $field_type = sanitize_key(
            $data['field_type'] ?? 'text'
        );

        $options = $this->sanitize_options(
            $data['options'] ?? []
        );

        $validation = $this->validate_field(
            $name,
            $field_key,
            $field_type,
            $options
        );

        if (is_wp_error($validation)) {
            return $validation;
        }

        if ($this->field_key_exists($field_key)) {
            return new WP_Error(
                'sweetdesk_duplicate_field_key',
                'A ticket field with this key already exists.',
                ['status' => 409]
            );
        }

        $is_required = array_key_exists('is_required', $data)
            ? (int) rest_sanitize_boolean($data['is_required'])
            : 0;

        $is_active = array_key_exists('is_active', $data)
            ? (int) rest_sanitize_boolean($data['is_active'])
            : 1;

        $sort_order = absint($data['sort_order'] ?? 0);
        $now = current_time('mysql', true);

        $inserted = $wpdb->insert(
            $this->fields_table,
            [
                'name' => $name,
                'field_key' => $field_key,
                'field_type' => $field_type,
                'is_required' => $is_required,
                'is_active' => $is_active,
                'sort_order' => $sort_order,
                'options' => $this->encode_options(
                    $field_type,
                    $options
                ),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            return $this->database_error(
                'Unable to create the ticket field.'
            );
        }

        $field = $this->get_field_by_id(
            (int) $wpdb->insert_id
        );

        if (!$field) {
            return new WP_Error(
                'sweetdesk_field_creation_failed',
                'The ticket field was created but could not be retrieved.',
                ['status' => 500]
            );
        }

        return $field;
    }

    public function update_field(
        int $id,
        array $data
    ): array|WP_Error {
        global $wpdb;

        if ($id <= 0) {
            return new WP_Error(
                'sweetdesk_invalid_field_id',
                'A valid ticket field ID is required.',
                ['status' => 400]
            );
        }

        $existing = $this->get_field_by_id($id);

        if (!$existing) {
            return new WP_Error(
                'sweetdesk_field_not_found',
                'The requested ticket field was not found.',
                ['status' => 404]
            );
        }

        if ($data === []) {
            return new WP_Error(
                'sweetdesk_no_field_changes',
                'No ticket field properties were provided for update.',
                ['status' => 400]
            );
        }

        $name = array_key_exists('name', $data)
            ? sanitize_text_field($data['name'])
            : $existing['name'];

        $field_key = array_key_exists('field_key', $data)
            ? sanitize_key($data['field_key'])
            : $existing['field_key'];

        $field_type = array_key_exists('field_type', $data)
            ? sanitize_key($data['field_type'])
            : $existing['field_type'];

        $options = array_key_exists('options', $data)
            ? $this->sanitize_options($data['options'])
            : $existing['options'];

        $validation = $this->validate_field(
            $name,
            $field_key,
            $field_type,
            $options
        );

        if (is_wp_error($validation)) {
            return $validation;
        }

        if ($this->field_key_exists($field_key, $id)) {
            return new WP_Error(
                'sweetdesk_duplicate_field_key',
                'A ticket field with this key already exists.',
                ['status' => 409]
            );
        }

        /*
         * Existing ticket metadata uses field_key as meta_key.
         * Prevent changing the key after values have been stored.
         */
        if (
            $field_key !== $existing['field_key'] &&
            $this->field_has_values($existing['field_key'])
        ) {
            return new WP_Error(
                'sweetdesk_field_key_in_use',
                'The field key cannot be changed because ticket values already use it.',
                ['status' => 409]
            );
        }

        $updates = [];
        $formats = [];

        if (array_key_exists('name', $data)) {
            $updates['name'] = $name;
            $formats[] = '%s';
        }

        if (array_key_exists('field_key', $data)) {
            $updates['field_key'] = $field_key;
            $formats[] = '%s';
        }

        if (array_key_exists('field_type', $data)) {
            /*
             * Changing types after values exist may make historical data
             * incompatible, so prevent it once the field is in use.
             */
            if (
                $field_type !== $existing['field_type'] &&
                $this->field_has_values($existing['field_key'])
            ) {
                return new WP_Error(
                    'sweetdesk_field_type_in_use',
                    'The field type cannot be changed because ticket values already use this field.',
                    ['status' => 409]
                );
            }

            $updates['field_type'] = $field_type;
            $formats[] = '%s';
        }

        if (array_key_exists('is_required', $data)) {
            $updates['is_required'] =
                (int) rest_sanitize_boolean(
                    $data['is_required']
                );

            $formats[] = '%d';
        }

        if (array_key_exists('is_active', $data)) {
            $updates['is_active'] =
                (int) rest_sanitize_boolean(
                    $data['is_active']
                );

            $formats[] = '%d';
        }

        if (array_key_exists('sort_order', $data)) {
            $updates['sort_order'] =
                absint($data['sort_order']);

            $formats[] = '%d';
        }

        if (
            array_key_exists('options', $data) ||
            array_key_exists('field_type', $data)
        ) {
            $updates['options'] = $this->encode_options(
                $field_type,
                $options
            );

            $formats[] = '%s';
        }

        if ($updates === []) {
            return $existing;
        }

        $updates['updated_at'] =
            current_time('mysql', true);

        $formats[] = '%s';

        $updated = $wpdb->update(
            $this->fields_table,
            $updates,
            ['id' => $id],
            $formats,
            ['%d']
        );

        if ($updated === false) {
            return $this->database_error(
                'Unable to update the ticket field.'
            );
        }

        $field = $this->get_field_by_id($id);

        if (!$field) {
            return new WP_Error(
                'sweetdesk_field_update_failed',
                'The ticket field was updated but could not be retrieved.',
                ['status' => 500]
            );
        }

        return $field;
    }

    public function delete_field(int $id): true|WP_Error
    {
        global $wpdb;

        if ($id <= 0) {
            return new WP_Error(
                'sweetdesk_invalid_field_id',
                'A valid ticket field ID is required.',
                ['status' => 400]
            );
        }

        $field = $this->get_field_by_id($id);

        if (!$field) {
            return new WP_Error(
                'sweetdesk_field_not_found',
                'The requested ticket field was not found.',
                ['status' => 404]
            );
        }

        if ($this->field_has_values($field['field_key'])) {
            return new WP_Error(
                'sweetdesk_field_in_use',
                'This ticket field cannot be permanently deleted because ticket values use it. Deactivate it instead.',
                ['status' => 409]
            );
        }

        $deleted = $wpdb->delete(
            $this->fields_table,
            ['id' => $id],
            ['%d']
        );

        if ($deleted === false) {
            return $this->database_error(
                'Unable to permanently delete the ticket field.'
            );
        }

        if ($deleted === 0) {
            return new WP_Error(
                'sweetdesk_field_not_found',
                'The requested ticket field was not found.',
                ['status' => 404]
            );
        }

        return true;
    }

    private function get_field_by_id(int $id): ?array
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT
                    id,
                    name,
                    field_key,
                    field_type,
                    is_required,
                    is_active,
                    sort_order,
                    options,
                    created_at,
                    updated_at
                FROM {$this->fields_table}
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

        return $this->format_field($row);
    }

    private function field_key_exists(
        string $field_key,
        ?int $exclude_id = null
    ): bool {
        global $wpdb;

        if ($exclude_id !== null) {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT COUNT(*)
                    FROM {$this->fields_table}
                    WHERE field_key = %s
                      AND id != %d
                    ",
                    $field_key,
                    $exclude_id
                )
            );
        } else {
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT COUNT(*)
                    FROM {$this->fields_table}
                    WHERE field_key = %s
                    ",
                    $field_key
                )
            );
        }

        return (int) $count > 0;
    }

    private function field_has_values(
        string $field_key
    ): bool {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT COUNT(*)
                FROM {$this->ticket_meta_table}
                WHERE meta_key = %s
                ",
                $field_key
            )
        );

        return (int) $count > 0;
    }

    private function validate_field(
        string $name,
        string $field_key,
        string $field_type,
        array $options
    ): true|WP_Error {
        if ($name === '') {
            return new WP_Error(
                'sweetdesk_field_name_required',
                'A ticket field name is required.',
                ['status' => 400]
            );
        }

        if ($field_key === '') {
            return new WP_Error(
                'sweetdesk_field_key_required',
                'A valid ticket field key is required.',
                ['status' => 400]
            );
        }

        if (!in_array($field_type, self::FIELD_TYPES, true)) {
            return new WP_Error(
                'sweetdesk_invalid_field_type',
                'The specified ticket field type is not supported.',
                ['status' => 400]
            );
        }

        if (
            $field_type === 'select' &&
            $options === []
        ) {
            return new WP_Error(
                'sweetdesk_select_options_required',
                'Select fields must contain at least one option.',
                ['status' => 400]
            );
        }

        return true;
    }

    private function sanitize_options(mixed $options): array
    {
        if (!is_array($options)) {
            return [];
        }

        $sanitized = array_map(
            static function (mixed $option): string {
                return sanitize_text_field(
                    (string) $option
                );
            },
            $options
        );

        $sanitized = array_filter(
            $sanitized,
            static fn (string $option): bool =>
                $option !== ''
        );

        return array_values(array_unique($sanitized));
    }

    private function encode_options(
        string $field_type,
        array $options
    ): ?string {
        if ($field_type !== 'select') {
            return null;
        }

        return wp_json_encode($options);
    }

    private function format_field(array $field): array
    {
        $options = [];

        if (!empty($field['options'])) {
            $decoded = json_decode(
                $field['options'],
                true
            );

            if (is_array($decoded)) {
                $options = $decoded;
            }
        }

        return [
            'id' => (int) $field['id'],
            'name' => (string) $field['name'],
            'field_key' => (string) $field['field_key'],
            'field_type' => (string) $field['field_type'],
            'is_required' => (bool) $field['is_required'],
            'is_active' => (bool) $field['is_active'],
            'sort_order' => (int) $field['sort_order'],
            'options' => $options,
            'created_at' => (string) $field['created_at'],
            'updated_at' => (string) $field['updated_at'],
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
                'SweetDesk ticket field database error: ' .
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