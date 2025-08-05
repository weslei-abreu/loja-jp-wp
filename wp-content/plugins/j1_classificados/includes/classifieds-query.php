<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Inclui os classificados nas queries padrão de produtos
 */
add_action('pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'product' ) {
        $query->set('post_type', array('product', 'classified'));
    }
});
