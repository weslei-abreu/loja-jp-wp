<?php
/**
 *
 * The default template for displaying content
 * @since 1.0
 * @version 1.2.0
 *
 */

$columns					= zota_tbay_blog_loop_columns('');
$show_date 					= zota_tbay_get_boolean_query_var('enable_date', true);
$show_author 				= zota_tbay_get_boolean_query_var('enable_author', true);
$show_categories 			= zota_tbay_get_boolean_query_var('enable_categories', true);
$short_descriptions 		= zota_tbay_get_boolean_query_var('enable_short_descriptions', true);
$read_more 					= zota_tbay_get_boolean_query_var('enable_readmore', true);
$show_comment				= zota_tbay_get_boolean_query_var('enable_comment', false);
$show_comment_text			= zota_tbay_get_boolean_query_var('enable_comment_text', false);

$layout_blog   			= apply_filters( 'zota_archive_layout_blog', 10,2 );

$class_main = $class_left = '';

wp_enqueue_script( 'slick' );
wp_enqueue_script( 'zota-custom-slick' );

$galleries = zota_tbay_get_post_galleries();

?>
<!-- /post-standard -->
<?php if ( ! is_single() ) : ?>
<div  class="post clearfix <?php echo esc_attr($layout_blog); ?>">
<?php endif; ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class($class_main); ?>>
<?php if ( is_single() ) : ?>
	<div class="entry-single">
	<?php echo zota_tbay_post_media( get_the_excerpt() ); ?>
<?php endif; ?>
		<?php
			if ( is_single() ) : ?>
				
	        	
				<?php if( $galleries ): ?>
				<div id="post-slide-<?php the_ID(); ?>" class="owl-carousel-play" data-ride="carousel">
					<div class="owl-carousel slider-blog" data-carousel="owl" data-items="1" data-desktopslick="1" data-desktopsmallslick="1" data-tabletslick="1" data-landscapeslick="1" data-mobileslick="1" data-nav="true" data-pagination="false">
						<?php foreach ($galleries as $key => $_img) {
							echo '<img class="skip-lazy" src="'. esc_url($_img) .'">';
						} ?>
					</div>
				</div>
				<?php elseif( has_post_thumbnail() ) : ?>
					<?php zota_tbay_post_thumbnail(); ?>
				<?php endif; ?>
				<div class="entry-header">
					
					<?php if( get_the_category_list() && $show_categories ) : ?>
						<span class="entry-category">
							<?php zota_the_post_category_full(false); ?>
						</span>
					<?php endif; ?>

	        		<?php if (get_the_title()) {
		                ?>
		                    <h1 class="entry-title">
		                       <?php the_title(); ?>
		                    </h1>
		                <?php
	            	} ?>
	        		
					<?php zota_post_meta(array(
						'date'     		=> true,
						'cats'     		=> 0,
						'author'   		=> true,
						'comments' 		=> true,
						'comments_text' => true,
						'tags'     		=> 0,
						'edit'     		=> 0,
					)); ?>

        		</div>
				<div class="post-excerpt entry-content">
					 

					<?php the_content( esc_html__( 'Read more', 'zota' ) ); ?>

					<div class="zota-tag-socials-box"><?php do_action('zota_tbay_post_tag_socials') ?></div>

					<?php do_action('zota_tbay_post_bottom') ?>
					
				</div><!-- /entry-content -->

				<?php
					wp_link_pages( array(
						'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'zota' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
						'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'zota' ) . ' </span>%',
						'separator'   => '<span class="screen-reader-text">, </span>',
					) );
				?>
		<?php endif; ?>
        
    <?php if ( ! is_single() ) : ?>
	
    	<?php
		 	if ( has_post_thumbnail() ) {
		  	?>
		  	<figure class="entry-thumb <?php echo esc_attr( $class_left ); ?> <?php echo  (!has_post_thumbnail() ? 'no-thumb' : ''); ?>">
				   <?php zota_tbay_post_thumbnail(); 
				   ?>
		  	</figure>
		  	<?php
		 	}
		?>
		<div class="entry-content <?php echo esc_attr( $class_left ); ?> <?php echo ( !has_post_thumbnail() ) ? 'no-thumb' : ''; ?>">

			<div class="entry-header">
				<?php if( get_the_category_list() && $show_categories ) : ?>
					<span class="entry-category">
						<?php zota_the_post_category_full(false); ?>
					</span>
				<?php endif; ?>

				<?php zota_post_archive_the_title(); ?>	

				<?php if( $short_descriptions ) : ?>
					<?php zota_post_archive_the_short_description(); ?>
				<?php endif; ?>

				<?php zota_post_meta(array(
					'author'     	=> $show_author,
					'date'     		=> $show_date, 
					'tags'     		=> 0,
					'comments' 		=> $show_comment,
					'comments_text'	=> $show_comment_text,
					'edit'    		=> 0,
				)); ?>

				<?php if( $read_more ) : ?>
					<?php zota_post_archive_the_read_more(); ?>
				<?php endif; ?>

		    </div>

		</div>

    <?php endif; ?>
    <?php if ( is_single() ) : ?>
</div>
<?php endif; ?>
</article>

<?php if ( ! is_single() ) : ?>
</div>
<?php endif; ?>