<?php

class SweetDesk_API {

    public static function init() {

        add_action(
            'rest_api_init',
            [ self::class, 'register_routes' ]
        );
    }

    public static function register_routes() {

        register_rest_route(
            'sweetdesk/v1',
            '/tickets',
            [
                'methods'  => 'POST',

                'callback' => [
                    self::class,
                    'get_tickets'
                ],

                'permission_callback' => function () {

                    return current_user_can(
                        'manage_options'
                    );
                }
            ]
        );
    }

    public static function get_tickets( $request ) {

        $params = $request->get_json_params();

        $filters = $params['filters'] ?? [];

        $sort = $params['sort'] ?? [];

        $file =
            plugin_dir_path( __FILE__ ) .
            'data/tickets.json';

        $tickets =
            json_decode(
                file_get_contents( $file ),
                true
            );

        $tickets = self::apply_filters(
            $tickets,
            $filters
        );

        $tickets = self::apply_sort(
            $tickets,
            $sort
        );

        return rest_ensure_response(
            $tickets
        );
    }

    private static function apply_filters(
        $tickets,
        $filters
    ) {

        if ( empty( $filters ) ) {
            return $tickets;
        }

        $filtered = [];

        foreach ( $tickets as $ticket ) {

            $matches = true;

            foreach ( $filters as $filter ) {

                $field =
                    $filter['field'];

                $operator =
                    $filter['operator'];

                $value =
                    strtolower(
                        $filter['value']
                    );

                $ticket_value =
                    strtolower(
                        $ticket[ $field ] ?? ''
                    );

                switch ( $operator ) {

                    case 'equals':

                        if (
                            $ticket_value !== $value
                        ) {
                            $matches = false;
                        }

                        break;

                    case 'contains':

                        if (
                            strpos(
                                $ticket_value,
                                $value
                            ) === false
                        ) {
                            $matches = false;
                        }

                        break;

                    case 'before':

                        if (
                            $ticket_value >= $value
                        ) {
                            $matches = false;
                        }

                        break;

                    case 'after':

                        if (
                            $ticket_value <= $value
                        ) {
                            $matches = false;
                        }

                        break;
                }

                if ( ! $matches ) {
                    break;
                }
            }

            if ( $matches ) {
                $filtered[] = $ticket;
            }
        }

        return $filtered;
    }

    private static function apply_sort(
        $tickets,
        $sort
    ) {

        $field =
            $sort['field']
            ?? 'date_opened';

        $direction =
            $sort['direction']
            ?? 'desc';

        usort(
            $tickets,
            function ( $a, $b )
            use ( $field, $direction ) {

                $valA = $a[ $field ];
                $valB = $b[ $field ];

                if ( $valA == $valB ) {
                    return 0;
                }

                if ( $direction === 'asc' ) {

                    return (
                        $valA < $valB
                    ) ? -1 : 1;
                }

                return (
                    $valA > $valB
                ) ? -1 : 1;
            }
        );

        return $tickets;
    }
}