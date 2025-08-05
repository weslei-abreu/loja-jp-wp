<?php
/**
 * Auction last bids elementor widget
 *
 */

if ( $query_results->have_posts() ) {
?>
<div class="products latest-bids">
    <h2><?php esc_html_e('Latest Bids', 'wc_simple_auctions') ?></h2>

    <div class="recent-bids-image-links" style="opacity: 1;">
          <?php
            while ( $query_results->have_posts() ) :
                $query_results->the_post();
                $product = wc_get_product( get_the_ID() );?>
        <a class="recent-bids-image-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" data-auction_id="<?php echo esc_attr( $product->get_id()); ?>">
            <?php echo $product->get_image( 'large'); ?>
        </a>
         <?php endwhile;?>
    </div>

    <div class="recent-bids-list-container">
        <ul class="products recent-bids-list">
            <?php
            while ( $query_results->have_posts() ) :
                $query_results->the_post();
                $product = wc_get_product( get_the_ID() );?>
                <li class="product recent-bids-list-item" data-auction_id="<?php echo esc_attr( $product->get_id()); ?>" >
                    <a class="recent-bids-list-item-inner" href="<?php echo esc_url( $product->get_permalink() ); ?>" data-auction_id="<?php echo esc_attr( $product->get_id()); ?>">
                    <span class="recent-bids-list-item-datum recent-bids-list-item-datum-title">
                       <span class="product-title woocommerce-loop-product__title"><?php echo esc_html( $product->get_name() ); ?></span>
                    </span>
                    <span class="recent-bids-list-item-datum recent-bids-list-item-datum-amount">
                        <span class="recent-bids-list-item-datum-inner" ><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
                    </span>
                    <span class="recent-bids-list-item-datum recent-bids-list-item-datum-time">
                        <?php wc_get_template( 'global/auction-countdown-compact.php', array( 'hide_time' => $hide_time ) ); ?>
                    </span>
                </a>
            </li>
            <?php endwhile;?>
        </ul>
    </div>
</div>


<?php }
