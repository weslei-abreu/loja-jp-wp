<?php
/**
 * Template for shortcode
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( is_user_logged_in() ) {
	$user_id = get_current_user_id();
	if ( $useractivity ) { ?>
		<table class="my_auctions_activity">
		<thead>
			<th><?php esc_html_e('Date', 'wc_simple_auctions'); ?></th>
			<th><?php esc_html_e('Auction', 'wc_simple_auctions'); ?></th>
			<th><?php esc_html_e('Bid', 'wc_simple_auctions'); ?></th>
			<th><?php esc_html_e('Status', 'wc_simple_auctions'); ?></th>
		</thead>
		<?php
		foreach ( $useractivity as $key => $value ) {
			if ( get_post_status ($value->auction_id ) == 'publish' ) {
				$class = '';
				$product = wc_get_product($value->auction_id);

			if ($product && method_exists( $product, 'get_type') && $product->get_type() == 'auction') {
				if ($product->is_closed()) {
					$class .='closed ';
				}

				if ($product->get_auction_current_bider() == $user_id && !$product->is_sealed()) {
					$class .='winning ';
				}

				if ($product->get_auction_current_bider() == $user_id && !$product->is_reserve_met()) {
					$class .='reserved ';
				}

				if ( strtotime( $product->get_auction_relisted() ) > strtotime( $value->date ) ) {
					$class .='relisted ';
				}
				?>
				<tr class="<?php echo $class; ?>">
					<td><?php echo $value->date; ?></td>
					<td>
						<?php if ( get_permalink( $value->auction_id ) ) { ?>
							<a href="<?php echo get_permalink( $value->auction_id ); ?>">
								<?php echo get_the_title( $value->auction_id ); ?>
							</a>
						<?php } else { ?>
							<?php echo get_the_title( $value->auction_id ); ?>
						<?php }?>
					</td>
					<td><?php echo wc_price($value->bid); ?></td>
					<td><?php echo $product->get_price_html(); ?></td>
				</tr>
				<?php }
			}
		} ?>
		</table>
		<?php
			wc_get_template( 'loop/pagination.php', $pagination_args );
		?>
	<?php }	else { ?>
		<div class="woocommerce"><p class="woocommerce-info">'<?php esc_html_e('There is no auction activity.','wc_simple_auctions' ) ?></p></div>
	<?php } ?>
<?php } else { ?>
<div class="woocommerce"><p class="woocommerce-info">'<?php esc_html_e('Please log in to see your auctions activity.','wc_simple_auctions' ) ?></p></div>
<?php } ?>
