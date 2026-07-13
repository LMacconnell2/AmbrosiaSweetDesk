<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH . 'src/api/services/settings-fields-service.php';

class SweetDesk_Settings_Fields_Controller
{
    private SweetDesk_Settings_Fields_Service $service;

    public function __construct()
    {
        $this->service =
            new SweetDesk_Settings_Fields_Service();
    }

    public function view_permissions_check(
        WP_REST_Request $request
    ): bool|WP_Error {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'sweetdesk_not_authenticated',
                'You must be logged in to view ticket fields.',
                ['status' => 401]
            );
        }

        $include_inactive = rest_sanitize_boolean(
            $request->get_param('include_inactive')
        );

        if (
            $include_inactive &&
            !current_user_can('sweetdesk_manage_settings') &&
            !current_user_can('manage_options')
        ) {
            return new WP_Error(
                'sweetdesk_forbidden',
                'You do not have permission to view inactive ticket fields.',
                ['status' => 403]
            );
        }

        return true;
    }

    public function manage_permissions_check(): bool|WP_Error
    {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'sweetdesk_not_authenticated',
                'You must be logged in to manage ticket fields.',
                ['status' => 401]
            );
        }

        if (
            !current_user_can('sweetdesk_manage_settings') &&
            !current_user_can('manage_options')
        ) {
            return new WP_Error(
                'sweetdesk_forbidden',
                'You do not have permission to manage ticket fields.',
                ['status' => 403]
            );
        }

        return true;
    }

    public function delete_permissions_check(): bool|WP_Error
    {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'sweetdesk_not_authenticated',
                'You must be logged in to delete ticket fields.',
                ['status' => 401]
            );
        }

        if (
            !current_user_can('sweetdesk_delete_settings') &&
            !current_user_can('manage_options')
        ) {
            return new WP_Error(
                'sweetdesk_forbidden',
                'You do not have permission to permanently delete ticket fields.',
                ['status' => 403]
            );
        }

        return true;
    }

    public function get_fields(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $include_inactive = rest_sanitize_boolean(
            $request->get_param('include_inactive')
        );

        $fields = $this->service->get_fields($include_inactive);

        if (is_wp_error($fields)) {
            return $fields;
        }

        return rest_ensure_response([
            'data' => $fields,
            'count' => count($fields),
        ]);
    }

    public function create_field(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $data = [
            'name' => $request->get_param('name'),
            'field_key' => $request->get_param('field_key'),
            'field_type' => $request->get_param('field_type'),
            'is_required' => $request->get_param('is_required'),
            'is_active' => $request->get_param('is_active'),
            'sort_order' => $request->get_param('sort_order'),
            'options' => $request->get_param('options'),
        ];

        $field = $this->service->create_field($data);

        if (is_wp_error($field)) {
            return $field;
        }

        return new WP_REST_Response(
            [
                'message' => 'Ticket field created successfully.',
                'data' => $field,
            ],
            201
        );
    }

    public function update_field(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $id = absint($request->get_param('id'));
        $data = [];

        $properties = [
            'name',
            'field_key',
            'field_type',
            'is_required',
            'is_active',
            'sort_order',
            'options',
        ];

        foreach ($properties as $property) {
            if ($request->has_param($property)) {
                $data[$property] = $request->get_param($property);
            }
        }

        $field = $this->service->update_field($id, $data);

        if (is_wp_error($field)) {
            return $field;
        }

        return rest_ensure_response([
            'message' => 'Ticket field updated successfully.',
            'data' => $field,
        ]);
    }

    public function delete_field(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $id = absint($request->get_param('id'));

        $deleted = $this->service->delete_field($id);

        if (is_wp_error($deleted)) {
            return $deleted;
        }

        return rest_ensure_response([
            'message' => 'Ticket field permanently deleted.',
            'data' => [
                'id' => $id,
                'deleted' => true,
            ],
        ]);
    }
}