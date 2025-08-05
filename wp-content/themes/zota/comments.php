<?php
/**
 * The template for displaying comments
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
        <h3 class="comments-title"><?php comments_number( esc_html__('0 Comments', 'zota'), esc_html__('1 Comment', 'zota'), esc_html__('% Comments', 'zota') ); ?></h3>
		<?php zota_tbay_comment_nav(); ?>
		<ul class="comment-list">
			<?php
				wp_list_comments( array(
					'style'       => 'ul',
					'short_ping'  => true,
					'avatar_size' => 80,
				) );
			?>
		</ul><!-- .comment-list -->

		<?php zota_tbay_comment_nav(); ?>

	<?php endif; // have_comments() ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'zota' ); ?></p>
	<?php endif; ?>

	<?php
		$args = wp_parse_args( array() );
		if ( ! isset( $args['format'] ) ) {
			$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
		}
		$html5    = 'html5' === $args['format'];
        $comment_args = array(
        'title_reply'=> esc_html__('Leave a Reply','zota'),
        'comment_field' => '<p class="comment-form-comment form-group"><textarea id="comment" class="form-control" name="comment" placeholder="'. esc_html__('Your Comment', 'zota') .'" cols="45" rows="11" aria-required="true"></textarea></p>',
        'fields' => apply_filters(
        	'comment_form_default_fields',
        		array(
                    'author' => '<p class="comment-form-author form-group col-md-4">
					            <input id="author" class="form-control" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="'. esc_html__('Your name', 'zota') .'" size="30" aria-required="true" /></p>',
                    'email' => '<p class="comment-form-email form-group col-md-4">
					            <input id="email" class="form-control" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" placeholder="'. esc_html__('Your email', 'zota') .'" size="30" aria-required="true" /></p>',
					'url'    => '<p class="comment-form-url col-md-4"><input id="url" class="form-control" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" placeholder="'. esc_html__('Your website', 'zota') .'" size="30" maxlength="200" /></p>',
                )
			),
            'label_submit' => esc_html__('Post Comment', 'zota'),
			'comment_notes_before' => '<div class="form-group h-info">'.esc_html__('Your email address will not be published. Required fields are makes.','zota').'</div>',
			'comment_notes_after' => '',
        );
    ?>

	<?php zota_tbay_comment_form($comment_args); ?>
</div><!-- .comments-area -->
