<?php
/**
 * Follow Store Update Email plain template.
 *
 * An email sent to the customer about followed store update.
 *
 * @class       Dokan_Follow_Store_Email
 * @version     4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

esc_html_e( 'Updates from your favorite stores', 'dokan' );
echo " \n\n";

foreach ( $data['vendors'] as $vendor ) :
    if ( $vendor->products->have_posts() || ! empty( $vendor->coupons ) ) :
        echo " \n\n";
        echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
        // translators: Vendor Store Name.
        echo esc_html( wptexturize( sprintf( __( 'Vendor: %s', 'dokan' ), $vendor->get_shop_name() ) ) );
        echo esc_url( $vendor->get_shop_url() );
        echo " \n\n";

        if ( $vendor->products->have_posts() ) :
            $products = $vendor->products->posts;
            esc_html_e( 'New Products', 'dokan' );
            echo " \n\n";
            echo "\n-------------------------------------------------------\n";
            foreach ( $products as $i => $product ) :
                $product = wc_get_product( $product );
                echo esc_html( wp_trim_words( $product->get_name(), 4, '...' ) );
                echo " \n";
                echo esc_url( $product->get_permalink() );
                echo " \n\n";

            endforeach;
            esc_html_e( 'See all', 'dokan' );
            echo " \n";
            echo esc_url( $vendor->get_shop_url() );
            echo "\n-------------------------------------------------------\n";
            echo " \n\n";
        endif;

        if ( ! empty( $vendor->coupons ) ) :
            $coupons = $vendor->coupons;
            $coupons_count = count( $coupons );
            echo "\n-------------------------------------------------------\n";
            esc_html_e( 'Coupons', 'dokan' );
            echo "\n-------------------------------------------------------\n";
            echo " \n\n";

            for ( $i = 0; $i < $coupons_count; $i++ ) :
                $coupon = $coupons[ $i ];
                echo esc_html( $coupon->get_code() );
                echo " \n\n";
            endfor;
        endif;
    endif;
endforeach;

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
    echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
    echo "\n\n----------------------------------------\n\n";
}

echo esc_html( wp_strip_all_tags( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) );
