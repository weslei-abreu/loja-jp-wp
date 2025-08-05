<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


use Elementor\Controls_Manager;

class Zota_Elementor_Custom_Language extends Zota_Elementor_Widget_Base {

    public function get_name() {
        return 'tbay-custom-language';
    }

    public function get_title() {
        return esc_html__('Zota Language', 'zota');
    }

    public function get_icon() {
        return 'eicon-text-area';
    }

    protected function get_html_wrapper_class() {
		return 'w-auto elementor-widget-' . $this->get_name();
    }
       
    protected function zota_custom_language() {
        do_action('zota_tbay_header_custom_language');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Style', 'zota'),
            ]
        );
        $this->add_control(
            'custom_language_size',
            [
                'label' => esc_html__('Font Size', 'zota'),
                'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 80,
					],
				],
				'selectors' => [
                    '{{WRAPPER}} .tbay-custom-language a span' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'custom_language_line_height',
            [
                'label' => esc_html__('Line Height', 'zota'),
                'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 80,
					],
				],
				'selectors' => [
                    '{{WRAPPER}}  .tbay-custom-language > ul > li > a > span' => 'line-height: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'color_text_custom_language',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-custom-language .select-button'    => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'hover_color_text_custom_language',
            [
                'label'     => esc_html__('Hover Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-custom-language .select-button:hover,{{WRAPPER}} .tbay-custom-language li:hover .select-button,
                    {{WRAPPER}} .tbay-custom-language .select-button:hover:after,{{WRAPPER}} .tbay-custom-language li:hover .select-button:after,
                    {{WRAPPER}} a:hover'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'position_sub_menu',
            [
                'label'     => esc_html__('Position Sub Menu', 'zota'),
                'type'      => Controls_Manager::SELECT,
                'options' => [
                    'top' => esc_html__('Top','zota'),
                    'bottom' => esc_html__('Bottom','zota'),
                ],
                'default' => 'bottom',
                'prefix_class' => 'sub-menu-',
                
            ]
        );
    
        $this->end_controls_section();
    }
}
$widgets_manager->register(new Zota_Elementor_Custom_Language());

