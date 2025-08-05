<?php
/**
 * New Support Ticket email
 *
 * This template can be overridden by copying it to yourtheme/dokan/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  Dokan
 *
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );

$store_url         = dokan_get_store_url( $store_id );
$store_support_url = admin_url( 'admin.php?page=dokan' ) . '#/admin-store-support?page_type=single&topic=' . $topic_id . '&vendor_id=' . $store_id;

?>

<div style="margin-bottom: 40px;">
    <?php esc_html_e( 'Hi,', 'dokan' ); ?>

	<p><?php esc_html_e( 'A new support ticket is created in store: ', 'dokan' ); ?> <a href="<?php echo $store_url ? esc_url( $store_url ) : ''; ?>" target="_blank"><?php echo esc_html( $store_info['store_name'] ); ?></a></p>

	<p><?php esc_html_e( 'Ticket URL :', 'dokan' ); ?> <a href="<?php echo esc_url( $store_support_url ); ?>"><?php echo esc_url( $store_support_url ); ?></a></p>

	---
	<p><?php esc_html_e( 'From', 'dokan' ); ?> <?php echo esc_html( $store_info['store_name'] ); ?></p>
	<p><?php echo esc_url( home_url() ); ?></p>
</div>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
