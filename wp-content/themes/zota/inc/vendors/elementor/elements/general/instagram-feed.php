<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Zota_Elementor_Instagram_Feed extends  Zota_Elementor_Carousel_Base{
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
        return 'tbay-instagram-feed';
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
        return esc_html__( 'Zota Instagram Feed', 'zota' );
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
        return 'eicon-gallery-justified';
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
            'heading_settings',
            [
                'label' => esc_html__( 'Settings', 'zota' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'select_feeds',
            [
                'label'     => esc_html__('Select Feeds', 'zota'),
                'type'      => Controls_Manager::SELECT,
                'options'   => $this->get_select_feeds(),
            ]
        );

        $this->add_control(
            'settings_feed',
            [
                'label' => false,
                'type' => Controls_Manager::RAW_HTML,
                'raw'             => sprintf(__('Please settings each feed <a href="%s" target="_blank">here</a>', 'zota'), admin_url('?page=sbi-feed-builder')),
            ]
        );

        $this->end_controls_section();

        $this->register_controls_load_more();
        $this->register_controls_item_style();

        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);

    }

    protected function register_controls_load_more(){
        $this->start_controls_section(
            'section_load_more',
            [
                'label' => esc_html__( 'Load More', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'load_more_typography',
                'selector' => '{{WRAPPER}} #sbi_load .sbi_btn_text',
            ]
        );

        $this->add_responsive_control(
            'load_more_style_margin',
            [
                'label' => esc_html__( 'Margin', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} #sbi_load' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );        

        $this->add_responsive_control(
            'load_more_style_padding',
            [
                'label' => esc_html__( 'Padding', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} #sbi_load' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        ); 

        $this->add_control(
            'load_more_style_bg',
            [
                'label' => esc_html__( 'Background', 'zota' ),
                'type' => Controls_Manager::COLOR, 
                'selectors' => [
                    '{{WRAPPER}} #sb_instagram #sbi_load .sbi_load_btn' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_controls_item_style(){
        $this->start_controls_section(
            'section_item_style',
            [
                'label' => esc_html__( 'Item', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label'     => esc_html__( 'Space Between', 'zota' ),
                'type'      => Controls_Manager::SLIDER,
                'default'   => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100, 
                    ],
                ],   
                'selectors' => [
                    '{{WRAPPER}} #sb_instagram.sbi_col_4 #sbi_images .sbi_item'   => 'padding-left: {{SIZE}}{{UNIT}} !important; padding-right: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} #sb_instagram.sbi_col_4 #sbi_images'         => 'margin-left: -{{SIZE}}{{UNIT}}; margin-right: -{{SIZE}}{{UNIT}}; width: calc(100% + {{SIZE}}{{UNIT}} + {{SIZE}}{{UNIT}});',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function get_select_feeds() {
        $exported_feeds = \InstagramFeed\Builder\SBI_Db::feeds_query();
        $feeds = array();
        foreach($exported_feeds as $feed_id => $feed) {
            $feeds[$feed['id']] = 'Feed '.$feed['id'];
        }

        return $feeds;
    }

}
$widgets_manager->register(new Zota_Elementor_Instagram_Feed());
