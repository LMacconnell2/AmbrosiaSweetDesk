<?php

if (!defined('ABSPATH')) exit;

class SweetDesk_People_Controller {

    private SweetDesk_People_Service $service;

    public function __construct() {
        $this->service = new SweetDesk_People_Service();
    }

    public function get_people(WP_REST_Request $request) {
        return rest_ensure_response($this->service->get_people([
            'q'          => sanitize_text_field($request->get_param('q') ?? ''),
            'roles'      => sanitize_text_field($request->get_param('roles') ?? ''),
            'team_ids'   => sanitize_text_field($request->get_param('team_ids') ?? ''),
            'client_ids' => sanitize_text_field($request->get_param('client_ids') ?? ''),
            'internal'   => $request->get_param('internal'),
            'is_active'  => $request->get_param('is_active'),
            'page'       => absint($request->get_param('page') ?? 1),
            'per_page'   => absint($request->get_param('per_page') ?? 25),
            'sort'       => sanitize_key($request->get_param('sort') ?? 'last_name'),
            'order'      => sanitize_key($request->get_param('order') ?? 'asc'),
        ]));
    }

    public function get_person(WP_REST_Request $request) {
        return rest_ensure_response(
            $this->service->get_person(absint($request['id']))
        );
    }

    public function create_person(WP_REST_Request $request) {
        return rest_ensure_response(
            $this->service->create_person($request->get_json_params() ?: [])
        );
    }

    public function update_person(WP_REST_Request $request) {
        return rest_ensure_response(
            $this->service->update_person(
                absint($request['id']),
                $request->get_json_params() ?: []
            )
        );
    }

    public function delete_person(WP_REST_Request $request) {
        return rest_ensure_response(
            $this->service->delete_person(absint($request['id']))
        );
    }

    public function export_people(WP_REST_Request $request) {
        return $this->service->export_people([
            'q'          => sanitize_text_field($request->get_param('q') ?? ''),
            'roles'      => sanitize_text_field($request->get_param('roles') ?? ''),
            'team_ids'   => sanitize_text_field($request->get_param('team_ids') ?? ''),
            'client_ids' => sanitize_text_field($request->get_param('client_ids') ?? ''),
            'internal'   => $request->get_param('internal'),
            'is_active'  => $request->get_param('is_active'),
        ]);
    }

    public function import_people(WP_REST_Request $request) {
        return rest_ensure_response(
            $this->service->import_people($request)
        );
    }
}