<?php

wp_enqueue_script( 'slick' );
wp_enqueue_script( 'zota-custom-slick' );
  
$inner = 'inner';

$columns 		= isset($columns) ? $columns : 4;

$skin = zota_tbay_get_theme();
$product_item = 'item-product/skins/'.$skin.'/'.$inner;

$rows_count 	= isset($rows) ? $rows : 1;
$nav_type		= ( !empty($nav_type) ) ? $nav_type : 'no';
$pagi_type		= ( !empty($pagi_type) ) ? $pagi_type : 'no';
$auto_type		= ( !empty($auto_type) ) ? $auto_type : 'no';
$loop_type		= ( !empty($loop_type) ) ? $loop_type : 'no';
$autospeed_type	= ( !empty($autospeed_type) ) ? $autospeed_type : 500;


$screen_desktop          	=      isset($screen_desktop) ? $screen_desktop : 4;
$screen_desktopsmall     	=      isset($screen_desktopsmall) ? $screen_desktopsmall : 3;
$screen_tablet           	=      isset($screen_tablet) ? $screen_tablet : 3;
$screen_landscape_mobile    =      isset($screen_landscape_mobile) ? $screen_landscape_mobile : 2;
$screen_mobile           	=      isset($screen_mobile) ? $screen_mobile : 1;

$disable_mobile          =      isset($disable_mobile) ? $disable_mobile : '';

$data_carousel = zota_tbay_data_carousel($rows, $nav_type, $pagi_type, $loop_type, $auto_type, $autospeed_type, $disable_mobile); 
$responsive_carousel  = zota_tbay_check_data_responsive_carousel($columns, $screen_desktop, $screen_desktopsmall, $screen_tablet, $screen_landscape_mobile, $screen_mobile);

$class_item = ($rows_count != 1) ? 'row-no-one' : '';

$classes = array('products-grid', 'product');
?>
<div class="owl-carousel products rows-<?php echo esc_attr( $rows_count ); ?> <?php echo esc_attr( $class_item ); ?>" <?php echo $responsive_carousel; ?>  <?php echo $data_carousel; ?> >
    <?php while ( $loop->have_posts() ): $loop->the_post(); ?>
		
		<div class="item">
			<div <?php wc_product_class( $classes, $loop->get_id() ); ?>>
	            <?php 
					$post_object = get_post( get_the_ID() );
					setup_postdata( $GLOBALS['post'] =& $post_object );
					
					wc_get_template( $product_item.'.php'); 
				?>
	        </div>
	
		</div>
		
    <?php endwhile; ?>
</div> 
<?php wp_reset_postdata(); ?>