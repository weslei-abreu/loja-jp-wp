<?php 
if( zota_checkout_optimized() ) {
	get_template_part('page-templates/header-checkout'); 
	return;
}

$header 	= apply_filters( 'zota_tbay_get_header_layout', 'header_default' );
$class_header = zota_header_located_on_slider();
?>

<header id="tbay-header" class="tbay_header-template site-header <?php echo esc_attr($class_header) ?>">

	<?php if ( $header != 'header_default' ) : ?>	

		<?php zota_tbay_display_header_builder(); ?> 

	<?php else : ?>
	
	<?php get_template_part( 'page-templates/header-default' ); ?>

	<?php endif; ?>
	<div id="nav-cover"></div>
	<div class="bg-close-canvas-menu"></div>
</header>