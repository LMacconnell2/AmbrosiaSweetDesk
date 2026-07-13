<?php

/**
 * Minimal frontend shell for the SweetDesk ticket portal.
 * Intentionally omits theme get_header()/get_footer() so nav and footer
 * link chrome from the active theme do not appear.
 */

if (!defined('ABSPATH')) {
    exit;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html(wp_get_document_title()); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('sweetdesk-portal'); ?>>
    <main class="sweetdesk-portal__main">
        <?php
        while (have_posts()) {
            the_post();
            the_content();
        }
        ?>
    </main>
    <?php wp_footer(); ?>
</body>
</html>
