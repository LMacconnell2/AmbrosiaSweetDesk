<?php

if (!defined('ABSPATH')) {
    exit;
}

class SweetDesk_Frontend_Shortcodes {

    public function register_shortcodes(): void {
        add_shortcode(
            'sweetdesk_ticket_form',
            [$this, 'render_ticket_form']
        );
    }

    public function render_ticket_form($atts = []): string {
        $is_logged_in = is_user_logged_in();
        $portal_url = SweetDesk_Portal_Access::get_portal_page_url();
        $login_url = wp_login_url($portal_url);
        $logout_url = wp_logout_url($portal_url);
        $current_user_display = $is_logged_in
            ? wp_get_current_user()->display_name
            : '';

        ob_start();
        include SWEETDESK_PATH . 'src/views/frontend/ticket-form.php';
        return ob_get_clean();
    }

    public function enqueue_assets(): void {
        if (!$this->page_has_shortcode()) {
            return;
        }

        wp_enqueue_style(
            'sweetdesk-theme',
            SWEETDESK_URL . 'assets/css/ambrosia-theme.css',
            [],
            SWEETDESK_VERSION
        );

        wp_enqueue_style(
            'sweetdesk-components',
            SWEETDESK_URL . 'assets/css/ambrosia-components.css',
            ['sweetdesk-theme'],
            SWEETDESK_VERSION
        );

        wp_enqueue_style(
            'sweetdesk-ticket-form',
            SWEETDESK_URL . 'assets/css/ticket-form.css',
            ['sweetdesk-components'],
            SWEETDESK_VERSION
        );

        wp_enqueue_style(
            'quill-snow',
            SWEETDESK_URL . 'assets/vendor/quill/quill.snow.css',
            [],
            '1.3.7'
        );

        wp_enqueue_style(
            'sweetdesk-quill-editor',
            SWEETDESK_URL . 'assets/css/sweetdesk-editor.css',
            ['quill-snow', 'sweetdesk-theme'],
            SWEETDESK_VERSION
        );

        wp_enqueue_script(
            'quill',
            SWEETDESK_URL . 'assets/vendor/quill/quill.min.js',
            [],
            '1.3.7',
            true
        );

        wp_enqueue_script(
            'sweetdesk-editor',
            SWEETDESK_URL . 'assets/js/sweetdesk-editor.js',
            ['quill'],
            SWEETDESK_VERSION,
            true
        );

        wp_enqueue_script(
            'sweetdesk-ticket-form',
            SWEETDESK_URL . 'assets/js/ticket-form.js',
            ['sweetdesk-editor'],
            SWEETDESK_VERSION,
            true
        );

        $portal_url = SweetDesk_Portal_Access::get_portal_page_url();

        wp_localize_script(
            'sweetdesk-ticket-form',
            'SweetDeskTicketForm',
            [
                'apiUrl' => esc_url_raw(rest_url('sweetdesk/v1')),
                'nonce' => wp_create_nonce('wp_rest'),
                'loginUrl' => esc_url_raw(wp_login_url($portal_url)),
            ]
        );
    }

    /**
     * Use a chrome-free template on the portal page (no theme header/footer menus).
     */
    public function use_portal_template(string $template): string {
        if (!$this->page_has_shortcode()) {
            return $template;
        }

        $portal_template = SWEETDESK_PATH . 'src/views/frontend/portal-template.php';

        if (is_readable($portal_template)) {
            return $portal_template;
        }

        return $template;
    }

    private function page_has_shortcode(): bool {
        if (!is_singular()) {
            return false;
        }

        global $post;

        if (!$post instanceof WP_Post) {
            return false;
        }

        return has_shortcode($post->post_content, 'sweetdesk_ticket_form');
    }
}
