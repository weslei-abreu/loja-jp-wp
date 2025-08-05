<?php
/**
 * Follow Store Vendor Email template.
 *
 * An email sent to the vendor about store follower update.
 *
 * @class       Dokan_Follow_Store_Vendor_Email
 * @version     4.0.0
 */
defined( 'ABSPATH') || exit;

require_once ABSPATH . WPINC . '/formatting.php';

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<div id="dokan-follow-store">
    <h1 id="dokan-follow-store-title">
        <?php
        if ( 'following' === $data['status'] ) {
            esc_html_e( 'You have got a new store follower!', 'dokan' );
        } else {
            esc_html_e( 'Someone just unfollowed your store!', 'dokan' );
        }
        ?>
    </h1>

    <p class="status">
        <?php
        if ( 'following' === $data['status'] ) {
            // translators: Follower Name.
            printf( __( '%s has just followed your store.', 'dokan' ), $data['follower']->display_name );
        } else {
            // translators: Follower Name.
            printf( __( '%s has just unfollowed your store.', 'dokan' ), $data['follower']->display_name );
        }
        ?>
    </p>

</div>

<style type="text/css">
    .status {
        font-size: 18px;
        margin-top: 30px !important;
    }
</style>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
