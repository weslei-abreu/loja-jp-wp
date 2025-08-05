<?php

if( !zota_is_Woocommerce_activated() ) return;


if ( ! function_exists( 'zota_tbay_recently_viewed_set_cookie_meta' ) ) {
    function zota_tbay_recently_viewed_set_cookie_meta($products_list) {
        $user_id            = get_current_user_id();
        $meta_products_list = 'zota_recently_viewed_product_list';
        $cookie_name        = 'zota_recently_viewed_products_list';

        // if user also exists add meta with products list
        if( $user_id ) {
            update_user_meta( $user_id, $meta_products_list, $products_list );
        } else {
            // Store for session only.
            wc_setcookie( $cookie_name, implode( '|', $products_list ) );
        }
    }
}

if ( ! function_exists( 'zota_tbay_wc_track_user_get_cookie' ) ) {

    function zota_tbay_wc_track_user_get_cookie() {
        $user_id            = get_current_user_id();
        $cookie_name        = 'zota_recently_viewed_products_list';
        $meta_products_list = 'zota_recently_viewed_product_list';

        if( ! $user_id ) {
            if ( empty( $_COOKIE[ $cookie_name ] ) ) { // @codingStandardsIgnoreLine.
                $products_list = array();
            } else {
                $products_list = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[ $cookie_name ] ) ) );
            }
        }
        else {
            $meta = get_user_meta( $user_id, $meta_products_list, true );
            $products_list = ! empty( $meta ) ? $meta : array();
        }

        return $products_list;

    }

}

if ( ! function_exists( 'zota_tbay_wc_track_user_viewed_produts' ) ) {
    function zota_tbay_wc_track_user_viewed_produts() {
        if ( ! is_singular( 'product' ) ) {
            return;
        }

        global $post;

        $products_list      = zota_tbay_wc_track_user_get_cookie();

        // Unset if already in viewed products list.
        $keys = array_flip( $products_list );

        if ( isset( $keys[ $post->ID ] ) ) {
            unset( $products_list[ $keys[ $post->ID ] ] );
        }

        $products_list[] = $post->ID;

        // set cookie and save meta
        zota_tbay_recently_viewed_set_cookie_meta($products_list);
    }
    add_action( 'template_redirect', 'zota_tbay_wc_track_user_viewed_produts', 99 );
    add_action( 'init', 'zota_tbay_wc_track_user_viewed_produts', 99 ); 
}

if ( ! function_exists( 'zota_tbay_get_products_recently_viewed' ) ) {
    function zota_tbay_get_products_recently_viewed($number_post = 8) {
        $products_list      = zota_tbay_wc_track_user_get_cookie();

        if (empty($products_list)) {
            return '';
        }

        $products_list_value    = array_reverse(array_values($products_list));

        if ($number_post  !== -1 && count($products_list_value) > $number_post) {
            $products_list_value    = array_slice($products_list_value, 0, $number_post);
        }

        $type = 'products';
 
        $atts['ids'] = implode(',', $products_list_value);

        $shortcode = new WC_Shortcode_Products($atts, $type);

        $query = $shortcode->get_query_args();

        $query['orderby'] = 'post__in';
        $query['post__in'] = $products_list_value; 

        return $query;
    }
}

/*The list product recently viewed*/
if ( ! function_exists( 'zota_tbay_wc_get_recently_viewed' ) ) {
    function zota_tbay_wc_get_recently_viewed() {
            $num_post           =   zota_tbay_get_config('max_products_recentview', 8);
            
            $args = zota_tbay_get_products_recently_viewed($num_post);
            $args = apply_filters( 'zota_list_recently_viewed_products_args', $args );


            $products = new WP_Query( $args );

            ob_start();

            ?>
                <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                    <?php wc_get_template_part( 'content', 'recent-viewed' ); ?>

                <?php endwhile; // end of the loop. ?>

            <?php

            $content = ob_get_clean();

            wp_reset_postdata();

            return $content;
    }
}
