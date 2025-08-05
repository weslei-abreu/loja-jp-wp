<?php
extract( $args );
extract( $instance );
$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . trim($after_title);
}

if( isset($instance['styles']) ) {
	$styles = $instance['styles'];
}

$args = array(
	'post_type' => 'post',
	'meta_key' => 'zota_post_views_count',
	'orderby' => 'meta_value_num', 
	'order' => 'DESC',
	'posts_per_page' => $number_post
);

$query = new WP_Query($args);
if($query->have_posts()): ?>
	<div class="post-widget media-post-layout widget-content <?php echo esc_attr($styles); ?>">
		<ul>
		<?php
			while($query->have_posts()):$query->the_post();
		?>
			<li class="post">
				<?php
		        if ( has_post_thumbnail() ) {
		            ?>
	                <div class="entry-thumb">
	                    <a href="<?php the_permalink(); ?>" class="entry-image">
	                        <?php the_post_thumbnail( 'widget' ); ?>
	                    </a>  
	                </div>
		            <?php
		        }
		        ?>
		        <div class="entry-content">
		          	<?php
		              if (get_the_title()) {
		              ?>
		                  <h4 class="entry-title">
		                      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		                  </h4>
		              <?php
		         	 }
		          	?>

                   	<ul class="entry-meta-list">
						<li class="entry-date"><?php echo zota_time_link(); ?></li>
                  	</ul>
		        </div>
			</li>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
		</ul>
	</div>

<?php endif; ?>
