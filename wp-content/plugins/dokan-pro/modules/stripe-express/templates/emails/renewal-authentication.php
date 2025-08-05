<?php
    defined( 'ABSPATH' ) || exit; // Exit if called directly
    do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>
    <?php
    // translators: 1) name of the blog, 2) link to payment re-authentication URL, note: no full stop due to url at the end
    echo wp_kses( sprintf( _x( 'The automatic payment to renew your subscription with %1$s has failed. To reactivate the subscription, please login and authorize the renewal from your account page: %2$s', 'In failed renewal authentication email', 'dokan' ), esc_html( get_bloginfo( 'name' ) ), '<a href="' . esc_url( $authorization_url ) . '">' . esc_html__( 'Authorize the payment &raquo;', 'dokan' ) . '</a>' ), [ 'a' => [ 'href' => true ] ] );
    ?>
</p>

<?php do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}
do_action( 'woocommerce_email_footer', $email );
