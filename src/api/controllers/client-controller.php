<?php

if (!defined('ABSPATH')) exit;

require_once SWEETDESK_PATH .
    'src/api/services/client-service.php';

class SweetDesk_Client_Controller {

    private SweetDesk_Client_Service $service;

    public function __construct() {
        $this->service = new SweetDesk_Client_Service();
    }

    public function get_clients(WP_REST_Request $request) {
        return rest_ensure_response(
            $this->service->get_clients([
                'q'        => sanitize_text_field($request->get_param('q') ?? ''),
                'page'     => absint($request->get_param('page') ?? 1),
                'per_page' => absint($request->get_param('per_page') ?? 25),
                'sort'     => sanitize_key($request->get_param('sort') ?? 'name'),
                'order'    => sanitize_key($request->get_param('order') ?? 'asc'),
            ])
        );
    }

    public function get_client(WP_REST_Request $request) {
        $id = absint($request['id']);
        return rest_ensure_response($this->service->get_client($id));
    }

    public function create_client(WP_REST_Request $request) {
        $data = $request->get_json_params();
        return rest_ensure_response($this->service->create_client($data));
    }

    public function update_client(WP_REST_Request $request) {
        $id = absint($request['id']);
        $data = $request->get_json_params();

        return rest_ensure_response($this->service->update_client($id, $data));
    }

    public function delete_client(WP_REST_Request $request) {
        $id = absint($request['id']);
        return rest_ensure_response($this->service->delete_client($id));
    }

    public function export_clients(WP_REST_Request $request) {
        return rest_ensure_response(
            $this->service->export_clients([
                'q' => sanitize_text_field($request->get_param('q') ?? ''),
                'include_people' => filter_var($request->get_param('include_people'), FILTER_VALIDATE_BOOLEAN),
                'include_recent_tickets' => filter_var($request->get_param('include_recent_tickets'), FILTER_VALIDATE_BOOLEAN),
            ])
        );
    }

    public function import_clients(WP_REST_Request $request) {
        $data = $request->get_json_params();
        return rest_ensure_response($this->service->import_clients($data));
    }

    public function permissions_check(WP_REST_Request $request)
    {
        return current_user_can('read');
    }
}