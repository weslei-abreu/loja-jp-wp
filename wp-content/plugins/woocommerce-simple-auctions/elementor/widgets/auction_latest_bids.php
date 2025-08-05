<?php
namespace ElementorPro\Modules\Woocommerce\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Auction_Latest_Bids extends Base_Widget {

	public function get_name() {
		return 'woocommerce-auction-latest-bids';
	}

	public function get_title() {
		return esc_html__( 'Auctions Latest Bids', 'wc_simple_auctions' );
	}

	public function get_icon() {
		return 'eicon-auction-latest-bids';
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'auction' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_auction_latest bids_style',
			[
				'label' => esc_html__( 'auction latest bids', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wc_style_warning',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'elementor-pro' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-latest-bids',
			]
		);

		$this->add_responsive_control(
			'text_align',
			[
				'label' => esc_html__( 'Alignment', 'elementor-pro' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'elementor-pro' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'elementor-pro' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'elementor-pro' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '.woocommerce {{WRAPPER}}',
			]
		);

		$this->add_control(
			'auction_latest bids_heading',
			[
				'label' => esc_html__( 'auction latest bids', 'elementor-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__( 'Number of auctions', 'elementor-pro' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 5,
				'range' => [
					'px' => [
						'max' => 20,
					],
				],
			]
		);
		$this->add_control(
			'auction_latest bids_color',
			[
				'label' => esc_html__( 'Color', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.woocommerce {{WRAPPER}}' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'auction_latest bids_typography',
				'selector' => '.woocommerce {{WRAPPER}}',
			]
		);

		$this->add_control(
			'auction_latest bids_block',
			[
				'label' => esc_html__( 'Stacked', 'elementor-pro' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'elementor-auction-latest-bids-block-',
			]
		);

		$this->add_responsive_control(
			'auction_latest bids_spacing',
			[
				'label' => esc_html__( 'Spacing', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}}:not(.elementor-auction-latest-bids-block-yes)' => 'margin-right: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}}:not(.elementor-auction-latest-bids-block-yes)' => 'margin-left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.elementor-auction-latest-bids-block-yes' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
	global $wpdb;
	$settings = $this->get_settings_for_display();
	$posts_per_page = 5;

	if ( ! empty( $settings['posts_per_page'] ) ) {
			$posts_per_page = $settings['posts_per_page'];
	}



		$auctions_ids = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT auction_id
					FROM (
  						SELECT auction_id, MAX(date) AS date
  						FROM ' . $wpdb->prefix . 'simple_auction_log
  						GROUP BY auction_id
					) t
					ORDER BY date DESC  LIMIT 0,%d',

				 $posts_per_page ),
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
					'posts_per_page' => $posts_per_page,
					'no_found_rows'  => 1,
					'post__in'       => $postids ,
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'orderby'        => 'post__in',
					'auction_arhive' => true,
					'show_past_auctions' => true,
				);

		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'auction',
			),
		);


		$r = new \WP_Query( $query_args );

		$template_args = array(
						'hide_time' => empty( $instance['hide_time'] ) ? 0 : 1,
						'query_results' => $r
					);

		wc_get_template( 'elementor-widgets/auctions-last-bids.php', $template_args );





	}

	public function render_plain_content() {}
}
