<?php
/**
 * Example enqueue order for the split ticket scripts.
 * Adjust SWEETDESK_PLUGIN_URL and paths to match your project.
 */

$ticket_scripts = [
    'sweetdesk-tickets-state' => 'assets/js/tickets/tickets-state.js',
    'sweetdesk-tickets-api' => 'assets/js/tickets/tickets-api.js',
    'sweetdesk-tickets-lookups' => 'assets/js/tickets/tickets-lookups.js',
    'sweetdesk-tickets-renderer' => 'assets/js/tickets/tickets-renderer.js',
    'sweetdesk-tickets-filters' => 'assets/js/tickets/tickets-filters.js',
    'sweetdesk-tickets-modal' => 'assets/js/tickets/tickets-modal.js',
    'sweetdesk-tickets-transfer' => 'assets/js/tickets/tickets-transfer.js',
];

$previous_handle = 'sweetdesk-admin'; // Replace with the handle that provides SweetDeskEditor/renderBadge.

foreach ($ticket_scripts as $handle => $relative_path) {
    wp_enqueue_script(
        $handle,
        SWEETDESK_PLUGIN_URL . $relative_path,
        [$previous_handle],
        SWEETDESK_VERSION,
        true
    );

    $previous_handle = $handle;
}

wp_enqueue_script(
    'sweetdesk-tickets-list',
    SWEETDESK_PLUGIN_URL . 'assets/js/tickets/tickets-list.js',
    [$previous_handle],
    SWEETDESK_VERSION,
    true
);

// Localize the final script, or retain localization on any earlier dependency.
wp_localize_script('sweetdesk-tickets-list', 'SweetDesk', [
    'apiUrl' => esc_url_raw(rest_url('sweetdesk/v1')),
    'nonce' => wp_create_nonce('wp_rest'),
    'ticketDetailBase' => admin_url('admin.php?page=sweetdesk-ticket&id='),
]);
