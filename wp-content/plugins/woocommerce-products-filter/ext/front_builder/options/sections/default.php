<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

return [
    [
        'title' => esc_html__('Show title label if possible', 'woocommerce-products-filter'),
        'description' => esc_html__('Show/Hide meta block title on the front. This is default options for elements which has not their own options, and even this ones can not be accessible for 100%', 'woocommerce-products-filter'),
        'element' => 'switcher',
        'field' => 'show_title_label',
        'value' => 1//default
    ],
    [
        'title' => esc_html__('Show toggle button if possible', 'woocommerce-products-filter'),
        'description' => esc_html__('Show toggle button near the title on the front above the block of html-items', 'woocommerce-products-filter'),
        'element' => 'select',
        'field' => 'show_toggle_button',
        'value' => [
            'value' => 0,
            'options' => [
                0 => esc_html__('No', 'woocommerce-products-filter'),
                1 => esc_html__('Yes, show as closed', 'woocommerce-products-filter'),
                2 => esc_html__('Yes, show as opened', 'woocommerce-products-filter')
            ],
        ]
    ],
    [
        'title' => esc_html__('Tooltip if possible', 'woocommerce-products-filter'),
        'description' => esc_html__('Tooltip text if necessary', 'woocommerce-products-filter'),
        'element' => 'textarea',
        'field' => 'tooltip_text',
        'value' => ''
    ],
];

