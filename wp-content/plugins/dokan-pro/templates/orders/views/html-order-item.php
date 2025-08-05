<?php
/**
 * Shows an order item
 *
 * @var \WC_Order $order Order object
 * @var \WC_Product $_product Product object
 * @var \WC_Order_Item $item The item being displayed
 * @var int $item_id The id of the item being displayed
 * @var false|\WC_Order_Item[]|\WC_Order_Item_Tax[] $order_taxes The id of the item being displayed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @var false|null|WC_Product $_product Product.
 */

$product_url = '';
if ( $_product && 'trash' !== $_product->get_status() ) {
    $product_url = current_user_can( 'dokan_edit_product' ) ? dokan_edit_product_url( absint( dokan_get_prop( $_product, 'id' ) ) ) : $_product->get_permalink();
}
?>
<tr class="item <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item ); ?>" data-order_item_id="<?php echo $item_id; ?>">
	<!-- <td class="check-column"><input type="checkbox" /></td> -->
	<td class="thumb">
		<?php if ( $_product && 'trash' !== $_product->get_status() ) : ?>
            <?php
            $tip_content = '<strong>' . __( 'Product ID:', 'dokan' ) . '</strong> ' . absint( $item['product_id'] );
            if ( $item['variation_id'] && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
                $tip_content .= '<br/><strong>' . __( 'Variation ID:', 'dokan' ) . '</strong> ' . absint( $item['variation_id'] );
            } elseif ( $item['variation_id'] ) {
                $tip_content .= '<br/><strong>' . __( 'Variation ID:', 'dokan' ) . '</strong> ' . absint( $item['variation_id'] ) . ' (' . __( 'No longer exists', 'dokan' ) . ')';
            }

            if ( $_product && $_product->get_sku() ) {
                $tip_content .= '<br/><strong>' . __( 'Product SKU:', 'dokan' ) . '</strong> ' . esc_html( $_product->get_sku() );
            }

            if ( $_product->is_type( 'variation' ) ) {
                $tip_content .= '<br/>' . wc_get_formatted_variation( $_product, true );
            }
            ?>
			<a href="<?php echo esc_url( $product_url ); ?>" class="tips" data-tip="<?php echo wp_kses_post( $tip_content ); ?>">
                <?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?>
            </a>
        <?php elseif ( $_product && 'trash' === $_product->get_status() ) : ?>
            <?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => $_product->get_title() ) ); ?>
		<?php else : ?>
			<?php echo wc_placeholder_img( 'shop_thumbnail' ); ?>
		<?php endif; ?>
	</td>
	<td class="name" data-sort-value="<?php echo esc_attr( $item['name'] ); ?>">

		<?php echo ( $_product && $_product->get_sku() ) ? esc_html( $_product->get_sku() ) . ' &ndash; ' : ''; ?>

		<?php if ( $_product && 'trash' !== $_product->get_status() ) : ?>
			<a target="_blank" href="<?php echo esc_url( $product_url ); ?>">
				<?php echo esc_html( $item['name'] ); ?>
			</a>
		<?php else : ?>
			<?php echo esc_html( $item['name'] ); ?>
		<?php endif; ?>

		<input type="hidden" class="order_item_id" name="order_item_id[]" value="<?php echo esc_attr( $item_id ); ?>" />
		<input type="hidden" name="order_item_tax_class[<?php echo absint( $item_id ); ?>]" value="<?php echo isset( $item['tax_class'] ) ? esc_attr( $item['tax_class'] ) : ''; ?>" />
        <?php $variation_meta = $item->get_formatted_meta_data(); ?>
			<?php if ( ! empty( $variation_meta ) ) : ?>
                <?php foreach ( $variation_meta as $meta_id => $meta ) : ?>
                    <p class="order-product-variation" style="color: gray">
                        <span style="color: #444"><?php echo $meta->display_key . ':'; ?></span>
                        <?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>

		<?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, $_product ); ?>

		<div class="view">
			<?php
				global $wpdb;

                $metadata = dokan_get_metadata( $order, $item_id );

			if ( ! empty( $metadata ) ) {
				echo '<table cellspacing="0" class="display_meta">';
				foreach ( $metadata as $order_meta ) {

                    /**
                     * Filters the list of hidden order item meta keys.
                     *
                     * @since 3.0.0
                     * @param array $hidden_order_item_metas Array of hidden meta keys.
                     */
                    $hidden_order_item_metas = apply_filters(
                        'woocommerce_hidden_order_itemmeta', array(
                            '_qty',
                            '_tax_class',
                            '_product_id',
                            '_variation_id',
                            '_line_subtotal',
                            '_line_subtotal_tax',
                            '_line_total',
                            '_line_tax',
                        )
                    );

                    // Skip hidden core fields
                    if ( in_array( $order_meta['meta_key'], $hidden_order_item_metas, true ) ) {
                        continue;
                    }

					// Skip serialised meta
					if ( is_serialized( $order_meta['meta_value'] ) ) {
						continue;
					}

					// Get attribute data
					if ( taxonomy_exists( wc_sanitize_taxonomy_name( $order_meta['meta_key'] ) ) ) {
						$order_term               = get_term_by( 'slug', $order_meta['meta_value'], wc_sanitize_taxonomy_name( $order_meta['meta_key'] ) );
						$order_meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $order_meta['meta_key'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						$order_meta['meta_value'] = isset( $order_term->name ) ? $order_term->name : $order_meta['meta_value']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					} else {
						$order_meta['meta_key'] = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $order_meta['meta_key'], $_product ), $order_meta['meta_key'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					}

					echo '<tr><th>' . wp_kses_post( rawurldecode( $order_meta['meta_key'] ) ) . ':</th><td>' . wp_kses_post( wpautop( make_clickable( rawurldecode( $order_meta['meta_value'] ) ) ) ) . '</td></tr>';
				}
				echo '</table>';
			}
			?>
		</div>
		<div class="edit" style="display: none;">
			<table class="meta dokan-table dokan-table-strip" cellspacing="0">
				<tbody class="meta_items">
				<?php
                $metadata = dokan_get_metadata( $order, $item_id );
				if ( ! empty( $metadata ) ) {
					foreach ( $metadata as $meta ) {

                        /**
                         * Filters the list of hidden order item meta keys.
                         *
                         * @since 3.0.0
                         * @param array $hidden_order_item_metas Array of hidden meta keys.
                         */
                        $hidden_order_item_metas = apply_filters(
                            'woocommerce_hidden_order_itemmeta', array(
                                '_qty',
                                '_tax_class',
                                '_product_id',
                                '_variation_id',
                                '_line_subtotal',
                                '_line_subtotal_tax',
                                '_line_total',
                                '_line_tax',
                            )
                        );

						// Skip hidden core fields
						if ( in_array( $meta['meta_key'], $hidden_order_item_metas, true ) ) {
							continue;
						}

						// Skip serialised meta
						if ( is_serialized( $meta['meta_value'] ) ) {
							continue;
						}

						$meta['meta_key']   = rawurldecode( $meta['meta_key'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						$meta['meta_value'] = esc_textarea( rawurldecode( $meta['meta_value'] ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						$meta['meta_id']    = absint( $meta['meta_id'] );

						echo '<tr data-meta_id="' . esc_attr( $meta['meta_id'] ) . '">
								<td>
									<input type="text" name="meta_key[' . $meta['meta_id'] . ']" value="' . esc_attr( $meta['meta_key'] ) . '" />
									<textarea name="meta_value[' . $meta['meta_id'] . ']">' . $meta['meta_value'] . '</textarea>
								</td>
								<td width="1%"><button class="remove_order_item_meta button">&times;</button></td>
							</tr>';
					}
				}
				?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4"><button class="add_order_item_meta button"><?php esc_html_e( 'Add&nbsp;meta', 'dokan' ); ?></button></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<?php do_action( 'woocommerce_after_order_itemmeta', $item_id, $item, $_product ); ?>

	</td>

	<?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>

	<td class="item_cost" width="1%" data-sort-value="<?php echo esc_attr( $order->get_item_subtotal( $item, false, true ) ); ?>">
		<div class="view">
			<?php
			if ( isset( $item['line_total'] ) ) {
				if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] !== $item['line_total'] ) {
					echo '<del>' . wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) ) . '</del> ';
				}
				echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) );
			}
			?>
		</div>
	</td>

	<td class="quantity" width="1%">
		<div class="view">
			<?php
				echo ( isset( $item['qty'] ) ) ? esc_html( $item['qty'] ) : '';

                $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

			if ( 0 !== $refunded_qty ) {
				echo ' <small class="refunded">-' . $refunded_qty . '</small>';
			}
			?>
		</div>
		<div class="edit" style="display: none;">
			<?php $item_qty = esc_attr( $item['qty'] ); ?>
			<input style="width:60px" type="number" step="<?php echo apply_filters( 'woocommerce_quantity_input_step', '1', $_product ); ?>" min="0" autocomplete="off" name="order_item_qty[<?php echo absint( $item_id ); ?>]" placeholder="0" value="<?php echo $item_qty; ?>" data-qty="<?php echo $item_qty; ?>" class="quantity" />
 		</div>
        <?php if ( $item_qty > absint( $refunded_qty ) ) : ?>
		<div class="refund" style="display: none;">
			<input style="width:60px" type="number" step="<?php echo apply_filters( 'woocommerce_quantity_input_step', '1', $_product ); ?>" min="0" max="<?php echo $item['qty']; ?>" autocomplete="off" name="refund_order_item_qty[<?php echo absint( $item_id ); ?>]" placeholder="0" size="4" class="refund_order_item_qty" />
		</div>
        <?php endif; ?>
	</td>

	<td class="line_cost" width="1%" data-sort-value="<?php echo esc_attr( isset( $item['line_total'] ) ? $item['line_total'] : '' ); ?>">
		<div class="view">
			<?php
			if ( isset( $item['line_total'] ) ) {
				if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] !== $item['line_total'] ) {
					echo '<del>' . wc_price( $item['line_subtotal'], array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) ) . '</del> ';
				}
				echo wc_price( $item['line_total'], array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) );
			}

            $refunded = $order->get_total_refunded_for_item( $item_id );
			if ( $refunded ) {
				echo ' <small class="refunded">-' . wc_price( $refunded, array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) ) . '</small>';
			}
			?>
		</div>
		<div class="edit" style="display: none;">
			<div class="split-input">
				<?php $item_total = ( isset( $item['line_total'] ) ) ? esc_attr( wc_format_localized_price( $item['line_total'] ) ) : ''; ?>
				<input style="width:60px" type="text" name="line_total[<?php echo absint( $item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" value="<?php echo $item_total; ?>" class="line_total wc_input_price tips" data-tip="<?php esc_html_e( 'After pre-tax discounts.', 'dokan' ); ?>" data-total="<?php echo $item_total; ?>" />

				<?php $item_subtotal = ( isset( $item['line_subtotal'] ) ) ? esc_attr( wc_format_localized_price( $item['line_subtotal'] ) ) : ''; ?>
				<input style="width:60px" type="text" name="line_subtotal[<?php echo absint( $item_id ); ?>]" value="<?php echo $item_subtotal; ?>" class="line_subtotal wc_input_price tips" data-tip="<?php esc_html_e( 'Before pre-tax discounts.', 'dokan' ); ?>" data-subtotal="<?php echo $item_subtotal; ?>" />
			</div>
		</div>
        <?php if ( $item_total > $refunded ) : ?>
		<div class="refund" style="display: none;">
			<input style="width:60px" type="text" name="refund_line_total[<?php echo absint( $item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" class="refund_line_total wc_input_price" />
		</div>
        <?php endif; ?>
	</td>

	<?php
	if ( empty( $legacy_order ) && wc_tax_enabled() ) :
		$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
		$tax_data      = maybe_unserialize( $line_tax_data );

		foreach ( $order_taxes as $tax_item ) :
			$tax_item_id       = $tax_item['rate_id'];
			$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
			$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';

			?>
					<td class="line_tax" width="1%">
						<div class="view">
						<?php
						if ( '' !== $tax_item_total ) {
							if ( isset( $tax_item_subtotal ) && $tax_item_subtotal !== $tax_item_total ) {
								echo '<del>' . wc_price( wc_round_tax_total( $tax_item_subtotal ), array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) ) . '</del> ';
							}

							echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) );
						} else {
							echo '&ndash;';
						}

                        $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id );
						if ( $refunded ) {
							echo ' <small class="refunded">-' . wc_price( $refunded, array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) ) . '</small>';
						}
						?>
						</div>
						<div class="edit" style="display: none;">
							<div class="split-input">
							<?php $item_total_tax = ( isset( $tax_item_total ) ) ? esc_attr( wc_format_localized_price( $tax_item_total ) ) : ''; ?>
								<input style="width:60px" type="text" name="line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" value="<?php echo $item_total_tax; ?>" class="line_tax wc_input_price tips" data-tip="<?php esc_html_e( 'After pre-tax discounts.', 'dokan' ); ?>" data-total_tax="<?php echo $item_total_tax; ?>" />

							<?php $item_subtotal_tax = ( isset( $tax_item_subtotal ) ) ? esc_attr( wc_format_localized_price( $tax_item_subtotal ) ) : ''; ?>
								<input style="width:60px" type="text" name="line_subtotal_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]" value="<?php echo $item_subtotal_tax; ?>" class="line_subtotal_tax wc_input_price tips" data-tip="<?php esc_html_e( 'Before pre-tax discounts.', 'dokan' ); ?>" data-subtotal_tax="<?php echo $item_subtotal_tax; ?>" />
							</div>
						</div>
                        <?php if ( $tax_item_total > $refunded ) : ?>
						<div class="refund" style="display: none;">
							<input style="width:60px" type="text" name="refund_line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" class="refund_line_tax wc_input_price" data-tax_id="<?php echo esc_attr( $tax_item_id ); ?>" />
						</div>
                        <?php endif; ?>
					</td>
				<?php
			endforeach;
		endif;
	?>

    <?php if ( $order->is_editable() ) : ?>
        <td class="wc-order-edit-line-item">
            <div class="wc-order-edit-line-item-actions">
                <a class="edit-order-item" href="#"></a><a class="delete-order-item" href="#"></a>
            </div>
        </td>
    <?php endif; ?>
</tr>
