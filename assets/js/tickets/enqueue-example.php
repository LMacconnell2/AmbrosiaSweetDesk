<?php
/**
 * Example enqueue order for the split ticket-list scripts.
 *
 * The localized SweetDesk object is attached to the state script so it exists
 * before the API and lookup modules execute.
 */

$plugin_url = plugin_dir_url(__FILE__) . '../../../';

wp_enqueue_script(
    'sweetdesk-tickets-state',
    $plugin_url . 'assets/js/tickets/tickets-state.js',
    ['sweetdesk-editor', 'sweetdesk-badge-helpers'],
    SWEETDESK_VERSION,
    true
);

wp_localize_script(
    'sweetdesk-tickets-state',
    'SweetDesk',
    [
        'apiUrl' => esc_url_raw(rest_url('sweetdesk/v1')),
        'nonce' => wp_create_nonce('wp_rest'),
        'ticketDetailBase' => admin_url(
            'admin.php?page=sweetdesk-ticket-detail&ticket_id='
        ),
    ]
);

wp_enqueue_script(
    'sweetdesk-tickets-api',
    $plugin_url . 'assets/js/tickets/tickets-api.js',
    ['sweetdesk-tickets-state'],
    SWEETDESK_VERSION,
    true
);

wp_enqueue_script(
    'sweetdesk-tickets-lookups',
    $plugin_url . 'assets/js/tickets/tickets-lookups.js',
    ['sweetdesk-tickets-state', 'sweetdesk-tickets-api'],
    SWEETDESK_VERSION,
    true
);

wp_enqueue_script(
    'sweetdesk-tickets-renderer',
    $plugin_url . 'assets/js/tickets/tickets-renderer.js',
    ['sweetdesk-tickets-state', 'sweetdesk-badge-helpers'],
    SWEETDESK_VERSION,
    true
);

wp_enqueue_script(
    'sweetdesk-tickets-filters',
    $plugin_url . 'assets/js/tickets/tickets-filters.js',
    [
        'sweetdesk-tickets-state',
        'sweetdesk-tickets-lookups',
        'sweetdesk-tickets-renderer',
    ],
    SWEETDESK_VERSION,
    true
);

wp_enqueue_script(
    'sweetdesk-tickets-modal',
    $plugin_url . 'assets/js/tickets/tickets-modal.js',
    [
        'sweetdesk-tickets-state',
        'sweetdesk-tickets-api',
        'sweetdesk-tickets-lookups',
        'sweetdesk-tickets-renderer',
        'sweetdesk-editor',
    ],
    SWEETDESK_VERSION,
    true
);

wp_enqueue_script(
    'sweetdesk-tickets-transfer',
    $plugin_url . 'assets/js/tickets/tickets-transfer.js',
    ['sweetdesk-tickets-api'],
    SWEETDESK_VERSION,
    true
);

wp_enqueue_script(
    'sweetdesk-tickets-list',
    $plugin_url . 'assets/js/tickets/tickets-list.js',
    [
        'sweetdesk-tickets-state',
        'sweetdesk-tickets-api',
        'sweetdesk-tickets-lookups',
        'sweetdesk-tickets-renderer',
        'sweetdesk-tickets-filters',
        'sweetdesk-tickets-modal',
        'sweetdesk-tickets-transfer',
    ],
    SWEETDESK_VERSION,
    true
);
