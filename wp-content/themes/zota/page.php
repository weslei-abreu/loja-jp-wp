<?php

get_header();
$sidebar_configs = zota_tbay_get_page_layout_configs();

$class_row = ( get_post_meta( $post->ID, 'tbay_page_layout', true ) === 'main-right' ) ? 'flex-row-reverse' : '';

zota_tbay_render_breadcrumbs();

?>

<section id="main-container" class="<?php echo esc_attr( apply_filters('zota_tbay_page_content_class', 'container') );?>">
	<div class="row <?php echo esc_attr($class_row); ?>">
		<?php if ( isset($sidebar_configs['sidebar']) && is_active_sidebar($sidebar_configs['sidebar']['id']) ) : ?>
		<div class="<?php echo esc_attr($sidebar_configs['sidebar']['class']) ;?>">
		  	<aside class="sidebar" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
		   		<?php dynamic_sidebar( $sidebar_configs['sidebar']['id'] ); ?>
		  	</aside>
		</div>
		<?php endif; ?>
		<div id="main-content" class="main-page <?php echo esc_attr($sidebar_configs['main']['class']); ?>">
			<div id="main" class="site-main">
				<?php
				// Start the loop.
				while ( have_posts() ) : the_post();
				
					// Include the page content template.
					the_content(); 

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'zota' ),
							'after'  => '</div>',
						)
					);
 
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				// End the loop.
				endwhile;
				?>
			</div><!-- .site-main -->

		</div><!-- .content-area -->
	</div>
</section>
<?php get_footer(); ?>