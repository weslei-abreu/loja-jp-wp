<?php 
global $product;
$size_image    = isset($size_image) ? $size_image : apply_filters( 'zota_woocommerce_thumbnail_product_vertical', 'woocommerce_thumbnail' );
$product_style = isset($product_style) ? $product_style : '';

?>
<div class="product-block product <?php echo esc_attr($product_style); ?> <?php zota_is_product_variable_sale(); ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
	<div class="product-content">
		<?php 
			/**
			* Hook: woocommerce_before_shop_loop_item.
			*
			* @hooked woocommerce_template_loop_product_link_open - 10
			*/
			do_action( 'woocommerce_before_shop_loop_item' );
		?>

		<div class="block-inner">
			<figure class="image">
				<a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" class="product-image">
					<?php
						/**
						* woocommerce_before_shop_loop_item_title hook
						*
						* @hooked woocommerce_template_loop_product_thumbnail - 10
						*/
						do_action( 'woocommerce_before_shop_loop_item_title', $size_image, true );
					?>
				</a>
				<?php zota_tbay_item_deal_ended_flash_sale($flash_sales, $end_date); ?>
				<?php 
					/**
					* Hook: tbay_woocommerce_loop_sale_vertical.
					*
					* @hooked woocommerce_show_product_loop_sale_flash - 10
					* @hooked only_feature_product_label - 10
					*/
					do_action( 'tbay_woocommerce_loop_sale_vertical' );
				?>
			</figure>
		</div>
		<div class="caption">
			<?php do_action( 'woocommerce_after_shop_loop_item_vertical_price'); ?>
			<?php zota_the_product_name(); ?>


			<?php do_action( 'woocommerce_after_shop_loop_item_vertical_title'); ?>


			<?php 
				/**
				* Hook: woocommerce_after_shop_loop_item.
				*
				* @hooked woocommerce_template_loop_product_link_close - 5
				* @hooked woocommerce_template_loop_add_to_cart - 10
				*/
				do_action( 'woocommerce_after_shop_loop_item' );
			?>
		
		</div>
    </div>
</div>
