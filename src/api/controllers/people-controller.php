<?php

if (!defined('ABSPATH')) exit;

require_once SWEETDESK_PATH .
    'src/api/services/people-service.php';

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

    public function permissions_check(WP_REST_Request $request)
    {
        return SweetDesk_Portal_Access::rest_require_staff();
    }

    public function get_people_lookup(WP_REST_Request $request)
    {
        try {
            $filters = [
                'q' => $request->get_param('q'),
                'internal' => $request->has_param('internal')
                    ? rest_sanitize_boolean($request->get_param('internal'))
                    : null,
                'roles' => $request->get_param('roles'),
                'client_ids' => $request->get_param('client_ids'),
                'limit' => $request->get_param('limit') ?: 100,
            ];

            $people = $this->service->get_people_lookup($filters);

            return new WP_REST_Response(
                [
                    'success' => true,
                    'data' => $people,
                    'count' => count($people),
                ],
                200
            );
        } catch (InvalidArgumentException $exception) {
            return new WP_Error(
                'sweetdesk_invalid_people_lookup_request',
                $exception->getMessage(),
                ['status' => 400]
            );
        } catch (Throwable $exception) {
            return new WP_Error(
                'sweetdesk_people_lookup_failed',
                'Unable to retrieve the people lookup.',
                [
                    'status' => 500,
                ]
            );
        }
    }
}