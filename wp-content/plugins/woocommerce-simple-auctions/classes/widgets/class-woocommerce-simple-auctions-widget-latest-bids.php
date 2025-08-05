<?php
/**
 * Latest bid Auctions Widget
 *
 * Gets and displays featured auctions in an unordered list
 *
* @package Widgets
 * @version 1.0.0
 * @extends WP_Widget
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}; // Exit if accessed directly

class  WC_SA_Widget_Latest_Bid_Auction extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_latest_bids_auctions';
		$this->widget_description = esc_html__( 'Display a list of auctions with latest bid on your site.', 'wc_simple_auctions' );
		$this->widget_id          = 'woocommerce_latest_bids_auctions';
		$this->widget_name        = esc_html__( 'WooCommerce Latest Bid Auction', 'wc_simple_auctions' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Latest bids auctions', 'wc_simple_auctions' ),
				'label' => __( 'Title', 'wc_simple_auctions' ),
			),
			'number' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 5,
				'label' => esc_html__( 'Number of auctions to show:', 'wc_simple_auctions' ),
			),
			'hide_time' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => esc_html__( 'Hide time left', 'wc_simple_auctions' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 * @param array $args     Arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {

		global $wpdb;

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : $this->settings['number']['std'];

		$auctions_ids = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT auction_id
					FROM (
  						SELECT auction_id, MAX(date) AS date
  						FROM ' . $wpdb->prefix . 'simple_auction_log
  						GROUP BY auction_id
					) t
					ORDER BY date DESC  LIMIT 0,%d',

				 $number ),
			ARRAY_N );

		$postids = array();
		if(isset($auctions_ids) && !empty($auctions_ids)){
			foreach ($auctions_ids as $auction) {
				$postids []= $auction[0];

			}
        } else{
            return;
        }


		$query_args = array(
					'posts_per_page' => $number,
					'no_found_rows'  => 1,
					'post__in'       => $postids ,
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'orderby'        => 'post__in',
				);



				$query_args['tax_query'] = array(
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => 'auction',
					),
				);


				$query_args['auction_arhive'] = true;



				$r = new WP_Query( $query_args );

				if ( $r->have_posts() ) {

					$this->widget_start( $args, $instance );

					echo wp_kses_post( apply_filters( 'woocommerce_before_widget_product_list', '<ul class="product_list_widget">' ) );

					$template_args = array(
						'widget_id'   => $args['widget_id'],
						'hide_time' => empty( $instance['hide_time'] ) ? 0 : 1,
					);

					while ( $r->have_posts() ) {
						$r->the_post();
						wc_get_template( 'content-widget-auction-product.php', $template_args );
					}

					echo wp_kses_post( apply_filters( 'woocommerce_after_widget_product_list', '</ul>' ) );

					$this->widget_end( $args );
				}

				wp_reset_postdata();

				$content = ob_get_clean();

				echo wp_kses_post ( $content);

				$this->cache_widget( $args, $content );
	}
}
