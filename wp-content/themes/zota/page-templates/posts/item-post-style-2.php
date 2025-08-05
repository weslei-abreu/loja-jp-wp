<?php 
$style           = isset($style) ? $style : 'post-style-2';
$thumbsize       = isset($thumbnail_size_size) ? $thumbnail_size_size : 'medium';
$show_title      = zota_switcher_to_boolean($show_title); ;
$show_categories   = zota_switcher_to_boolean($show_category); 
$show_author     =  zota_switcher_to_boolean($show_author); 
$show_date       =  zota_switcher_to_boolean($show_date); 
$show_comments   =  zota_switcher_to_boolean($show_comments); 
$show_comments_text   =  zota_switcher_to_boolean($show_comments_text); 
$post_title_tag       = isset($post_title_tag) ? $post_title_tag : 'h3';
$show_excerpt    =  zota_switcher_to_boolean($show_excerpt); 
$excerpt_length  = isset($excerpt_length) ? $excerpt_length : 15;
$show_read_more  =  zota_switcher_to_boolean($show_read_more); 
$read_more_text  = isset($read_more_text) ? $read_more_text : esc_html__('Read more', 'zota');


$text_domain               = esc_html__(' comments','zota');    
if( get_comments_number() == 1) {
    $text_domain = esc_html__(' comment','zota');
}

?>
<article class="post item-post <?php echo esc_attr($style); ?>">   
    <figure class="entry-thumb <?php echo  (!has_post_thumbnail() ? 'no-thumb' : ''); ?>">
        <a href="<?php the_permalink(); ?>"  class="entry-image">
          <?php
                if ( zota_elementor_is_activated() ) {
                    the_post_thumbnail($thumbsize);
                } else {
                    the_post_thumbnail();
                }
          ?>
        </a> 
    </figure>
    <div class="entry-header">
        <?php if ( $show_title && get_the_title() ) : ?>
            <<?php echo trim($post_title_tag); ?> class="entry-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </<?php echo trim($post_title_tag); ?>>
        <?php endif; ?>
                
        
        <?php if( $show_excerpt ) : ?>
            <div class="entry-description"><?php echo zota_tbay_substring( get_the_excerpt(), $excerpt_length, '...' ); ?></div>
        <?php endif; ?>

        <?php zota_post_meta(array(
            'date'          => $show_date,
            'author'        => $show_author,
            'author_img'    => 0,
            'comments'      => $show_comments,
            'comments_text' => $show_comments_text,
            'tags'          => 0,
            'cats'          => $show_categories,
            'edit'          => 0,
        )); ?>
        
        <?php do_action('zota_blog_before_meta_list'); ?>

        <?php if( $show_read_more ) : ?>
            <a href="<?php the_permalink(); ?>" class="readmore" title="<?php echo esc_attr($read_more_text); ?>"><?php echo trim($read_more_text); ?></a>
        <?php endif; ?>

        <?php do_action('zota_blog_after_meta_list'); ?>
    </div>
</article>
