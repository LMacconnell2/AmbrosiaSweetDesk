<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once SWEETDESK_PATH . 'src/api/services/analytics-service.php';

class SweetDesk_Analytics_Controller
{
    private SweetDesk_Analytics_Service $service;

    public function __construct()
    {
        $this->service = new SweetDesk_Analytics_Service();
    }

    public function permissions_check(WP_REST_Request $request)
    {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'sweetdesk_not_logged_in',
                __('You must be logged in to view analytics.', 'sweetdesk'),
                ['status' => 401]
            );
        }

        /*
         * Replace manage_options with a custom SweetDesk capability
         * once plugin-specific capabilities are registered.
         */
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'sweetdesk_analytics_forbidden',
                __('You do not have permission to view analytics.', 'sweetdesk'),
                ['status' => 403]
            );
        }

        return true;
    }

    public function get_summary(WP_REST_Request $request)
    {
        $filters = $this->get_reporting_filters($request);

        if (is_wp_error($filters)) {
            return $filters;
        }

        $result = $this->service->get_summary($filters);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    public function get_oldest_unresolved(WP_REST_Request $request)
    {
        $scope = sanitize_key((string) $request->get_param('scope'));
        $person_id = absint($request->get_param('person_id'));
        $limit = min(100, max(1, absint($request->get_param('limit') ?: 3)));

        $scope_error = $this->validate_scope($scope, $person_id);

        if (is_wp_error($scope_error)) {
            return $scope_error;
        }

        $result = $this->service->get_oldest_unresolved([
            'scope' => $scope,
            'person_id' => $person_id ?: null,
            'limit' => $limit,
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'data' => $result,
        ]);
    }

    public function get_recent_messages(WP_REST_Request $request)
    {
        $person_id = absint($request->get_param('person_id'));
        $source = sanitize_key((string) ($request->get_param('source') ?: 'all'));
        $limit = min(100, max(1, absint($request->get_param('limit') ?: 5)));

        if (!in_array($source, ['all', 'customer', 'staff'], true)) {
            return new WP_Error(
                'sweetdesk_invalid_message_source',
                __('Source must be all, customer, or staff.', 'sweetdesk'),
                ['status' => 400]
            );
        }

        $result = $this->service->get_recent_messages([
            'person_id' => $person_id ?: null,
            'source' => $source,
            'limit' => $limit,
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'data' => $result,
        ]);
    }

    public function export(WP_REST_Request $request)
    {
        $filters = $this->get_reporting_filters($request);

        if (is_wp_error($filters)) {
            return $filters;
        }

        $format = sanitize_key((string) ($request->get_param('format') ?: 'json'));

        if (!in_array($format, ['csv', 'json'], true)) {
            return new WP_Error(
                'sweetdesk_invalid_export_format',
                __('Export format must be csv or json.', 'sweetdesk'),
                ['status' => 400]
            );
        }

        /*
         * These are the same service methods used by the dashboard routes.
         * No analytics calculations are duplicated here.
         */
        $export_data = [
            'summary' => $this->service->get_summary($filters),
            'oldest_unresolved' => [
                'data' => $this->service->get_oldest_unresolved([
                    'scope' => $filters['scope'],
                    'person_id' => $filters['person_id'],
                    'limit' => 100,
                ]),
            ],
            'recent_messages' => [
                'data' => $this->service->get_recent_messages([
                    'person_id' => $filters['person_id'],
                    'source' => 'all',
                    'limit' => 100,
                    'date_start' => $filters['date_start'],
                    'date_end_exclusive' => $filters['date_end_exclusive'],
                ]),
            ],
        ];

        foreach ($export_data as $section) {
            if (is_wp_error($section)) {
                return $section;
            }

            if (
                is_array($section) &&
                isset($section['data']) &&
                is_wp_error($section['data'])
            ) {
                return $section['data'];
            }
        }

        $filename = sprintf(
            'sweetdesk-analytics-%s-%s-to-%s.%s',
            $filters['scope'],
            $filters['date_start'],
            $filters['date_end'],
            $format
        );

        if ($format === 'csv') {
            $csv = $this->service->build_csv_export($export_data);

            $response = new WP_REST_Response($csv, 200);
            $response->header('Content-Type', 'text/csv; charset=utf-8');
            $response->header(
                'Content-Disposition',
                'attachment; filename="' . sanitize_file_name($filename) . '"'
            );

            return $response;
        }

        $response = new WP_REST_Response($export_data, 200);
        $response->header('Content-Type', 'application/json; charset=utf-8');
        $response->header(
            'Content-Disposition',
            'attachment; filename="' . sanitize_file_name($filename) . '"'
        );

        return $response;
    }

    private function get_reporting_filters(WP_REST_Request $request)
    {
        $scope = sanitize_key((string) $request->get_param('scope'));
        $person_id = absint($request->get_param('person_id'));
        $date_start = sanitize_text_field(
            (string) $request->get_param('date_start')
        );
        $date_end = sanitize_text_field(
            (string) $request->get_param('date_end')
        );
        $team_id = absint($request->get_param('team_id'));

        $scope_error = $this->validate_scope($scope, $person_id);

        if (is_wp_error($scope_error)) {
            return $scope_error;
        }

        if (
            !$this->is_valid_date($date_start) ||
            !$this->is_valid_date($date_end)
        ) {
            return new WP_Error(
                'sweetdesk_invalid_date',
                __('Dates must use the YYYY-MM-DD format.', 'sweetdesk'),
                ['status' => 400]
            );
        }

        if ($date_start > $date_end) {
            return new WP_Error(
                'sweetdesk_invalid_date_range',
                __('date_start cannot be later than date_end.', 'sweetdesk'),
                ['status' => 400]
            );
        }

        $end_date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date_end,
            wp_timezone()
        );

        if (!$end_date) {
            return new WP_Error(
                'sweetdesk_invalid_end_date',
                __('The reporting end date is invalid.', 'sweetdesk'),
                ['status' => 400]
            );
        }

        return [
            'scope' => $scope,
            'person_id' => $person_id ?: null,
            'team_id' => $team_id ?: null,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'date_start_sql' => $date_start . ' 00:00:00',
            'date_end_exclusive' => $end_date
                ->modify('+1 day')
                ->format('Y-m-d 00:00:00'),
        ];
    }

    private function validate_scope(string $scope, int $person_id)
    {
        if (!in_array($scope, ['company', 'user'], true)) {
            return new WP_Error(
                'sweetdesk_invalid_analytics_scope',
                __('Scope must be company or user.', 'sweetdesk'),
                ['status' => 400]
            );
        }

        if ($scope === 'user' && $person_id < 1) {
            return new WP_Error(
                'sweetdesk_missing_person_id',
                __('person_id is required when scope is user.', 'sweetdesk'),
                ['status' => 400]
            );
        }

        return true;
    }

    private function is_valid_date(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed instanceof DateTimeImmutable &&
            $parsed->format('Y-m-d') === $date;
    }
}