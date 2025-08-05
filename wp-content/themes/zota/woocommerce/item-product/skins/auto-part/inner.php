<?php 
global $product;

$size_image    = isset($size_image) ? $size_image : apply_filters( 'zota_woocommerce_thumbnail_product_grid', 'woocommerce_thumbnail' );
$flash_sales 	= isset($flash_sales) ? $flash_sales : false;
$end_date 		= isset($end_date) ? $end_date : '';

$countdown_title 		= isset($countdown_title) ? $countdown_title : '';

$countdown 		= isset($countdown) ? $countdown : false;
$class = array();
$class_flash_sale = zota_tbay_class_flash_sale($flash_sales);
array_push($class, $class_flash_sale);


?>
<div <?php zota_tbay_product_class($class); ?> data-product-id="<?php echo esc_attr($product->get_id()); ?>">
	<?php 
		do_action( 'zota_woocommerce_before_product_block_grid' );

		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 */
		do_action( 'woocommerce_before_shop_loop_item' );
	?>

	<div class="product-content">
		<?php zota_woo_product_time_countdown($countdown, $countdown_title); ?>
		<div class="block-inner">
			<figure class="image">
				<a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" class="product-image">
					<?php
						/**
						* woocommerce_before_shop_loop_item_title hook
						*
						* @hooked woocommerce_template_loop_product_thumbnail - 10
						*/
						do_action( 'woocommerce_before_shop_loop_item_title', $size_image, false );
					?>
				</a>
				
				<?php 
					/**
					* zota_tbay_after_shop_loop_item_title hook
					*
					* @hooked zota_tbay_add_slider_image - 10
					*/
					do_action( 'zota_tbay_after_shop_loop_item_title', $size_image );
				?>
			
			<?php zota_tbay_item_deal_ended_flash_sale($flash_sales, $end_date); ?>
			</figure>
			<div class="group-buttons">	
				<?php 
					/**
					* zota_woocommerce_group_buttons hook
					*
					* @hooked zota_the_quick_view - 10
					* @hooked zota_the_yith_compare - 20
					* @hooked zota_the_yith_wishlist - 30
					* @hooked woocommerce_template_loop_add_to_cart - 40
					*/
					do_action( 'zota_woocommerce_group_buttons', $product->get_id() );
				?>
		    </div>
		</div>
		<?php zota_tbay_stock_flash_sale($flash_sales); ?>
		<?php
			/**
			* tbay_woocommerce_before_content_product hook
			*
			* @hooked woocommerce_show_product_loop_sale_flash - 10
			*/
			do_action( 'tbay_woocommerce_before_content_product' );
		?>
		
		
		
		<div class="caption">
			<?php
				/**
				* zota_woocommerce_loop_item_rating hook
				*
				* @hooked woocommerce_template_loop_rating - 15
				*/
				do_action( 'zota_woocommerce_loop_item_rating');
			?>
			<?php 
				do_action('zota_woo_before_shop_loop_item_caption');
			?>

			<?php zota_the_product_name(); ?>

			<?php
				/**
				* woocommerce_after_shop_loop_item_title hook
				*
				* @hooked woocommerce_template_loop_price - 10
				*/
				do_action( 'woocommerce_after_shop_loop_item_title');
			?>

			<?php
				do_action('zota_tbay_variable_product');
			?>	
			
			<?php 
				do_action('zota_woo_after_shop_loop_item_caption');
			?>
		</div>

		
    </div>

	<?php 
		/**
		* Hook: woocommerce_after_shop_loop_item.
		*/
		do_action( 'woocommerce_after_shop_loop_item' );

		do_action( 'zota_woocommerce_after_product_block_grid' );
	?>
    
</div>