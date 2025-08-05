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

$videolink =  get_post_meta( get_the_ID(),'tbay_post_video_link', true );

if( !(isset($videolink) && $videolink) ) {
	$content = apply_filters( 'the_content', get_the_content() );
	$video = false;

	// Only get video from the content if a playlist isn't present.
	if ( false === strpos( $content, 'wp-playlist-script' ) ) {
		$video = get_media_embedded_in_content( $content, array( 'video', 'object', 'embed', 'iframe' ) );
	}
}

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
        	
			<?php $class_image = ($videolink) ? 'post-preview' : ''; ?>
			<?php 
				if($videolink || has_post_thumbnail()) {
					?>
						<div class="content-image <?php echo esc_attr( $class_image ); ?>">
							<?php if( $videolink ) : ?>
								<div class="video-thumb video-responsive"><?php echo wp_oembed_get( $videolink ); ?></div>
							<?php elseif( has_post_thumbnail() ) : ?>
								<?php zota_tbay_post_thumbnail(); ?>
							<?php endif; ?>
						</div>
					<?php
				} 
			?>
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