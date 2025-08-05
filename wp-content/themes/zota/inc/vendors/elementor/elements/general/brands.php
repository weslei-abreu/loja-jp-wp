<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Zota_Elementor_Brands extends  Zota_Elementor_Carousel_Base{
    /**
     * Get widget name.
     *
     * Retrieve tabs widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'tbay-brands';
    }

    /**
     * Get widget title.
     *
     * Retrieve tabs widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Zota Brands', 'zota' );
    }

    public function get_script_depends() {
        return [ 'zota-custom-slick', 'slick' ];
    } 
 
    /**
     * Get widget icon.
     *
     * Retrieve tabs widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-meta-data';
    }

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {
        $this->register_controls_heading();

        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'General', 'zota' ),
            ]
        );
 
        $this->add_control(
            'layout_type',
            [
                'label'     => esc_html__('Layout Type', 'zota'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'grid',
                'options'   => [
                    'grid'      => esc_html__('Grid', 'zota'), 
                    'carousel'  => esc_html__('Carousel', 'zota'), 
                ],
            ]
        );   
        $this->add_control(
            'brands_align',
            [
                'label' => esc_html__('Align','zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left','zota'),
                        'icon' => 'fas fa-align-left'
                    ],
                    'center' => [
                        'title' => esc_html__('Center','zota'),
                        'icon' => 'fas fa-align-center'
                    ],
                    'flex-end' => [
                        'title' => esc_html__('Right','zota'),
                        'icon' => 'fas fa-align-right'
                    ],   
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .item .inner,.tbay-element-brands .row.grid > div' => 'justify-content: {{VALUE}} !important',
                ]
            ]
        );
        $brands = new \Elementor\Repeater();

        $brands->add_control(
            'brand_image',
            [
                'label' => esc_html__( 'Choose Image', 'zota' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $brands->add_control(
            'brand_link',
            [
                'label' => esc_html__( 'Link to', 'zota' ),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        );

        $this->add_control(
            'brands',
            [
                'label' => esc_html__( 'Brand Items', 'zota' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $brands->get_controls()
            ]
        );

        

        $this->end_controls_section();

        $this->style_brand_item();

        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);

    }

    protected function style_brand_item() {
        $this->start_controls_section(
            'section_style_brand_item',
            [
                'label' => esc_html__( 'Brand item', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
			'brand_item_opacity',
			[
				'label' => esc_html__( 'Opacity', 'zota' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .item .inner img' => 'opacity: {{SIZE}};',
					'{{WRAPPER}} .item .inner img:hover' => 'opacity: 1;',
				],
			]
		);

        $this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'brand_item_border',
				'selector' => '{{WRAPPER}} .item .inner img',
			]
		);

		$this->add_responsive_control(
			'brand_item_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .item .inner img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->end_controls_section();

    }

    protected function render_item($item, $index) {
        extract($item);
        $image_id = $brand_image['id'];
        if (empty($image_id)) return;

        $link_key = 'brand_link_' . $index;

        echo '<div class="inner">';
        if (!empty($brand_link['url'])) {
            $this->add_link_attributes($link_key, $brand_link);
            echo '<a ' . $this->get_render_attribute_string($link_key) . '>';
        }
        echo wp_get_attachment_image($image_id, 'full');
        if (!empty($brand_link['url'])) echo '</a>';
        echo '</div>';
    }  


}
$widgets_manager->register(new Zota_Elementor_Brands());
