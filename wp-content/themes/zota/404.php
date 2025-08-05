<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage Zota
 * @since Zota 1.0
 */
/*

*Template Name: 404 Page
*/
get_header();
$image = zota_tbay_get_config('img_404');
if( isset($image['url']) && !empty($image['url']) ) {
	$image = $image['url'];
} else {
	$image = ZOTA_IMAGES . '/img-404.jpg';
}
?>

<section id="main-container" class=" container inner page-404">
	<div id="main-content" class="main-page">

		<section class="error-404">
			<h1 class="title-404"><?php esc_html_e('Opps! page not found','zota') ?></h1>
			<div class="page-content">
				<p class="sub-title"><?php esc_html_e( 'It seems we can not find what you are looking for. Perhaps searching can help or go back to', 'zota') ?> <a href="<?php echo esc_attr(home_url( '/' )) ?>" class="back"><?php esc_html_e('Home page ', 'zota'); ?></a></p>
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php esc_attr_e('page 404', 'zota'); ?>">
			</div><!-- .page-content -->
			
		</section><!-- .error-404 -->
	</div>
</section>

<?php get_footer(); ?>