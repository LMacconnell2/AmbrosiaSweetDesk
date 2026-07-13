<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH . 'src/api/services/email-settings-service.php';

class SweetDesk_Email_Settings_Controller
{
    private SweetDesk_Email_Settings_Service $service;

    public function __construct()
    {
        $this->service = new SweetDesk_Email_Settings_Service();
    }

    public function permissions_check(): bool|WP_Error
    {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'sweetdesk_not_authenticated',
                'You must be logged in to manage email settings.',
                ['status' => 401]
            );
        }

        return true;
    }

    public function get_settings(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $user_id = get_current_user_id();

        $settings = $this->service->get_settings($user_id);

        if (is_wp_error($settings)) {
            return $settings;
        }

        return rest_ensure_response([
            'data' => $settings,
        ]);
    }

    public function update_settings(
        WP_REST_Request $request
    ): WP_REST_Response|WP_Error {
        $user_id = get_current_user_id();
        $data = [];

        $properties = [
            'additional_emails',
            'send_ticket_updates',
            'send_team_mentions',
        ];

        foreach ($properties as $property) {
            if ($request->has_param($property)) {
                $data[$property] = $request->get_param($property);
            }
        }

        $settings = $this->service->update_settings(
            $user_id,
            $data
        );

        if (is_wp_error($settings)) {
            return $settings;
        }

        return rest_ensure_response([
            'message' => 'Email settings updated successfully.',
            'data' => $settings,
        ]);
    }
}