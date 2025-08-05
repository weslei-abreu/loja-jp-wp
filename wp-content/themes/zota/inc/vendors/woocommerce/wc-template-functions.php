<?php
if (!function_exists('zota_tbay_get_products')) {
    function zota_tbay_get_products($categories = array(), $product_type = 'featured_product', $paged = 1, $post_per_page = -1, $orderby = '', $order = '', $offset  = 0)
    {
        global $woocommerce, $wp_query;
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $post_per_page,
            'post_status' => 'publish',
            'paged' => $paged,
            'orderby'   => $orderby,
            'order' => $order,
            'offset' => $offset,
            'meta_query'     => WC()->query->get_meta_query(),
            'tax_query'      => WC()->query->get_tax_query(),
        );

        if (isset($args['orderby'])) {
            if ('price' == $args['orderby']) {
                $args = array_merge($args, array(
                    'meta_key'  => '_price',
                    'orderby'   => 'meta_value_num'
                ));
            }
            if ('featured' == $args['orderby']) {
                $args = array_merge($args, array(
                    'meta_key'  => '_featured',
                    'orderby'   => 'meta_value'
                ));
            }
            if ('sku' == $args['orderby']) {
                $args = array_merge($args, array(
                    'meta_key'  => '_sku',
                    'orderby'   => 'meta_value'
                ));
            }
        }

        if (!empty($categories) && is_array($categories)) {
            $args['tax_query']    = array(
                array(
                    'taxonomy'      => 'product_cat',
                    'field'         => 'slug',
                    'terms'         => $categories,
                    'operator'      => 'IN'
                )
            );
        }

        switch ($product_type) {
            case 'best_selling':
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                $args['ignore_sticky_posts']   = 1;
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
                break;
            case 'featured_product':
                $args['ignore_sticky_posts']    = 1;
                $args['meta_query']             = array();
                $args['meta_query'][]           = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][]           = $woocommerce->query->visibility_meta_query();
                $args['tax_query'][]              = array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => 'featured',
                        'operator' => 'IN'
                    )
                );

                break;
            case 'top_rate':
                $args['meta_key']       = '_wc_average_rating';
                $args['orderby']        = 'meta_value_num';
                $args['order']          = 'DESC';
                $args['meta_query']     = array();
                $args['meta_query'][]   = WC()->query->get_meta_query();
                $args['tax_query'][]    = WC()->query->get_tax_query();
                break;

            case 'recent_product':
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                break;
            case 'random_product':
                $args['orderby']    = 'rand';
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                break;
            case 'deals':
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                $args['post__in'] = $product_ids_on_sale;
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
                $args['meta_query'][] =  array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key'           => '_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                        array(
                            'key'           => '_min_variation_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                    ),
                    array(
                        'key'           => '_sale_price_dates_to',
                        'value'         => time(),
                        'compare'       => '>',
                        'type'          => 'numeric'
                    ),
                );
                break;
            case 'on_sale':
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                $args['post__in'] = $product_ids_on_sale;
                break;
        }

        wc_reset_loop();

        return new WP_Query($args);
    }
}

if (!function_exists('zota_tbay_get_woocommerce_mini_cart')) {
    function zota_tbay_get_woocommerce_mini_cart($args = array())
    {

        $args = wp_parse_args(
            $args,
            array(
                'icon_mini_cart'                => [
                    'value' => 'tb-icon tb-icon-shopping-cart',
                ],
                'show_title_mini_cart'          => '',
                'title_mini_cart'               => esc_html__('Shopping cart', 'zota'),
                'title_dropdown_mini_cart'      => esc_html__('Shopping cart', 'zota'),
                'price_mini_cart'               => '',
            )
        );

        $position = apply_filters('zota_cart_position', 10, 2);

        $mark = '';
        if(!empty($position)) {
            $mark = '-';
        }

        wc_get_template('cart/mini-cart-button'.$mark.$position.'.php', array('args' => $args)) ;
    }
}

// breadcrumb for woocommerce page
if (!function_exists('zota_tbay_woocommerce_breadcrumb_defaults')) {
    function zota_tbay_woocommerce_breadcrumb_defaults($args)
    {
        if(zota_checkout_optimized()) {
            return;
        }

        global $post,$wp_query;

        $breadcrumb_img = zota_tbay_get_config('woo_breadcrumb_image');
        $breadcrumb_color = zota_tbay_get_config('woo_breadcrumb_color');
        $style = array();
        $img = '';

        $sidebar_configs = zota_tbay_get_woocommerce_layout_configs();


        $breadcrumbs_layout = zota_tbay_get_config('product_breadcrumb_layout', 'color');


        if(isset($_GET['breadcrumbs_layout'])) {
            $breadcrumbs_layout = $_GET['breadcrumbs_layout'];
        }

        $class_container = '';
        if(isset($sidebar_configs['container_full']) &&  $sidebar_configs['container_full']) {
            $class_container = 'container-full';
        }

        switch ($breadcrumbs_layout) {
            case 'image':
                $breadcrumbs_class = ' breadcrumbs-image';
                break;
            case 'color':
                $breadcrumbs_class = ' breadcrumbs-color';
                break;
            case 'text':
                $breadcrumbs_class = ' breadcrumbs-text';
                break;
            default:
                $breadcrumbs_class  = ' breadcrumbs-text';
        }

        if(isset($sidebar_configs['breadscrumb_class'])) {
            $breadcrumbs_class .= ' '.$sidebar_configs['breadscrumb_class'];
        }


        $current_page = true;

        switch ($current_page) {
            case is_shop():
                $page_id = wc_get_page_id('shop');
                break;
            case is_checkout():
            case is_order_received_page():
                $page_id = wc_get_page_id('checkout');
                break;
            case is_edit_account_page():
            case is_add_payment_method_page():
            case is_lost_password_page():
            case is_account_page():
            case is_view_order_page():
                $page_id = wc_get_page_id('myaccount');
                break;

            case is_page():
                $page_id = $post->ID;
                break;

            default:
                $page_id = get_queried_object()->term_id;
                break;
        }


        if (isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url']) && $breadcrumbs_layout !== 'color' && $breadcrumbs_layout !== 'text') {
            $img = '<img src="'.esc_url($breadcrumb_img['url']).'">';
        }

        if($breadcrumb_color && $breadcrumbs_layout !== 'image') {
            $style[] = 'background-color:'.$breadcrumb_color;
        }

        $estyle = (!empty($style) && $breadcrumbs_layout !== 'text') ? ' style="'.implode(";", $style).'"' : "";

        $title = $nav = '';

        if($breadcrumbs_layout == 'image') {

            if(is_product_category()) {
                $title = '<h1 class="page-title">'. single_cat_title('', false) .'</h1>';
            } else {
                $title = '<h1 class="page-title">'. get_the_title($page_id) .'</h1>';
            }

        } else {

            if(zota_tbay_get_config('enable_previous_page_woo', true)) {
                $nav .= '<a href="javascript:history.back()" class="zota-back-btn"><i class="tb-icon tb-icon-angle-left"></i><span class="text">'. esc_html__('Previous page', 'zota') .'</span></a>';
                $breadcrumbs_class .= ' active-nav-right';
            }

        }

        $args['wrap_before'] = '<section id="tbay-breadcrumb" '.$estyle.' class="tbay-breadcrumb '.esc_attr($breadcrumbs_class).'">'.$img.'<div class="container '.$class_container.'"><div class="breadscrumb-inner">'. $title .'<ol class="tbay-woocommerce-breadcrumb breadcrumb">';
        $args['wrap_after'] = '</ol>'. $nav .'</div></div></section>';

        return $args;
    }
}

if (!function_exists('zota_tbay_woocommerce_get_cookie_display_mode')) {
    function zota_tbay_woocommerce_get_cookie_display_mode()
    {

        $woo_mode = zota_tbay_get_config('product_display_mode', 'grid');

        if(isset($_COOKIE['zota_display_mode']) && $_COOKIE['zota_display_mode'] == 'grid') {
            $woo_mode = 'grid';
        } elseif (isset($_COOKIE['zota_display_mode']) && $_COOKIE['zota_display_mode'] == 'grid2') {
            $woo_mode = 'grid2';
        } elseif(isset($_COOKIE['zota_display_mode']) && $_COOKIE['zota_display_mode'] == 'list') {
            $woo_mode = 'list';
        }

        return $woo_mode;
    }
}

if (!function_exists('zota_tbay_woocommerce_get_display_mode')) {
    function zota_tbay_woocommerce_get_display_mode()
    {

        $woo_mode = zota_tbay_woocommerce_get_cookie_display_mode();

        if(isset($_GET['display_mode']) && $_GET['display_mode'] == 'grid') {
            $woo_mode = 'grid';
        } elseif(isset($_GET['display_mode']) && $_GET['display_mode'] == 'list') {
            $woo_mode = 'list';
        }

        if(! zota_woo_is_vendor_page() && ! is_shop() && ! is_product_taxonomy()) {
            $woo_mode = 'grid';
        }


        return $woo_mode;
    }
}


/*Check not child categories*/
if(!function_exists('zota_is_check_not_child_categories')) {
    function zota_is_check_not_child_categories()
    {
        global $wp_query;

        if(is_product_category()) {

            $cat   = get_queried_object();
            $cat_id     = $cat->term_id;

            $args2 = array(
                'taxonomy'     => 'product_cat',
                'parent'       => $cat_id,
            );

            $sub_cats = get_categories($args2);
            if(!$sub_cats) {
                return true;
            }

        }

        return false;
    }
}

/*Check not product in categories*/
if(!function_exists('zota_is_check_hidden_filter')) {
    function zota_is_check_hidden_filter()
    {

        if(is_product_category()) {

            $checkchild_cat     =  zota_is_check_not_child_categories();

            if(!$checkchild_cat &&  'subcategories' === get_option('woocommerce_category_archive_display')) {
                return true;
            }
        }

        return false;
    }
}


// Two product thumbnail
if (!function_exists('zota_tbay_woocommerce_get_two_product_thumbnail')) {
    function zota_tbay_woocommerce_get_two_product_thumbnail($size)
    {
        global $product;

        $placeholder = wc_get_image_size($size);
        $placeholder_width = $placeholder['width'];
        $placeholder_height = $placeholder['height'];
        $post_thumbnail_id =  $product->get_image_id();

        $output = '';
        $class = 'image-no-effect';
        if (has_post_thumbnail()) {
            $attachment_ids = $product->get_gallery_image_ids();

            $class = ($attachment_ids && isset($attachment_ids[0])) ? 'attachment-shop_catalog image-effect' : $class;

            $output .= wp_get_attachment_image($post_thumbnail_id, $size, false, array('class' => $class ));

            if ($attachment_ids && isset($attachment_ids[0])) {
                $output .= wp_get_attachment_image($attachment_ids[0], $size, false, array('class' => 'image-hover' ));
            }

        } else {
            $output .= '<img src="'.wc_placeholder_img_src().'" alt="'. esc_attr__('Placeholder', 'zota'). '" class="'. esc_attr($class) .'" width="'. esc_attr($placeholder_width) .'" height="'. esc_attr($placeholder_height) .'" />';
        }
        return trim($output);
    }
}

// Slider product thumbnail
if (!function_exists('zota_tbay_woocommerce_get_silder_product_thumbnail')) {
    function zota_tbay_woocommerce_get_silder_product_thumbnail($size) {
        global $product;

        wp_enqueue_script('slick');
        wp_enqueue_script('zota-custom-slick');

        $placeholder = wc_get_image_size($size);
        $placeholder_width = $placeholder['width'];
        $placeholder_height = $placeholder['height'];
        $post_thumbnail_id = $product->get_image_id();

        $output = '';
        $class = 'image-no-effect';

        if (has_post_thumbnail()) {
            $class = 'item-slider';
            $output .= '<div class="tbay-product-slider-gallery">';
            $output .= '<div class="gallery_item first tbay-image-loaded">' . wp_get_attachment_image($post_thumbnail_id, $size, false, ['class' => $class]) . '</div>';

            $attachment_ids = $product->get_gallery_image_ids();
            $attachment_ids = array_slice($attachment_ids, 0, apply_filters('zota_tbay_product_gallery_limit', 8)); // Limit to maximum 8 images

            foreach ($attachment_ids as $attachment_id) {
                $output .= '<div class="gallery_item tbay-image-loaded">' . wp_get_attachment_image($attachment_id, $size, false, ['class' => $class]) . '</div>';
            }

            $output .= '</div>';
        } else {
            $output .= '<div class="gallery_item first tbay-image-loaded">';
            $output .= '<img src="' . wc_placeholder_img_src() . '" alt="' . esc_attr__('Placeholder', 'zota') . '" class="' . esc_attr($class) . '" width="' . esc_attr($placeholder_width) . '" height="' . esc_attr($placeholder_height) . '" />';
            $output .= '</div>';
        }

        return trim($output);
    }
}


if (!function_exists('zota_tbay_product_class')) {
    function zota_tbay_product_class($class = array())
    {
        global $product;

        $class_array    = array();

        $type           = apply_filters('zota_woo_config_product_layout', 10, 2);
        $class_varible  = zota_is_product_variable_sale();

        $class    = trim(join(' ', $class));
        if(!is_array($class)) {
            $class = explode(" ", $class);
        }

        array_push($class_array, "product-block", "grid", "product", $type, $class_varible);

        $class_array    = array_merge($class_array, $class);

        $class_array    = trim(join(' ', $class_array));

        echo 'class="' . esc_attr($class_array) . '"';
    }
}



if(! function_exists('zota_has_swatch')) {
    function zota_has_swatch($id, $attr_name, $value)
    {
        $swatches = array();

        $color = $image = $button = '';

        $term = get_term_by('slug', $value, $attr_name);
        if (is_object($term)) {
            $color      =   sanitize_hex_color(get_term_meta($term->term_id, 'product_attribute_color', true));
            $image      =   get_term_meta($term->term_id, 'product_attribute_image', true);
            $button      =   $term->name;
        }

        if($color != '') {
            $swatches['color']  = $color;
            $swatches['type']   = 'color';
        } elseif($image != '') {
            $swatches['image'] = $image;
            $swatches['type']   = 'image';
        } else {
            $swatches['button'] = $button;
            $swatches['type']   = 'button';
        }

        return $swatches;
    }
}


if(! function_exists('zota_get_option_variations')) {
    function zota_get_option_variations($attribute_name, $available_variations, $option = false, $product_id = false)
    {
        $swatches_to_show = array();
        foreach ($available_variations as $key => $variation) {
            $option_variation = array();
            $attr_key = 'attribute_' . $attribute_name;
            if(! isset($variation['attributes'][$attr_key])) {
                return;
            }

            $val = $variation['attributes'][$attr_key]; // red green black ..

            if(! empty($variation['image']['thumb_src'])) {
                $option_variation = array(
                    'variation_id' => $variation['variation_id'],
                    'image_src' => $variation['image']['thumb_src'],
                    'image_srcset' => $variation['image']['srcset'],
                    'image_sizes' => $variation['image']['sizes'],
                    'is_in_stock' => $variation['is_in_stock'],
                );
            }

            // Get only one variation by attribute option value
            if($option) {
                if($val != $option) {
                    continue;
                } else {
                    return $option_variation;
                }
            } else {
                // Or get all variations with swatches to show by attribute name

                $swatch = zota_has_swatch($product_id, $attribute_name, $val);
                $swatches_to_show[$val] = array_merge($swatch, $option_variation);

            }

        }

        return $swatches_to_show;

    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * Show attribute swatches list
 * ------------------------------------------------------------------------------------------------
 */
if (!function_exists('zota_swatches_list')) {
    function zota_swatches_list($attribute_name = false) {
        global $product;

        $id = $product->get_id();
        if (empty($id) || !$product->is_type('variable')) {
            return false;
        }

        $attribute_name = $attribute_name ?: zota_get_swatches_attribute();
        if (empty($attribute_name)) {
            return false;
        }

        $available_variations = $product->get_available_variations();
        if (empty($available_variations)) {
            return false;
        }

        $swatches_to_show = zota_get_option_variations($attribute_name, $available_variations, false, $id);
        if (empty($swatches_to_show)) {
            return false;
        }

        // Limit maximum 10 swatches
        $swatches_to_show = array_slice($swatches_to_show, 0, apply_filters('zota_swatches_list_limit', 10), true);

        // Preload all terms to avoid repeated queries
        $terms = wc_get_product_terms($id, $attribute_name, ['fields' => 'all']);
        $term_map = [];
        foreach ($terms as $term) {
            $term_map[$term->slug] = $term;
        }

        $out = '<div class="tbay-swatches-wrapper"><ul data-attribute_name="attribute_' . esc_attr($attribute_name) . '">';
        foreach ($swatches_to_show as $key => $swatch) {
            $style = $class = $data = '';
            $term = $term_map[$key] ?? null;
            $name = $term ? $term->name : '';

            if (isset($swatch['image_src'])) {
                $class .= 'swatch-has-image';
                $data .= 'data-image-src="' . esc_attr($swatch['image_src']) . '"';
                $data .= ' data-image-srcset="' . esc_attr($swatch['image_srcset']) . '"';
                $data .= ' data-image-sizes="' . esc_attr($swatch['image_sizes']) . '"';
                if (!$swatch['is_in_stock']) {
                    $class .= ' variation-out-of-stock';
                }
            }

            switch ($swatch['type']) {
                case 'color':
                    $style = 'background-color:' . esc_attr($swatch['color']);
                    $class .= ' variable-item-span-color';
                    break;
                case 'image':
                    $img = wp_get_attachment_image_src($swatch['image'], 'woocommerce_thumbnail');
                    $style = 'background-image: url(' . esc_url($img[0]) . ')';
                    $class .= ' variable-item-span-image';
                    break;
                case 'button':
                    $name = $swatch['button'];
                    $class .= ' variable-item-span-label';
                    break;
            }

            $out .= '<li><a href="javascript:void(0)" class="' . esc_attr($class) . ' swatch swatch-' . esc_attr(strtolower($key)) . '" style="' . esc_attr($style) . '" ' . $data . ' data-toggle="tooltip" title="' . esc_attr($name) . '">' . esc_html($name) . '</a></li>';
        }

        $out .= '</ul></div>';
        return $out;
    }
}

if(! function_exists('zota_get_swatches_attribute')) {
    function zota_get_swatches_attribute()
    {
        $custom = get_post_meta(get_the_ID(), '_zota_attribute_select', true);

        return empty($custom) ? zota_tbay_get_config('variation_swatch') : $custom;
    }
}


// get layout configs
if (!function_exists('zota_tbay_get_woocommerce_layout_configs')) {
    function zota_tbay_get_woocommerce_layout_configs()
    {

        if(function_exists('dokan_is_store_page') && dokan_is_store_page()) {
            return;
        }

        if(!is_product()) {
            $page = 'product_archive_sidebar';
        } else {
            $page = 'product_single_sidebar';
        }

        $sidebar = zota_tbay_get_config($page);


        if (!is_singular('product')) {

            $product_archive_layout  =   (isset($_GET['product_archive_layout'])) ? $_GET['product_archive_layout'] : zota_tbay_get_config('product_archive_layout', 'shop-left');


            if(zota_woo_is_mvxp_vendor_store()) {
                $sidebar = 'mvx-marketplace-store';

                if(!is_active_sidebar($sidebar)) {
                    $configs['main'] = array( 'class' => 'archive-full' );
                }
            }

            if (function_exists('dokan_is_store_page') && dokan_is_store_page() && dokan_get_option('enable_theme_store_sidebar', 'dokan_appearance', 'off') === 'off') {
                $product_archive_layout = 'full-width';
            }

            if(isset($product_archive_layout)) {
                switch ($product_archive_layout) {
                    case 'shop-left':
                        $configs['sidebar'] = array( 'id'  => $sidebar, 'class' => 'tbay-sidebar-shop col-12 col-xl-3'  );
                        $configs['main']    = array( 'class'    => 'col-12 col-xl-9' );
                        break;
                    case 'shop-right':
                        $configs['sidebar'] = array( 'id' => $sidebar,  'class' => 'tbay-sidebar-shop col-12 col-xl-3' );
                        $configs['main']    = array( 'class'    => 'col-12 col-xl-9' );
                        break;
                    default:
                        $configs['main']    = array( 'class' => 'archive-full' );
                        $configs['sidebar'] = array( 'id'  => $sidebar, 'class' => 'sidebar-desktop'  );
                        break;
                }

                if(($product_archive_layout === 'shop-left' ||  $product_archive_layout === 'shop-right') && (empty($configs['sidebar']['id']) || !is_active_sidebar($configs['sidebar']['id']))) {
                    $configs['main'] = array( 'class' => 'archive-full' );
                }
            }
        } else {

            $product_single_layout  =   (isset($_GET['product_single_layout'])) ? $_GET['product_single_layout'] : zota_get_single_select_layout();
            $class_main = '';
            $class_sidebar = '';
            if ($product_single_layout == 'left-main' || $product_single_layout == 'main-right') {

                $class_main = 'col-12 col-xl-9';
                $class_sidebar = 'col-12 col-xl-3';

                $sidebar = zota_tbay_get_config('product_single_sidebar', 'product-single');
            }
            if(isset($product_single_layout)) {
                switch ($product_single_layout) {
                    case 'vertical':
                        $configs['main']            = array( 'class' => 'archive-full' );
                        $configs['thumbnail']       = 'vertical';
                        $configs['breadscrumb']     = 'color';
                        break;
                    case 'horizontal':
                        $configs['main']            = array( 'class' => 'archive-full' );
                        $configs['thumbnail']       = 'horizontal';
                        $configs['breadscrumb']     = 'color';
                        break;
                    case 'left-main':
                        $configs['sidebar']         = array( 'id'  => $sidebar, 'class' => $class_sidebar  );
                        $configs['main']            = array( 'class' => $class_main );
                        $configs['thumbnail']       = 'horizontal';
                        $configs['breadscrumb']     = 'color';
                        break;
                    case 'main-right':
                        $configs['sidebar']         = array( 'id'  => $sidebar, 'class' => $class_sidebar  );
                        $configs['main']            = array( 'class' => $class_main );
                        $configs['thumbnail']       = 'horizontal';
                        $configs['breadscrumb']     = 'color';
                        break;
                    default:
                        $configs['main']            = array( 'class' => 'archive-full' );
                        $configs['thumbnail']       = 'horizontal';
                        $configs['breadscrumb']     = 'color';
                        break;
                }

                if(($product_single_layout === 'left-main' ||  $product_single_layout === 'main-right') && (empty($configs['sidebar']['id']) || !is_active_sidebar($configs['sidebar']['id']))) {
                    $configs['main'] = array( 'class' => 'archive-full' );
                }

            }

        }

        return $configs;
    }
}

if (!function_exists('zota_class_wrapper_start')) {
    function zota_class_wrapper_start()
    {

        $configs['content']                 = 'content';
        $configs['main']                    = 'main-wrapper ';

        if(function_exists('dokan_is_store_page') && dokan_is_store_page()) {
            return $configs;
        }

        $sidebar_configs                    = zota_tbay_get_woocommerce_layout_configs();
        $configs['content']                 = zota_add_cssclass($configs['content'], $sidebar_configs['main']['class']);

        if(!is_product()) {
            $configs['content']  = zota_add_cssclass($configs['content'], 'archive-shop');
            $class_main         =  (isset($_GET['product_archive_layout'])) ? $_GET['product_archive_layout'] : zota_tbay_get_config('product_archive_layout', 'shop-left');


            $configs['main']  = zota_add_cssclass($configs['main'], $class_main);
        } elseif(is_product()) {

            $configs['content']  = zota_add_cssclass($configs['content'], 'singular-shop');

            $class_main         =  (isset($_GET['product_single_layout'])) ? $_GET['product_single_layout'] : zota_tbay_get_config('product_single_layout', 'horizontal');


            $configs['main']  = zota_add_cssclass($configs['main'], $class_main);
        }

        return $configs;
    }
}

//get value fillter
if (! function_exists('zota_woocommerce_get_fillter')) {
    function zota_woocommerce_get_fillter($name, $default)
    {

        if (isset($_GET[$name])) :
            return $_GET[$name];
        else :
            return $default;
        endif;
    }
}

//Count product of category

if (!function_exists('zota_get_product_count_of_category')) {
    function zota_get_product_count_of_category($cat_id) {
        // Tạo khóa transient
        $transient_key = 'zota_product_count_cat_' . $cat_id;
        $count = get_transient($transient_key);

        if ($count === false) {
            $term = get_term($cat_id, 'product_cat');
            if ($term && !is_wp_error($term)) {
                $count = $term->count; // Lấy số lượng trực tiếp từ taxonomy
                set_transient($transient_key, $count, DAY_IN_SECONDS); // Lưu 1 ngày
            } else {
                $count = 0;
            }
        }

        return $count;
    }
}

if (!function_exists('zota_get_product_count_of_tags')) {
    function zota_get_product_count_of_tags($tag_id) {
        // Tạo khóa transient
        $transient_key = 'zota_product_count_tag_' . $tag_id;
        $count = get_transient($transient_key);

        if ($count === false) {
            $term = get_term($tag_id, 'product_tag');
            if ($term && !is_wp_error($term)) {
                $count = $term->count; // Lấy số lượng trực tiếp từ taxonomy
                set_transient($transient_key, $count, DAY_IN_SECONDS); // Lưu 1 ngày
            } else {
                $count = 0;
            }
        }

        return $count;
    }
}


/*Remove filter*/
if (! function_exists('zota_woocommerce_sub_categories')) {
    /**
     * Output the start of a product loop. By default this is a UL.
     *
     * @param bool $echo Should echo?.
     * @return string
     */
    function zota_woocommerce_sub_categories($echo = true)
    {
        ob_start();

        wc_set_loop_prop('loop', 0);

        $loop_start = apply_filters('zota_woocommerce_sub_categories', ob_get_clean());

        if ($echo) {
            echo trim($loop_start); // WPCS: XSS ok.
        } else {
            return $loop_start;
        }
    }
}


if (! function_exists('zota_is_product_variable_sale')) {
    function zota_is_product_variable_sale()
    {

        global $product;

        $class =  '';
        if($product->is_type('variable') && $product->is_on_sale()) {
            $class = 'tbay-variable-sale';
        }

        return $class;
    }
}

if (! function_exists('zota_woo_content_class')) {
    function zota_woo_content_class($class = '')
    {
        $sidebar_configs = zota_tbay_get_woocommerce_layout_configs();

        if(!(isset($sidebar_configs['right']) && is_active_sidebar($sidebar_configs['right']['sidebar'])) && !(isset($sidebar_configs['left']) && is_active_sidebar($sidebar_configs['left']['sidebar']))) {
            $class .= ' col-12';
        }

        return $class;
    }
}

if (! function_exists('zota_wc_wrapper_class')) {
    function zota_wc_wrapper_class($class = '')
    {

        $content_class = zota_woo_content_class($class);

        return apply_filters('zota_wc_wrapper_class', $content_class);
    }
}


if (!function_exists('zota_find_matching_product_variation')) {
    function zota_find_matching_product_variation($product, $attributes)
    {

        foreach($attributes as $key => $value) {
            if(strpos($key, 'attribute_') === 0) {
                continue;
            }

            unset($attributes[ $key ]);
            $attributes[ sprintf('attribute_%s', $key) ] = $value;
        }

        if(class_exists('WC_Data_Store')) {

            $data_store = WC_Data_Store::load('product');
            return $data_store->find_matching_product_variation($product, $attributes);

        } else {

            return $product->get_matching_variation($attributes);

        }

    }
}

if (! function_exists('zota_get_default_attributes')) {
    function zota_get_default_attributes($product)
    {

        if(method_exists($product, 'get_default_attributes')) {

            return $product->get_default_attributes();

        } else {

            return $product->get_variation_default_attributes();

        }

    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * Compare button
 * ------------------------------------------------------------------------------------------------
 */
if (! function_exists('zota_the_yith_compare')) {
    function zota_the_yith_compare($product_id)
    {
        if (class_exists('YITH_Woocompare_Frontend')) { 
            ?>
            <div class="yith-compare">

                <?php do_action('zota_before_add_to_compare_button'); ?>

            </div>
        <?php }
        }
}

if (! function_exists('zota_remove_compare_button_from_loop')) {
    add_action( 'init', 'zota_remove_compare_button_from_loop', 20 );
    function zota_remove_compare_button_from_loop() {
        if ( class_exists( 'YITH_Woocompare_Frontend' ) ) {
            $frontend = new YITH_WooCompare_Frontend();
            global $yith_woocompare;
            
            remove_action( 'woocommerce_after_shop_loop_item', array( $yith_woocompare->obj, 'output_button' ), 20 );
            add_action( 'zota_before_add_to_compare_button', array( $frontend, 'output_button' ), 10 );
        }
    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * WishList button
 * ------------------------------------------------------------------------------------------------
 */

if(! function_exists('zota_the_yith_wishlist')) {
    function zota_the_yith_wishlist()
    {

        if(!class_exists('YITH_WCWL')) {
            return;
        }

        $enabled_on_loop = 'yes' == get_option('yith_wcwl_show_on_loop', 'no');

        if(!class_exists('YITH_WCWL_Shortcode') || $enabled_on_loop) {
            return;
        }

        $active         = zota_tbay_get_config('enable_wishlist_mobile', false);

        $class_mobile   = ($active) ? 'shown-mobile' : '';

        echo '<div class="button-wishlist '. esc_attr($class_mobile) .'" title="'. esc_attr__('Wishlist', 'zota') . '">'.YITH_WCWL_Shortcode::add_to_wishlist(array()).'</div>';
    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * The Flash Sale
 * ------------------------------------------------------------------------------------------------
 */

if(! function_exists('zota_tbay_class_flash_sale')) {
    function zota_tbay_class_flash_sale($flash_sales)
    {
        global $product;

        if(isset($flash_sales) && $flash_sales) {
            $class_sale    = (!$product->is_on_sale()) ? 'tbay-not-flash-sale' : '';
            return $class_sale;
        }

    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * The Item Deal ended Flash Sale
 * ------------------------------------------------------------------------------------------------
 */

if(! function_exists('zota_tbay_item_deal_ended_flash_sale')) {
    function zota_tbay_item_deal_ended_flash_sale($flash_sales, $end_date)
    {
        global $product;

        $today      = strtotime("today");


        if($today > $end_date) {
            return;
        }

        $output = '';
        if(isset($flash_sales) && $flash_sales && !$product->is_on_sale()) {

            $output .= '<div class="item-deal-ended">';
            $output .= '<span>'. esc_html__('Deal ended', 'zota') .'</span>';
            $output .= '</div>';

        }
        echo trim($output);
    }
}


/**
 * ------------------------------------------------------------------------------------------------
 * The Count Down Flash Sale
 * ------------------------------------------------------------------------------------------------
 */
if(!function_exists('zota_tbay_label_flash_sale')) {
    function zota_tbay_label_flash_sale()
    {
        if(!zota_tbay_get_config('enable_text_time_coutdown', false)) {
            $dates = array(
                'days' => '',
                'hours' => '',
                'mins' => '',
                'secs' => '',
            );
        } else {
            $dates = array(
                'days' => apply_filters('zota_tbay_countdown_flash_sale_day', '<span class="label">'. esc_html__('days', 'zota') .'</span>'),
                'hours' => apply_filters('zota_tbay_countdown_flash_sale_hour', '<span class="label">'. esc_html__('hours', 'zota') .'</span>'),
                'mins' => apply_filters('zota_tbay_countdown_flash_sale_mins', '<span class="label">'. esc_html__('mins', 'zota') .'</span>'),
                'secs' => apply_filters('zota_tbay_countdown_flash_sale_secs', '<span class="label">'. esc_html__('secs', 'zota') .'</span>'),
            );

        }
        return $dates;
    }
}
if(!function_exists('zota_tbay_countdown_flash_sale')) {
    function zota_tbay_countdown_flash_sale($time_sale = '', $date_title = '', $date_title_ended = '', $strtotime = false)
    {
        wp_enqueue_script('jquery-countdowntimer');
        $_id        = zota_tbay_random_key();

        $today      = strtotime("today");

        $dates = zota_tbay_label_flash_sale();
        $days = $dates['days'];
        $hours = $dates['hours'];
        $mins = $dates['mins'];
        $secs = $dates['secs'];
        if($strtotime) {
            $time_sale = strtotime($time_sale);
        }

        ?>
        <?php if(!empty($time_sale)) : ?>
            <div class="flash-sales-date">
                <?php if (($today <= $time_sale)): ?>
                    <?php if(isset($date_title) && !empty($date_title)) :  ?>
                        <div class="date-title"><?php echo trim($date_title); ?></div>
                    <?php endif; ?>
                    
                    <div class="time">
                        <div class="tbay-countdown" id="tbay-flash-sale-<?php echo esc_attr($_id);?>" data-time="timmer"
                             data-date="<?php echo gmdate('m', $time_sale).'-'.gmdate('d', $time_sale).'-'.gmdate('Y', $time_sale).'-'. gmdate('H', $time_sale) . '-' . gmdate('i', $time_sale) . '-' .  gmdate('s', $time_sale) ; ?>" data-days="<?php echo esc_attr($days); ?>" data-hours="<?php echo esc_attr($hours); ?>" data-mins="<?php echo esc_attr($mins); ?>" data-secs="<?php echo esc_attr($secs); ?>">
                        </div>
                    </div>
                    
                <?php else: ?>
                    
                <?php if(isset($date_title_ended) && !empty($date_title_ended)) :  ?>
                    <div class="date-title"><?php echo trim($date_title_ended); ?></div>
                <?php endif; ?>
            <?php endif; ?> 
            </div> 
        <?php endif; ?> 
        <?php
    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * The Count Down Flash Sale
 * ------------------------------------------------------------------------------------------------
 */
if(!function_exists('zota_tbay_stock_flash_sale')) {
    function zota_tbay_stock_flash_sale($flash_sales = '')
    {
        global $product;

        if($flash_sales && $product->get_manage_stock()) : ?>
            <div class="stock-flash-sale stock">
                <?php
                $total_sales        = $product->get_total_sales();
            $stock_quantity     = $product->get_stock_quantity();

            $total_quantity   = (int)$total_sales + (int)$stock_quantity;

            $divi_total_quantity = ($total_quantity !== 0) ? $total_quantity : 1;

            $sold             = (int)$total_sales / (int)$divi_total_quantity;
            $percentsold      = $sold * 100;

            ?>
                <span class="tb-sold"><?php echo esc_html__('Sold', 'zota'); ?>: <span class="sold"><?php echo esc_html($total_sales) ?></span><span class="total">/<?php echo esc_html($total_quantity) ?></span></span>
                <div class="progress">
                    <div class="progress-bar active" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr($percentsold); ?>%">
                    </div>
                </div>
            </div>
        <?php endif;
    }
}


/**
 * ------------------------------------------------------------------------------------------------
 * QuickView button
 * ------------------------------------------------------------------------------------------------
 */
if (! function_exists('zota_the_quick_view')) {
    function zota_the_quick_view($product_id)
    {

        if (zota_tbay_get_config('enable_quickview', false)) {
            wp_enqueue_script('slick');
            wp_enqueue_script('zota-custom-slick');
            ?>
            <div class="tbay-quick-view">
                <a href="#" class="qview-button" title ="<?php esc_attr_e('Quick View', 'zota') ?>" data-effect="mfp-move-from-top" data-product_id="<?php echo esc_attr($product_id); ?>" data-toggle="modal" data-target="#tbay-quickview-modal">
                    <i class="tb-icon tb-icon-eye"></i>
                    <span><?php esc_html_e('Quick View', 'zota') ?></span>
                </a>
            </div>
            <?php

        }
    }
}


/**
 * ------------------------------------------------------------------------------------------------
 * Product name
 * ------------------------------------------------------------------------------------------------
 */

if(! function_exists('zota_the_product_name')) {
    function zota_the_product_name()
    {

        $active         = zota_tbay_get_config('enable_one_name_mobile', false);

        $class_mobile   = ($active) ? 'full_name' : '';

        do_action('woocommerce_shop_loop_item_title');
        ?>
        <h3 class="name <?php echo esc_attr($class_mobile); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <?php
    }
}

if (! function_exists('zota_woo_is_mvxp_vendor_store')) {
    function zota_woo_is_mvxp_vendor_store()
    {

        if (! class_exists('MVX')) {
            return false;
        }

        global $MVX;
        if (empty($MVX)) {
            return false;
        }

        if (is_tax($MVX->taxonomy->taxonomy_name)) {
            return true;
        }

        return false;
    }
}

/**
 * Check is vendor page
 *
 * @return bool
 */
if (! function_exists('zota_woo_is_vendor_page')) {
    function zota_woo_is_vendor_page()
    {

        if (function_exists('dokan_is_store_page') && dokan_is_store_page()) {
            return true;
        }

        if (class_exists('WCV_Vendors') && method_exists('WCV_Vendors', 'is_vendor_page')) {
            return WCV_Vendors::is_vendor_page();
        }

        if (zota_woo_is_mvxp_vendor_store()) {
            return true;
        }

        if (function_exists('wcfm_is_store_page') && wcfm_is_store_page()) {
            return true;
        }

        return false;
    }
}


/**
 * Check is vendor page
 *
 * @return bool
 */


if (! function_exists('zota_custom_product_get_rating_html')) {
    function zota_custom_product_get_rating_html($html, $rating, $count)
    {
        global $product;

        $output = '';

        $review_count = $product->get_review_count();

        if(empty($review_count)) {
            $review_count = 0;
        }

        $class = (empty($review_count)) ? 'no-rate' : '';

        $output .= '<div class="rating '. esc_attr($class) .'">';
        $output .= $html;
        $output .= '<div class="count"><span>'. $review_count .'</span></div>';
        $output .= '</div>';

        echo trim($output);

    }
}


/**
 * ------------------------------------------------------------------------------------------------
 * Mini cart Button
 * ------------------------------------------------------------------------------------------------
 */
if (!function_exists('zota_tbay_minicart_button')) {
    function zota_tbay_minicart_button($icon, $enable_text, $text, $enable_price)
    {
        global $woocommerce;
        ?>

        <span class="cart-icon">

            <?php if(!empty($icon['value'])) : ?>
                <i class="<?php echo esc_attr($icon['value']); ?>"></i>
            <?php endif;  ?>
            <span class="mini-cart-items">
               <?php echo sprintf('%d', $woocommerce->cart->cart_contents_count);?>
            </span>
        </span>

        <?php if((($enable_text === 'yes') && !empty($text)) || ($enable_price === 'yes')) { ?>
            <span class="text-cart">

            <?php if(($enable_text === 'yes') && !empty($text)) : ?>
                <span><?php echo trim($text); ?></span>
            <?php endif; ?>

            <?php if($enable_price === 'yes') : ?>
                <span class="subtotal"><?php echo trim(WC()->cart->get_cart_subtotal());?></span>
            <?php endif; ?>

        </span>

        <?php }
        }
}

/*product time countdown*/
if(!function_exists('zota_woo_product_time_countdown')) {
    function zota_woo_product_time_countdown($countdown = false, $countdown_title = '')
    {
        global $product;

        if(!$countdown) {
            return;
        }

        wp_enqueue_script('jquery-countdowntimer');
        $time_sale = get_post_meta($product->get_id(), '_sale_price_dates_to', true);
        $_id = zota_tbay_random_key();
        $dates = zota_tbay_label_flash_sale();
        $days = $dates['days'];
        $hours = $dates['hours'];
        $mins = $dates['mins'];
        $secs = $dates['secs'];
        ?>
        <?php if ($time_sale): ?>
            <div class="time">
                <div class="timming">
                    <?php if(isset($countdown_title) && !empty($countdown_title)) :  ?>
                        <div class="date-title"><?php echo trim($countdown_title); ?></div>
                    <?php endif; ?>
                    <div class="tbay-countdown" id="tbay-flash-sale-<?php echo esc_attr($_id);?>" data-time="timmer" data-date="<?php echo gmdate('m', $time_sale).'-'.gmdate('d', $time_sale).'-'.gmdate('Y', $time_sale).'-'. gmdate('H', $time_sale) . '-' . gmdate('i', $time_sale) . '-' .  gmdate('s', $time_sale) ; ?>" data-days="<?php echo esc_attr($days); ?>" data-hours="<?php echo esc_attr($hours); ?>" data-mins="<?php echo esc_attr($mins); ?>" data-secs="<?php echo esc_attr($secs); ?>" >
                    </div>
                </div>
            </div> 
        <?php endif; ?> 
        <?php
    }
}
if(!function_exists('zota_woo_product_time_countdown_stock')) {
    function zota_woo_product_time_countdown_stock($countdown = false)
    {
        global $product;
        if(!$countdown) {
            return;
        }

        if($product->get_manage_stock()) {?>
            <div class="stock">
                <?php
                    $total_sales    = $product->get_total_sales();
            $stock_quantity   = $product->get_stock_quantity();

            if($stock_quantity >= 0) {
                $total_quantity   = (int)$total_sales + (int)$stock_quantity;
                $sold         = (int)$total_sales / (int)$total_quantity;
                $percentsold    = $sold * 100;
            }
            ?>
              
                <?php if(isset($percentsold)) { ?>
                    <div class="progress">
                        <div class="progress-bar active" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo esc_attr($percentsold); ?>%">
                        </div>
                    </div>
                <?php } ?>
                <?php if($stock_quantity >= 0) { ?>
                    <span class="tb-sold"><?php esc_html_e('Sold', 'zota'); ?> : <span class="sold"><?php echo esc_html($total_sales) ?></span><span class="total">/<?php echo esc_html($total_quantity) ?></span></span>
                <?php } ?>
            </div>
        <?php }
        }
}

if (!function_exists('zota_product_quickview_image')) {
    function zota_product_quickview_image()
    {
        wc_get_template('single-product/image-quickview.php');
    }
}

if(! function_exists('zota_get_single_select_layout')) {
    function zota_get_single_select_layout()
    {
        $custom = get_post_meta(get_the_ID(), '_zota_single_layout_select', true);

        return empty($custom) ? zota_tbay_get_config('product_single_layout') : $custom;
    }
}

if (!function_exists('zota_tbay_minicart')) {
    function zota_tbay_minicart()
    {
        $template = apply_filters('zota_tbay_minicart_version', '');
        get_template_part('woocommerce/cart/mini-cart-button', $template);
    }
}


/**
* Function For Multi Layouts Single Product
*/
//-----------------------------------------------------
/**
 * Override Output the product tabs.
 *
 * @subpackage  Product/Tabs
 */
if (!function_exists('zota_override_woocommerce_output_product_data_tabs')) {
    function woocommerce_output_product_data_tabs()
    {
        $tabs_layout   =  apply_filters('zota_woo_tabs_style_single_product', 10, 2);
        if(wp_is_mobile() && zota_tbay_get_config('enable_tabs_mobile', false) && $tabs_layout !== 'sidebar') {
            wc_get_template('single-product/tabs/tabs-mobile.php');
            return;
        }

        if($tabs_layout !== 'fulltext') {
            add_filter('woocommerce_product_description_heading', '__return_empty_string', 10, 1);
            add_filter('zota_woocommerce_product_more_product_heading', '__return_empty_string', 10, 1);
        }


        if(isset($tabs_layout)) {

            if($tabs_layout == 'tabs') {
                wc_get_template('single-product/tabs/tabs.php');
            } else {
                wc_get_template('single-product/tabs/tabs-'.$tabs_layout.'.php');
            }
        }
    }
}


if (!function_exists('zota_tbay_display_custom_tab_builder')) {
    function zota_tbay_display_custom_tab_builder($tabs)
    {
        global $tabs_builder;
        $tabs_builder = true;
        $args = array(
          'name'        => $tabs,
          'post_type'   => 'tbay_customtab',
          'post_status' => 'publish',
          'numberposts' => 1
        );

        $tabs = array();

        $posts = get_posts($args);
        foreach ($posts as $post) {
            $tabs['title'] = $post->post_title;
            $tabs['content'] = do_shortcode($post->post_content);
            return $tabs;
        }
        $tabs_builder = false;
    }
}

if (! function_exists('zota_get_product_categories')) {
    function zota_get_product_categories()
    {
        $category = get_terms(
            array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            )
        );
        $results = array();
        if (!is_wp_error($category)) {
            foreach ($category as $category) {
                $results[$category->slug] = $category->name.' ('.$category->count.') ';
            }
        }
        return $results;
    }
}

if(!function_exists('zota_get_thumbnail_gallery_item')) {
    function zota_get_thumbnail_gallery_item()
    {

        return apply_filters('zota_get_thumbnail_gallery_item', 'flex-control-nav.flex-control-thumbs li');
    }
}

if(!function_exists('zota_get_gallery_item_class')) {
    function zota_get_gallery_item_class()
    {

        return apply_filters('zota_get_gallery_item_class', "woocommerce-product-gallery__image");
    }
}

if (! function_exists('zota_video_type_by_url')) {
    /**
     * Retrieve the type of video, by url
     *
     * @param string $url The video's url
     *
     * @return mixed A string format like this: "type:ID". Return FALSE, if the url isn't a valid video url.
     *
     * @since 1.1.0
     */
    function zota_video_type_by_url($url)
    {

        $parsed = parse_url(esc_url($url));

        switch ($parsed['host']) {

            case 'www.youtube.com':
            case    'youtu.be':
                $id = zota_get_yt_video_id($url);

                return "youtube:$id";

            case 'vimeo.com':
            case 'player.vimeo.com':
                preg_match('/.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/', $url, $matches);
                $id = $matches[5];

                return "vimeo:$id";

            default:
                return apply_filters('zota_woocommerce_featured_video_type', false, $url);

        }
    }
}
if (! function_exists('zota_get_yt_video_id')) {
    /**
     * Retrieve the id video from youtube url
     *
     * @param string $url The video's url
     *
     * @return string The youtube id video
     *
     * @since 1.1.0
     */
    function zota_get_yt_video_id($url)
    {

        $pattern =
            '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x';
        $result  = preg_match($pattern, $url, $matches);
        if (false !== $result) {
            return $matches[1];
        }

        return false;
    }
}

if (! function_exists('zota_get_product_menu_bar')) {
    function zota_get_product_menu_bar()
    {
        $menu_bar   = zota_tbay_get_config('enable_sticky_menu_bar', false);

        if (isset($_GET['sticky_menu_bar'])) {
            $menu_bar = $_GET['sticky_menu_bar'];
        }

        return $menu_bar;
    }
    add_filter('zota_woo_product_menu_bar', 'zota_get_product_menu_bar');
}


/*cart fragments*/
if (! function_exists('zota_added_cart_fragments')) {
    add_filter('woocommerce_add_to_cart_fragments', 'zota_added_cart_fragments', 10, 1);
    function zota_added_cart_fragments($fragments)
    {
        ob_start();
        $fragments['.mini-cart-items'] = '<span class="mini-cart-items">'. WC()->cart->get_cart_contents_count() .'</span>';
        $fragments['.subtotal'] = '<span class="subtotal">'. WC()->cart->get_cart_subtotal() .'</span>';
        return $fragments;
    }
}

// Quantity mode
if (! function_exists('zota_woocommerce_quantity_mode_active')) {
    function zota_woocommerce_quantity_mode_active()
    {
        $catalog_mode = zota_catalog_mode_active();

        if($catalog_mode) {
            return false;
        }

        $active = zota_tbay_get_config('enable_woocommerce_quantity_mode', false);

        $active = (isset($_GET['quantity_mode'])) ? $_GET['quantity_mode'] : $active;

        return $active;
    }
}

if (! function_exists('zota_quantity_field_archive')) {
    function zota_quantity_field_archive()
    {

        global $product;
        if ($product && $product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually()) {
            woocommerce_quantity_input(array( 'min_value' => 1, 'max_value' => $product->backorders_allowed() ? '' : $product->get_stock_quantity() ));
        }

    }
}

if (! function_exists('zota_is_quantity_field_archive')) {
    function zota_is_quantity_field_archive()
    {
        global $product;

        if($product && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually()) {
            $max_value = $product->get_max_purchase_quantity();
            $min_value = $product->get_min_purchase_quantity();

            if($max_value && $min_value === $max_value) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }
}

if (! function_exists('zota_woocommerce_quantity_mode_add_class')) {
    add_filter('woocommerce_post_class', 'zota_woocommerce_quantity_mode_add_class', 10, 2);
    function zota_woocommerce_quantity_mode_add_class($classes)
    {
        if(!zota_woocommerce_quantity_mode_active()) {
            return $classes;
        }

        $classes[] = 'product-quantity-mode';

        return $classes;
    }
}


if (! function_exists('zota_woocommerce_quantity_mode_group_button')) {
    function zota_woocommerce_quantity_mode_group_button()
    {
        if(!zota_woocommerce_quantity_mode_active() || zota_is_woo_variation_swatches_pro()) {
            return;
        }

        global $product;
        if(zota_is_quantity_field_archive() &&  $product->is_type('simple')) {
            $class_active = 'active';
        } else {
            $class_active = '';
        }

        echo '<div class="quantity-group-btn '. esc_attr($class_active) .'">';
        if(zota_is_quantity_field_archive() && $product->is_type('simple')) {
            zota_quantity_field_archive();
        }
        woocommerce_template_loop_add_to_cart();
        echo '</div>';
    }
}

if (! function_exists('zota_woocommerce_add_quantity_mode_list')) {
    function zota_woocommerce_add_quantity_mode_list()
    {
        if(zota_is_woo_variation_swatches_pro() || zota_woocommerce_quantity_mode_active()) {
            add_action('zota_woo_list_caption_right', 'zota_variation_swatches_pro_group_button', 20);
            add_action('zota_woo_list_caption_right', 'zota_woocommerce_quantity_mode_group_button', 20);
            remove_action('zota_woo_list_caption_right', 'woocommerce_template_loop_add_to_cart', 10);
        }
    }
}

if (! function_exists('zota_woocommerce_quantity_mode_remove_add_to_cart')) {
    function zota_woocommerce_quantity_mode_remove_add_to_cart()
    {
        if(zota_is_woo_variation_swatches_pro() || zota_woocommerce_quantity_mode_active()) {
            remove_action('zota_woocommerce_group_buttons', 'woocommerce_template_loop_add_to_cart', 40, 1);
        }
    }
    add_action('zota_tbay_after_shop_loop_item_title', 'zota_woocommerce_quantity_mode_remove_add_to_cart', 10);
}

if (! function_exists('zota_woocommerce_cart_item_name')) {
    function zota_woocommerce_cart_item_name($name, $cart_item, $cart_item_key)
    {
        if(!zota_tbay_get_config('show_checkout_image', false) || !is_checkout()) {
            return $name;
        }

        $_product       = $cart_item['data'];
        $thumbnail      = $_product->get_image();

        $output = $thumbnail;
        $output .= $name;

        return $output;
    }
    add_filter('woocommerce_cart_item_name', 'zota_woocommerce_cart_item_name', 10, 3);
}

if (! function_exists('zota_woocommerce_get_product_category')) {
    function zota_woocommerce_get_product_category()
    {
        global $product;
        echo wc_get_product_category_list($product->get_id(), ', ', '<span class="item-product-cate">', '</span>');
    }
}

if (!function_exists('zota_tbay_woocommerce_full_width_product_archives')) {
    function zota_tbay_woocommerce_full_width_product_archives($active)
    {

        $active = (isset($_GET['product_archive_layout'])) ? $_GET['product_archive_layout'] : zota_tbay_get_config('product_archive_layout', 'full-width');

        if($active === 'full-width') {
            $active = true;
        } else {
            $active = false;
        }

        return $active;
    }
    add_filter('zota_woo_width_product_archives', 'zota_tbay_woocommerce_full_width_product_archives');
}


if (!function_exists('zota_add_image_sizes_wvs')) {
    function zota_add_image_sizes_wvs($image_subsizes)
    {

        $item = 'woocommerce_thumbnail';
        $size = wc_get_image_size($item);

        $title  = ucwords(str_ireplace(array( '-', '_' ), ' ', $item));
        $width  = $size[ 'width' ];
        $height = $size[ 'height' ];

        $image_subsizes[ $item ] = sprintf('%s (%d &times; %d)', $title, $width, $height);

        return $image_subsizes;
    }

    add_filter('wvs_get_all_image_sizes', 'zota_add_image_sizes_wvs', 10, 1);
}


if (!function_exists('zota_get_mobile_form_cart_style')) {
    function zota_get_mobile_form_cart_style()
    {
        $ouput = (!empty(zota_tbay_get_config('mobile_form_cart_style', 'default'))) ? zota_tbay_get_config('mobile_form_cart_style', 'default') : 'default';

        return $ouput;
    }
}


if (!function_exists('zota_open_woocommerce_catalog_ordering')) {
    function zota_open_woocommerce_catalog_ordering()
    {
        echo '<div class="tbay-ordering"><span>'. esc_html__('Sort by:', 'zota') .'</span>';
    }
    add_action('woocommerce_before_shop_loop', 'zota_open_woocommerce_catalog_ordering', 29);
}

if (!function_exists('zota_close_woocommerce_catalog_ordering')) {
    function zota_close_woocommerce_catalog_ordering()
    {
        echo '</div>';
    }
    add_action('woocommerce_before_shop_loop', 'zota_close_woocommerce_catalog_ordering', 31);
}

if (!function_exists('zota_remove_add_to_cart_list_product')) {
    function zota_remove_add_to_cart_list_product()
    {
        remove_action('zota_woocommerce_group_buttons', 'woocommerce_template_loop_add_to_cart', 40, 1);
    }
    add_action('zota_woocommerce_before_shop_list_item', 'zota_remove_add_to_cart_list_product', 10);
}


if (! function_exists('zota_compatible_checkout_order')) {
    function zota_compatible_checkout_order()
    {
        $active = false;

        if(class_exists('WooCommerce_Germanized')) {
            $active = true;
        }

        if(function_exists('flux_fs')) {
            $active = true;
        }

        return $active;
    }
}

if (! function_exists('zota_change_woocommerce_button_proceed_to_checkout')) {
    function zota_change_woocommerce_button_proceed_to_checkout()
    {
        remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
        add_action('zota_woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 10);
    }
    add_action('woocommerce_before_cart', 'zota_change_woocommerce_button_proceed_to_checkout', 20);
}

if (! function_exists('zota_get_query_products')) {
    function zota_get_query_products($categories = array(), $cat_operator = '', $product_type = 'newest', $limit = '', $orderby = '', $order = '')
    {
        $atts = [
            'limit' => $limit,
            'orderby' => $orderby,
            'order' => $order
        ];

        if (!empty($categories)) {
            if (!is_array($categories)) {
                $atts['category'] = $categories;
            } else {
                $atts['category'] = implode(', ', $categories);
                $atts['cat_operator'] = $cat_operator;
            }
        }

        $type = 'products';

        $shortcode = new WC_Shortcode_Products($atts, $type);
        $args = $shortcode->get_query_args();

        $args = zota_get_attribute_query_product_type($args, $product_type);
        return new WP_Query($args);
    }
}

if (! function_exists('zota_get_attribute_query_product_type')) {
    function zota_get_attribute_query_product_type($args, $product_type)
    {
        global $woocommerce;

        switch ($product_type) {
            case 'best_selling':
                $args['meta_key']   = 'total_sales';
                $args['order']          = 'desc';
                $args['orderby']    = 'meta_value_num';
                $args['ignore_sticky_posts']   = 1;
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
                break;

            case 'featured':
                $args['ignore_sticky_posts']    = 1;
                $args['meta_query']             = array();
                $args['meta_query'][]           = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][]           = $woocommerce->query->visibility_meta_query();
                $args['tax_query'][]              = array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => 'featured',
                        'operator' => 'IN'
                    )
                );
                break;

            case 'top_rated':
                $args['meta_key']       = '_wc_average_rating';
                $args['orderby']        = 'meta_value_num';
                $args['order']          = 'desc';
                break;

            case 'newest':
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                break;

            case 'random_product':
                $args['orderby']    = 'rand';
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                break;

            case 'deals':
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                $args['post__in'] = $product_ids_on_sale;
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
                $args['meta_query'][] =  array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key'           => '_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                        array(
                            'key'           => '_min_variation_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                    ),
                    array(
                        'key'           => '_sale_price_dates_to',
                        'value'         => time(),
                        'compare'       => '>',
                        'type'          => 'numeric'
                    ),
                );
                break;

            case 'on_sale':
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                $args['post__in'] = $product_ids_on_sale;
                break;
        }

        if('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
            $args['meta_query'][] =  array(
                'relation' => 'AND',
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                )
            );
        }

        $product_visibility_term_ids = wc_get_product_visibility_term_ids();
        $args[ 'tax_query' ]         = isset($args[ 'tax_query' ]) ? $args[ 'tax_query' ] : array();
        $args[ 'tax_query' ][]       = array(
            'taxonomy' => 'product_visibility',
            'field'    => 'term_taxonomy_id',
            'terms'    => is_search() ? $product_visibility_term_ids[ 'exclude-from-search' ] : $product_visibility_term_ids[ 'exclude-from-catalog' ],
            'operator' => 'NOT IN',
        );

        return $args;
    }
}

/** Ajax Elementor Addon zota Product Tabs **/
if (! function_exists('zota_get_products_tab_ajax')) {
    function zota_get_products_tab_ajax()
    {
        if (! empty($_POST['atts'])) {

            $atts                   = zota_clean($_POST['atts']);
            $product_type           = zota_clean($_POST['value']);
            $atts['product_type']   = $product_type;

            $data = zota_elementor_products_ajax_template($atts);
            echo json_encode($data);
            die();
        }
    }
    add_action('wp_ajax_zota_get_products_tab_shortcode', 'zota_get_products_tab_ajax');
    add_action('wp_ajax_nopriv_zota_get_products_tab_shortcode', 'zota_get_products_tab_ajax');
}

/** Ajax Elementor Addon Product Categories Tabs **/
if (! function_exists('zota_get_products_categories_tab_shortcode')) {
    function zota_get_products_categories_tab_shortcode()
    {
        if (! empty($_POST['atts'])) {

            $atts               = zota_clean($_POST['atts']);
            $categories         = zota_clean($_POST['value']);
            $atts['categories'] = $categories;

            $data = zota_elementor_products_ajax_template($atts);
            echo json_encode($data);
            die();
        }
    }
    add_action('wp_ajax_zota_get_products_categories_tab_shortcode', 'zota_get_products_categories_tab_shortcode');
    add_action('wp_ajax_nopriv_zota_get_products_categories_tab_shortcode', 'zota_get_products_categories_tab_shortcode');
}

if (! function_exists('zota_elementor_products_ajax_template')) {
    function zota_elementor_products_ajax_template($settings)
    {

        extract($settings);

        $loop = zota_get_query_products($categories, $cat_operator, $product_type, $limit, $orderby, $order);

        if (preg_match('/\\\\/m', $attr_row)) {
            $attr_row = preg_replace('/\\\\/m', '', $attr_row);
        }
        ob_start();

        if($loop->have_posts()) :

            wc_get_template('layout-products/layout-products.php', array( 'loop' => $loop, 'product_style' => $product_style, 'attr_row' => $attr_row));

        endif;

        wc_reset_loop();
        wp_reset_postdata();

        return [
            'html' => ob_get_clean(),
        ];
    }
}

if (! function_exists('zota_is_variable_product_out_of_stock')) {
    function zota_is_variable_product_out_of_stock($product)
    {
        $children_count = 0; // initializing
        $variation_ids  = $product->get_visible_children();

        // Loop through children variations of the parent variable product
        foreach($variation_ids as $variation_id) {
            $variation = wc_get_product($variation_id); // Get the product variation Object

            if(! $variation->is_in_stock()) {
                $children_count++; // count out of stock children
            }
        }
        // Compare counts and return a boolean
        return count($variation_ids) == $children_count ? true : false;
    }
}

if (! function_exists('zota_woocommerce_single_title')) {
    function zota_woocommerce_single_title()
    {
        the_title('<h2 class="product_title entry-title">', '</h2>');
    }
}

/*Fix page search when product_cat emty WOOF  v3.3.4.3*/
if (! function_exists('zota_woo_fix_form_search_cate_empty_woof_new_version')) {
    add_action('admin_init', 'zota_woo_fix_form_search_cate_empty_woof_new_version', 10);
    function zota_woo_fix_form_search_cate_empty_woof_new_version()
    {
        $settings = get_option('woof_settings');

        $settings['force_ext_disable'] = 'url_request';

        update_option('woof_settings', $settings);
    }
}

if (!function_exists('zota_update_yith_wishlist_40')) {
    function zota_update_yith_wishlist_40()
    {
        update_option('yith_wcwl_add_to_wishlist_icon_type', 'default');
        update_option('yith_wcwl_added_to_wishlist_icon_type', 'default');
        update_option('yith_wcwl_add_to_wishlist_icon', 'heart-outline');
        update_option('yith_wcwl_added_to_wishlist_icon', 'heart-outline');
    }
}

if (!function_exists('zota_update_fix_new_plugin')) {
    add_action('after_setup_theme', 'zota_update_fix_new_plugin', 10);
    function zota_update_fix_new_plugin()
    {
        $current_theme_version = wp_get_theme()->get('Version');

        $stored_theme_version = get_option('zota_theme_version_fix_wishlist');

        if ($current_theme_version !== $stored_theme_version) {
            zota_update_yith_wishlist_40();

            update_option('zota_theme_version_fix_wishlist', $current_theme_version);
        }
    }
}

if (!function_exists('zota_get_wishlist_count')) {
    function zota_get_wishlist_count($expiration = 3600) {
        if (!class_exists('YITH_WCWL')) {
            return 0;
        }

        $cache_key = 'zota_wishlist_count_' . get_current_user_id();
        $cache_group = 'zota_wishlist';

        $wishlist_count = wp_cache_get($cache_key, $cache_group);

        if ($wishlist_count === false) {
            $wishlist_count = (defined('YITH_WCWL_VERSION') && version_compare(YITH_WCWL_VERSION, '4.0.0', '>='))
                ? yith_wcwl_count_products()
                : YITH_WCWL()->count_products();
            
            wp_cache_set($cache_key, $wishlist_count, $cache_group, $expiration);
        }

        return (int) $wishlist_count;
    }
}

if (!function_exists('zota_clear_wishlist_transient')) {
    function zota_clear_wishlist_transient() {
        $cache_key = 'zota_wishlist_count_' . get_current_user_id();
        $cache_group = 'zota_wishlist';
        wp_cache_delete($cache_key, $cache_group);
    }
}