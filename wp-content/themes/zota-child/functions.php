<?php
/**
 * @version    1.0
 * @package    zota
 * @author     Thembay Team <support@thembay.com>
 * @copyright  Copyright (C) 2023 Thembay.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: https://thembay.com
 */
  function zota_child_enqueue_styles() {
    wp_enqueue_style( 'zota-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'zota-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'zota-style' ),
        wp_get_theme()->get('Version')
    );
  }

add_action(  'wp_enqueue_scripts', 'zota_child_enqueue_styles', 10000 );
// Desativa as requisições AJAX e REST da wishlist (YITH)
add_filter( 'yith_wcwl_load_ajax', '__return_false' );
