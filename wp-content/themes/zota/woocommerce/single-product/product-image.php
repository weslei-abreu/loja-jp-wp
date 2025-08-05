<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

use Automattic\WooCommerce\Enums\ProductType;

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $product;

wp_enqueue_script( 'slick' );
wp_enqueue_script( 'zota-custom-slick' );

$sidebar_configs    = zota_tbay_get_woocommerce_layout_configs();
$images_layout      = ( !empty($sidebar_configs['thumbnail']) ) ? $sidebar_configs['thumbnail'] : 'horizontal';
$is_rtl 			= ( is_rtl() ) ? 'yes' : 'no';

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
	'woocommerce-product-gallery',
	'woocommerce-product-gallery--' . ( $product->get_image_id() ? 'with-images' : 'without-images' ),
	'woocommerce-product-gallery--columns-' . absint( $columns ),
	'images',
) );
 
?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" data-rtl="<?php echo esc_attr($is_rtl); ?>" data-layout="<?php echo esc_attr($images_layout); ?>">

	<div class="woocommerce-product-gallery__wrapper">
		<?php
		do_action( 'zota_woocommerce_before_product_thumbnails' );
		
  		if ( $product->get_image_id() ) {
  			$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
  		} else {
            $wrapper_classname = $product->is_type( ProductType::VARIABLE ) && ! empty( $product->get_available_variations( 'image' ) ) ?
            'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder' :
            'woocommerce-product-gallery__image--placeholder';
            $html  = sprintf( '<div class="%s">', esc_attr( $wrapper_classname ) );
			$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_attr__( 'Awaiting product image', 'zota' ) );
			$html .= '</div>';
		}
			
		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
		do_action( 'woocommerce_product_thumbnails' );
		
		?>
	</div>
</div>
