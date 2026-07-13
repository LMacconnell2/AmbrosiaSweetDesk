<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH .
    'src/api/services/settings-status-service.php';

class SweetDesk_Settings_Status_Controller
{
    private SweetDesk_Settings_Status_Service $service;

    public function __construct()
    {
        $this->service = new SweetDesk_Settings_Status_Service();
    }

    /**
     * Allows logged-in SweetDesk users to retrieve active statuses.
     *
     * Administrators and settings managers may additionally request
     * inactive statuses.
     */
    public function view_permissions_check(
        WP_REST_Request $request
    ): bool|WP_Error {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'sweetdesk_not_authenticated',
                'You must be logged in to view ticket statuses.',
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
                'You do not have permission to view inactive statuses.',
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
                'You must be logged in to manage ticket statuses.',
                ['status' => 401]
            );
        }

        if (
            !current_user_can('sweetdesk_manage_settings') &&
            !current_user_can('manage_options')
        ) {
            return new WP_Error(
                'sweetdesk_forbidden',
                'You do not have permission to manage ticket statuses.',
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
                'You must be logged in to delete ticket statuses.',
                ['status' => 401]
            );
        }

        if (
            !current_user_can('sweetdesk_delete_settings') &&
            !current_user_can('manage_options')
        ) {
            return new WP_Error(
                'sweetdesk_forbidden',
                'You do not have permission to permanently delete ticket statuses.',
                ['status' => 403]
            );
        }

        return true;
    }

    public function get_statuses(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $include_inactive = rest_sanitize_boolean(
            $request->get_param('include_inactive')
        );

        $statuses = $this->service->get_statuses($include_inactive);

        if (is_wp_error($statuses)) {
            return $statuses;
        }

        return rest_ensure_response([
            'data' => $statuses,
            'count' => count($statuses),
        ]);
    }

    public function create_status(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $data = [
            'name' => $request->get_param('name'),
            'slug' => $request->get_param('slug'),
            'is_active' => $request->get_param('is_active'),
            'sort_order' => $request->get_param('sort_order'),
        ];

        $status = $this->service->create_status($data);

        if (is_wp_error($status)) {
            return $status;
        }

        return new WP_REST_Response(
            [
                'message' => 'Ticket status created successfully.',
                'data' => $status,
            ],
            201
        );
    }

    public function update_status(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $id = absint($request->get_param('id'));

        /*
         * Only pass values that were actually included in the request.
         * This prevents omitted properties from replacing existing values.
         */
        $data = [];

        foreach (
            ['name', 'slug', 'is_active', 'sort_order']
            as $property
        ) {
            if ($request->has_param($property)) {
                $data[$property] = $request->get_param($property);
            }
        }

        $status = $this->service->update_status($id, $data);

        if (is_wp_error($status)) {
            return $status;
        }

        return rest_ensure_response([
            'message' => 'Ticket status updated successfully.',
            'data' => $status,
        ]);
    }

    public function delete_status(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $id = absint($request->get_param('id'));

        $deleted = $this->service->delete_status($id);

        if (is_wp_error($deleted)) {
            return $deleted;
        }

        return rest_ensure_response([
            'message' => 'Ticket status permanently deleted.',
            'data' => [
                'id' => $id,
                'deleted' => true,
            ],
        ]);
    }
}