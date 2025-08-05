<?php if ( zota_tbay_get_config('show_searchform') ): ?>

	<?php 
		$_id = zota_tbay_random_key();

		$autocomplete_search 	=  zota_tbay_get_config('autocomplete_search', true);

		$enable_image 	=  zota_tbay_get_config('show_search_product_image', true);
		$enable_price 	=  zota_tbay_get_config('show_search_product_price', true);
		$search_type 	=  zota_tbay_get_config('search_type', 'product');
		$number 		=  zota_tbay_get_config('search_max_number_results', 5);
		$minchars 		=  zota_tbay_get_config('search_min_chars', 3);

		$search_text_categories 		=  zota_tbay_get_config('search_text_categories', esc_html__('All categories', 'zota'));
		$search_placeholder 			=  zota_tbay_get_config('search_placeholder', esc_html__('I&rsquo;m shopping for...', 'zota'));
		$button_search 				=  zota_tbay_get_config('button_search', 'all');
		$button_search_text 		=  zota_tbay_get_config('button_search_text', 'Search');
		$button_search_icon 		=  zota_tbay_get_config('button_search_icon', 'tb-icon tb-icon-magnifier');


		$show_count 					= zota_tbay_get_config('search_count_categories',  false);

		$class_active_ajax = ( $autocomplete_search ) ? 'zota-ajax-search' : '';
	?>

	<?php $_id = zota_tbay_random_key(); ?>
	<div id="tbay-search-form-canvas" class="tbay-search-form">
		<button type="button" class="btn-search-icon search-open <?php echo esc_attr($button_search); ?>">
			<?php if(isset($button_search) && $button_search != 'icon' ) : ?>
			<span class="text"><?php echo trim($button_search_text); ?></span>
			<?php endif; ?>
			<?php if(isset($button_search) && $button_search != 'text' ) : ?>
				<i class="<?php echo esc_attr($button_search_icon); ?>"></i>
			<?php endif; ?>
		</button>
		<div class="sidebar-canvas-search">

			<div class="sidebar-content">

				<button type="button" class="btn-search-close">
					<?php esc_html_e( 'Close', 'zota' ) ?>	<i class="tb-icon tb-icon-close-01"></i>
				</button>

				<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="searchform <?php echo esc_attr( $class_active_ajax ); ?>" data-thumbnail="<?php echo esc_attr( $enable_image ); ?>" data-appendto=".search-results-<?php echo esc_attr( $_id ); ?>" data-price="<?php echo esc_attr( $enable_price ); ?>" data-minChars="<?php echo esc_attr( $minchars ) ?>" data-post-type="<?php echo esc_attr( $search_type ) ?>" data-count="<?php echo esc_attr( $number ); ?>">
					<div class="form-group">
						<div class="input-group">

							<input data-style="right" type="text" placeholder="<?php echo esc_attr($search_placeholder); ?>" name="s" required oninvalid="this.setCustomValidity('<?php esc_attr_e('Enter at least 2 characters', 'zota'); ?>')" oninput="setCustomValidity('')" class="tbay-search form-control input-sm"/>

							<div class="search-results-wrapper">
								<div class="zota-search-results search-results-<?php echo esc_attr( $_id ); ?>" data-ajaxsearch="<?php echo esc_attr( $autocomplete_search ) ?>" data-price="<?php echo esc_attr( $enable_price ); ?>"></div>
							</div>
							<div class="button-group input-group-addon">
								<button type="submit" class="button-search btn btn-sm icon">
									<i class="<?php echo esc_attr($button_search_icon); ?>"></i>
								</button>
							</div>

							<?php if ( zota_tbay_get_config('search_category') ): ?>
								<?php 
									wp_enqueue_style('sumoselect');
									wp_enqueue_script('jquery-sumoselect');	
								?>


								<div class="select-category input-group-addon">

									<span class="category-title"><?php esc_html_e( 'Search in:', 'zota' ) ?></span>
									
									<?php if ( class_exists( 'WooCommerce' ) && zota_tbay_get_config('search_type') == 'product' ) :
										$args = array(
											'show_option_none'   => $search_text_categories,
										    'show_count' => $show_count,
										    'hierarchical' => true,
										    'id' => 'product-cat-'.$_id,
										    'show_uncategorized' => 0
										);
									?> 
									<?php wc_product_dropdown_categories( $args ); ?>
									<?php elseif ( zota_tbay_get_config('search_type') == 'post' ):
										$args = array(
											'show_option_all' => $search_text_categories,
										    'show_count' => 1,
										    'hierarchical' => true,
										    'show_uncategorized' => 0,
										    'name' => 'category',
											'id' => 'blog-cat-'.$_id,
											'class' => 'postform dropdown_product_cat',
										);
									?>
										<?php wp_dropdown_categories( $args ); ?>
									<?php endif; ?>

							  	</div>
						  	<?php endif; ?>
	
							<input type="hidden" name="post_type" value="<?php echo esc_attr( zota_tbay_get_config('search_type') ); ?>" class="post_type" />
						</div>
						
					</div>
				</form>

			</div>

		</div>
	</div>

<?php endif; ?>