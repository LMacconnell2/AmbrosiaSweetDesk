<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH .
    'src/api/services/team-service.php';

class SweetDesk_Team_Controller
{
    private SweetDesk_Team_Service $service;

    public function __construct()
    {
        $this->service = new SweetDesk_Team_Service();
    }

    public function permissions_check(WP_REST_Request $request)
    {
        return current_user_can('read');
    }

    public function get_teams(WP_REST_Request $request)
    {
        $params = [
            'q' => sanitize_text_field($request->get_param('q') ?? ''),
            'team_member_id' => sanitize_text_field($request->get_param('team_member_id') ?? ''),
            'page' => max(1, absint($request->get_param('page') ?? 1)),
            'per_page' => max(1, min(100, absint($request->get_param('per_page') ?? 25))),
        ];

        return rest_ensure_response($this->service->get_teams($params));
    }

    public function create_team(WP_REST_Request $request)
    {
        $body = $request->get_json_params();

        $result = $this->service->create_team($body);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Team created successfully.',
            'data' => [
                'id' => $result,
            ],
        ]);
    }

    public function get_team(WP_REST_Request $request)
    {
        $id = absint($request['id']);
        $team = $this->service->get_team($id);

        if (!$team) {
            return new WP_Error(
                'sweetdesk_team_not_found',
                'Team not found.',
                ['status' => 404]
            );
        }

        return rest_ensure_response($team);
    }

    public function update_team(WP_REST_Request $request)
    {
        $id = absint($request['id']);
        $body = $request->get_json_params();

        $result = $this->service->update_team($id, $body);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Team updated successfully.',
            'data' => [
                'id' => $id,
            ],
        ]);
    }

    public function delete_team(WP_REST_Request $request)
    {
        $id = absint($request['id']);
        $result = $this->service->delete_team($id);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Team deleted successfully.',
        ]);
    }

    public function update_team_people(WP_REST_Request $request)
    {
        $id = absint($request['id']);
        $body = $request->get_json_params();

        $result = $this->service->update_team_people($id, $body['people'] ?? []);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Team members updated successfully.',
            'data' => [
                'team_id' => $id,
                'people' => $result,
            ],
        ]);
    }

    public function remove_team_person(WP_REST_Request $request)
    {
        $team_id = absint($request['id']);
        $person_id = absint($request['person_id']);

        $result = $this->service->remove_team_person($team_id, $person_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Person removed from team successfully.',
        ]);
    }

    public function search_people(WP_REST_Request $request)
    {
        $params = [
            'q' => sanitize_text_field($request->get_param('q') ?? ''),
            'role' => sanitize_text_field($request->get_param('role') ?? ''),
        ];

        return rest_ensure_response([
            'data' => $this->service->search_people($params),
        ]);
    }
}