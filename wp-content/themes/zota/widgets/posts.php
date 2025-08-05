<?php
extract( $args );
extract( $instance );
$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . trim($after_title);
}
$query = new WP_Query(array(
	'post_type'=>'post',
	'post__in' => $ids
));

if( isset($instance['styles']) ) {
	$styles = $instance['styles'];
}

if($query->have_posts()){
?>
	<div class="post-widget media-post-layout widget-content <?php echo esc_attr($styles); ?>">
		<?php while ( $query->have_posts() ): $query->the_post(); ?>
			<article class="item-post post">
				<?php
				if ( has_post_thumbnail() ) {
				  ?>
				  	<div class="entry-thumb <?php echo  (!has_post_thumbnail() ? 'no-thumb' : ''); ?>">
						<a href="<?php the_permalink(); ?>" aria-hidden="true">
						<?php
							the_post_thumbnail( 'full', array( 'alt' => get_the_title() ) );
						?>
						</a>
				  	</div>
			  	<?php
			 	}
				?>
				
			    <div class="entry-content <?php echo ( !has_post_thumbnail() ) ? 'no-thumb' : ''; ?>">
		            <?php
		                if (get_the_title()) {
		                ?>
		                    <h3 class="entry-title">
		                       <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		                    </h3>
		                <?php
		            	}
			        ?>
			        <ul class="entry-meta-list">
						<li class="entry-date"><?php echo zota_time_link(); ?></li>
						<li class="comments-link"><?php echo comments_popup_link( 'No comments','1 comment', '% comments', 'comments-link', 'Comments are off for this post' ); ?></li>
              		</ul>
			    </div>
			</article>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</div>
	
<?php } ?>
