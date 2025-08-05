<?php 
global $product;

?>
<div class="product-block list" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
	<?php 
		/**
		* Hook: zota_woocommerce_before_shop_list_item.
		*
		* @hooked zota_remove_add_to_cart_list_product - 10
		*/
		do_action( 'zota_woocommerce_before_shop_list_item' );
	?>
	<div class="product-content row">
		<div class="block-inner col-lg-3 col-4">
			<?php 
				/**
				* Hook: woocommerce_before_shop_loop_item.
				*/
				do_action( 'woocommerce_before_shop_loop_item' );
			?>
			<figure class="image">
				<a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" class="product-image">
					<?php
						/**
						* woocommerce_before_shop_loop_item_title hook
						*
						* @hooked woocommerce_show_product_loop_sale_flash - 10
						* @hooked woocommerce_template_loop_product_thumbnail - 10
						*/
						do_action( 'woocommerce_before_shop_loop_item_title' );
					?>
				</a>

				<?php 
					/**
					* zota_tbay_after_shop_loop_item_title hook
					*
					* @hooked zota_tbay_add_slider_image - 10
					*/
					do_action( 'zota_tbay_after_shop_loop_item_title' );
				?>
				<?php
				if( $product->is_on_sale() || $product->is_featured() ) {
					?>
					<div class="top-product-caption">
						<?php
							/**
							* tbay_woocommerce_before_content_product hook
							*
							* @hooked woocommerce_show_product_loop_sale_flash - 10
							*/
							do_action( 'tbay_woocommerce_before_content_product' );
						?>
					</div>
					<?php
				}
			?>
			</figure>
			
		</div>
		<div class="caption col-lg-9 col-8">
			<div class="caption-left">
			
				<?php 
					do_action('zota_woo_before_shop_list_caption');
				?>	

				<?php zota_the_product_name(); ?>
				
				<?php
					/**
					* zota_woo_list_caption_left hook
					*
					* @hooked woocommerce_template_loop_rating - 5
					*/
					do_action( 'zota_woo_list_caption_left');
				?>
				
                <?php
                    /**
                    * Hook: zota_shop_list_sort_description.
                    *
                    * @hooked woocommerce_template_single_excerpt - 5
                    */
                    do_action( 'zota_shop_list_sort_description' );
                ?>
				
				   <?php
					/**
					* zota_woo_list_after_short_description hook
					*
					* @hooked the_woocommerce_variable - 5
					* @hooked list_variable_swatches_pro - 5
					* @hooked zota_tbay_total_sales - 15
					*/
					do_action( 'zota_woo_list_after_short_description');
				?>
				
			</div>
			<div class="caption-right">
				<?php
					/**
					* zota_woo_list_caption_right hook
					*
					* @hooked woocommerce_template_loop_price - 5
					*/
					do_action( 'zota_woo_list_caption_right');
				?>
				<div class="group-buttons">	
					<?php 
						/**
						* zota_woocommerce_group_buttons hook
						*
						* @hooked zota_the_quick_view - 10
						* @hooked zota_the_yith_compare - 20
						* @hooked zota_the_yith_wishlist - 30
						*/
						do_action( 'zota_woocommerce_group_buttons', $product->get_id() );
					?>
				</div>

			</div>

			<?php 
				/**
				* Hook: woocommerce_after_shop_loop_item.
				*/
				do_action( 'woocommerce_after_shop_loop_item' );
			?>

		</div>
		
	</div>
	<?php 
		/**
		* Hook: zota_woocommerce_after_shop_list_item.
		*
		*/
		do_action( 'zota_woocommerce_after_shop_list_item' );
	?>
</div>


