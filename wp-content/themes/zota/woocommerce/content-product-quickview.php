
<?php
/**
 * zota_woocommerce_before_quick_view hook
 */
do_action( 'zota_woocommerce_before_quick_view' );
?>
<div id="tbay-quick-view-modal" class="singular-shop">
    <div id="product-<?php the_ID(); ?>" <?php post_class('product '); ?>>
    	<div id="tbay-quick-view-content" class="woocommerce single-product no-gutters">
            <div class="image-mains product col-12 col-md-6">
                <?php 
                    /**
                     * woocommerce_before_single_product_summary hook
                     *
                     * @hooked woocommerce_show_product_images - 20
                     */
                    do_action( 'woocommerce_before_single_product_summary' );
                ?>
            </div>
            <div class="summary entry-summary col-12 col-md-6">
                <div class="information">
                    <div class="zota-single-product-title-main">
                        <?php
                            /**
                             * zota_top_single_product hook
                             * @hooked the_product_single_time_countdown -5
                             * @hooked woocommerce_template_single_title -10
                             * @hooked woocommerce_template_single_rating -20
                             */
                            do_action( 'zota_top_single_product' );
                        ?>
                    </div>
                    <?php
                        /**
                         * woocommerce_single_product_summary hook
                         * @hooked the_product_single_time_countdown - 0
                         * @hooked woocommerce_template_single_price - 10
                         * @hooked excerpt_product_variable - 10
                         * @hooked woocommerce_template_single_excerpt - 20
                        * @hooked woocommerce_template_single_add_to_cart - 30
                        * @hooked woocommerce_template_single_meta - 40
                        */
                        do_action( 'woocommerce_single_product_summary' );
                    ?>
                </div>
            </div>
    	</div>
    </div>
</div>
<?php
/**
 * zota_woocommerce_before_quick_view hook
 */
do_action( 'zota_woocommerce_after_quick_view' );

