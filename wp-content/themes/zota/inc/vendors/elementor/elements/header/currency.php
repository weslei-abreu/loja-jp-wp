<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


use Elementor\Controls_Manager;

class Zota_Elementor_Currency extends Zota_Elementor_Widget_Base {

    public function get_name() {
        return 'tbay-currency';
    }

    public function get_title() {
        return esc_html__('Zota Currency', 'zota');
    }

    public function get_icon() {
        return 'eicon-database';
    }

    protected function get_html_wrapper_class() {
		return 'w-auto elementor-widget-' . $this->get_name();
	}

    protected function register_controls() {

        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Currency Settings', 'zota'),
            ]
        );

        $this->add_control(
            'txt_type',
            [
                'label'              => esc_html__('Choose Type Text', 'zota'),
                'type'               => Controls_Manager::SELECT,
                'options' => [
                    'desc' => esc_html__('Desc','zota'),
                    'code' => esc_html__('Code','zota')
                ],
                'default' => 'desc'
            ]
        );
        $this->add_control(
            'show_flags',
            [
                'label'              => esc_html__('Show Flags', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'no'
            ]
        );
        $this->add_control(
            'position_flags',
            [
                'label'              => esc_html__('Position Flags', 'zota'),
                'type'               => Controls_Manager::SELECT,
                'options' => [
                    'left'  => esc_html__('Left','zota'),
                    'right'  => esc_html__('Right','zota')
                ],
                'default' => 'left',
                'condition' => [
                    'show_flags' => 'yes'
                ]
            ]
        );
        
        $this->add_control(
            'text_currency_size',
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
                    '{{WRAPPER}} .woocommerce-currency-switcher-form .SumoSelect > .CaptionCont > span,
                    {{WRAPPER}}.SumoSelect > .optWrapper > .options li.opt label,
                    {{WRAPPER}} .SumoSelect>.CaptionCont,
                    {{WRAPPER}} .woocommerce-currency-switcher' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'text_currency_line_height',
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
                    '{{WRAPPER}} .woocommerce-currency-switcher-form .SumoSelect,
                    {{WRAPPER}} .woocommerce-currency-switcher' => 'line-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'color_text_currency',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-currency-switcher-form .SumoSelect > .CaptionCont,
                    {{WRAPPER}} .woocommerce-currency-switcher'=> 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'hover_color_text_currency',
            [
                'label'     => esc_html__('Hover Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    
                    '{{WRAPPER}} .woocommerce-currency-switcher-form .SumoSelect > .CaptionCont:hover,
                    {{WRAPPER}} .woocommerce-currency-switcher-form .SumoSelect:hover > .CaptionCont,
                    {{WRAPPER}} .woocommerce-currency-switcher:hover,
                    {{WRAPPER}} .SumoSelect > .optWrapper > .options li.opt.selected,
                    {{WRAPPER}} .SumoSelect > .optWrapper > .options li.opt:hover,
                    {{WRAPPER}} .woocommerce-currency-switcher-form .SumoSelect > .CaptionCont:hover label i:after,
                    {{WRAPPER}} .woocommerce-currency-switcher-form .SumoSelect:hover label i:after,
                    {{WRAPPER}} .SumoSelect > .optWrapper > .options li.opt:focus'    => 'color: {{VALUE}}',
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
    public function get_script_depends() {
        return ['jquery-sumoselect'];
    }
    protected function zota_currency() {
        $settings = $this->get_settings_for_display();
        extract($settings);

        if($show_flags === 'yes') {
            $check_flags = 1;
        }else {
            $check_flags = 0;
        }
        $this->add_render_attribute(
            'woocs',
            [
                'show_flags'    => $check_flags,
                'txt_type'      => $txt_type ,
                'flag_position' => $position_flags
            ]
        );

        $woocs = $this->get_render_attribute_string( 'woocs' );

        if( zota_is_Woocommerce_activated() && class_exists( 'WOOCS' ) ) {
            wp_enqueue_style('sumoselect');
            ?>
            <div class="tbay-currency">
            <?php
                echo do_shortcode( "[woocs $woocs ]" );
            ?>
            </div>
            <?php
        }
    }
}
$widgets_manager->register(new Zota_Elementor_Currency());

