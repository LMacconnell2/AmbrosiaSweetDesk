<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Portal_Access {

    public const OPTION_PORTAL_PAGE_ID = 'sweetdesk_portal_page_id';

    public const SHORTCODE = 'sweetdesk_ticket_form';

    /**
     * Administrators and Editors have unrestricted site access.
     */
    public static function user_has_full_access(?WP_User $user = null): bool {
        if ($user === null) {
            if (!is_user_logged_in()) {
                return false;
            }

            $user = wp_get_current_user();
        }

        if (!$user instanceof WP_User || !$user->ID) {
            return false;
        }

        $roles = (array) $user->roles;

        return in_array('administrator', $roles, true)
            || in_array('editor', $roles, true);
    }

    /**
     * Guests and logged-in users without admin/editor roles are portal-only.
     */
    public static function user_is_portal_only(?WP_User $user = null): bool {
        return !self::user_has_full_access($user);
    }

    public static function get_portal_page_id(): int {
        $page_id = (int) get_option(self::OPTION_PORTAL_PAGE_ID, 0);

        if ($page_id > 0 && get_post_status($page_id) === 'publish') {
            $post = get_post($page_id);

            if (
                $post instanceof WP_Post
                && has_shortcode($post->post_content, self::SHORTCODE)
            ) {
                return $page_id;
            }
        }

        $found_id = self::find_portal_page_id();

        if ($found_id > 0) {
            update_option(self::OPTION_PORTAL_PAGE_ID, $found_id);
        }

        return $found_id;
    }

    public static function get_portal_page_url(): string {
        $page_id = self::get_portal_page_id();

        if ($page_id > 0) {
            $url = get_permalink($page_id);

            if ($url) {
                return $url;
            }
        }

        return home_url('/');
    }

    public static function is_portal_page(): bool {
        $portal_id = self::get_portal_page_id();

        if ($portal_id <= 0) {
            return false;
        }

        return is_page($portal_id);
    }

    public static function deny_access(): void {
        wp_die(
            esc_html__('You do not have permission to access this page.', 'ambrosia-sweetdesk'),
            esc_html__('Forbidden', 'ambrosia-sweetdesk'),
            [
                'response' => 403,
                'back_link' => false,
            ]
        );
    }

    public static function rest_require_staff(): bool {
        return self::user_has_full_access();
    }

    public static function rest_require_logged_in(): bool {
        return is_user_logged_in();
    }

    public function restrict_frontend_access(): void {
        if (is_admin()) {
            return;
        }

        if (wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }

        if (is_404()) {
            wp_die(
                esc_html__(
                    'This page does not exist or is no longer available.',
                    'ambrosia-sweetdesk'
                ),
                esc_html__('Page Not Found', 'ambrosia-sweetdesk'),
                [
                    'response' => 404,
                    'back_link' => false,
                ]
            );
        }

        if (self::user_has_full_access()) {
            return;
        }

        // Until a portal page exists, do not lock the whole frontend.
        $portal_id = self::get_portal_page_id();

        if ($portal_id <= 0) {
            return;
        }

        if (self::is_portal_page()) {
            return;
        }

        // Front page may be the portal page (is_page alone can miss some setups).
        if (is_front_page() && (int) get_option('page_on_front') === $portal_id) {
            return;
        }

        self::deny_access();
    }

    public function restrict_admin_access(): void {
        if (self::user_has_full_access()) {
            return;
        }

        if (wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        $portal_url = esc_url(self::get_portal_page_url());
        $logout_url = esc_url(wp_logout_url(self::get_portal_page_url()));

        $message = '<p>' . esc_html__(
            'You do not have permission to access this page.',
            'ambrosia-sweetdesk'
        ) . '</p>';
        $message .= '<p><a href="' . $portal_url . '">' . esc_html__(
            'Return to Submit a Ticket',
            'ambrosia-sweetdesk'
        ) . '</a></p>';
        $message .= '<p><a href="' . $logout_url . '">' . esc_html__(
            'Log out',
            'ambrosia-sweetdesk'
        ) . '</a></p>';

        wp_die(
            $message,
            esc_html__('Forbidden', 'ambrosia-sweetdesk'),
            [
                'response' => 403,
                'back_link' => false,
            ]
        );
    }

    public function redirect_after_login($redirect_to, $requested_redirect_to, $user) {
        if ($user instanceof WP_User && self::user_is_portal_only($user)) {
            return self::get_portal_page_url();
        }

        return $redirect_to;
    }

    public function hide_admin_bar($show) {
        if (is_user_logged_in() && self::user_is_portal_only()) {
            return false;
        }

        return $show;
    }

    /**
     * Persist the portal page ID when a page with the shortcode is saved.
     */
    public function track_portal_page(int $post_id): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id)) {
            return;
        }

        $post = get_post($post_id);

        if (!$post instanceof WP_Post || $post->post_type !== 'page') {
            return;
        }

        $has_shortcode = has_shortcode($post->post_content, self::SHORTCODE);
        $current_portal_id = (int) get_option(self::OPTION_PORTAL_PAGE_ID, 0);

        if ($has_shortcode && $post->post_status === 'publish') {
            update_option(self::OPTION_PORTAL_PAGE_ID, (int) $post_id);
            return;
        }

        if ($current_portal_id === (int) $post_id) {
            delete_option(self::OPTION_PORTAL_PAGE_ID);

            $found_id = self::find_portal_page_id();

            if ($found_id > 0) {
                update_option(self::OPTION_PORTAL_PAGE_ID, $found_id);
            }
        }
    }

    /**
     * Create the portal page with the ticket-form shortcode if none exists yet.
     * Also uses it as the site front page so / is the client portal.
     */
    public function ensure_portal_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (self::get_portal_page_id() > 0) {
            return;
        }

        $page_id = wp_insert_post(
            [
                'post_title' => 'Submit a Ticket',
                'post_name' => 'submit-a-ticket',
                'post_content' => '[' . self::SHORTCODE . ']',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => get_current_user_id(),
            ],
            true
        );

        if (is_wp_error($page_id) || !$page_id) {
            return;
        }

        update_option(self::OPTION_PORTAL_PAGE_ID, (int) $page_id);
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $page_id);
    }

    public function maybe_show_portal_notice(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (self::get_portal_page_id() > 0) {
            return;
        }

        echo '<div class="notice notice-warning"><p>';
        echo esc_html__(
            'SweetDesk: Create a page with [sweetdesk_ticket_form] to enable the client portal.',
            'ambrosia-sweetdesk'
        );
        echo '</p></div>';
    }

    private static function find_portal_page_id(): int {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'orderby' => 'ID',
            'order' => 'ASC',
            's' => '[' . self::SHORTCODE,
        ]);

        foreach ($pages as $page) {
            if (has_shortcode($page->post_content, self::SHORTCODE)) {
                return (int) $page->ID;
            }
        }

        // Fallback without search filter (s can miss shortcode-only pages).
        $all_pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        foreach ($all_pages as $page) {
            if (has_shortcode($page->post_content, self::SHORTCODE)) {
                return (int) $page->ID;
            }
        }

        return 0;
    }
}
