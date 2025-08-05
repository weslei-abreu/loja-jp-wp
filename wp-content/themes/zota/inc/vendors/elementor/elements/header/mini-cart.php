<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
class Zota_Elementor_Mini_Cart extends Zota_Elementor_Widget_Base {

    protected $nav_menu_index = 1;

    public function get_name() {
        return 'tbay-mini-cart';
    }

    public function get_title() {
        return esc_html__('Zota Mini Cart', 'zota');
    }

    public function get_icon() {
        return 'eicon-cart-medium';
    }
    
    protected function get_html_wrapper_class() {
		return 'w-auto elementor-widget-' . $this->get_name();
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Mini Cart', 'zota'),
            ]
        );

        $this->add_control(
            'heading_mini_cart',
            [
                'label' => esc_html__('Mini Cart', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );   

        $this->add_control(
            'icon_mini_cart',
            [
                'label'              => esc_html__('Icon', 'zota'),
                'type'               => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'tb-icon tb-icon-shopping-cart',
					'library' => 'tbay-custom',
                ],                
            ]
        );
        $this->add_control(
            'icon_mini_cart_size',
            [
                'label' => esc_html__('Font Size Icon', 'zota'),
                'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cart-dropdown .cart-icon i' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'show_title_mini_cart',
            [
                'label'              => esc_html__('Display Title "Mini-Cart"', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => ''        
            ]
        );
        $this->add_control(
            'title_mini_cart',
            [
                'label'              => esc_html__('"Mini-Cart" Title', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'default'            => esc_html__('Shopping cart', 'zota'),
                'condition'          => [
                    'show_title_mini_cart' => 'yes'
                ]
            ]
        );
        
        $this->add_control(
            'price_mini_cart',
            [
                'label'              => esc_html__('Show "Mini-Cart" Price', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default'            => 'yes',
                'separator'    => 'after',
            ]
        );


        $this->end_controls_section();
        $this->register_section_style_icon();
        $this->register_section_style_text();
        $this->register_section_style_total();
        $this->register_section_style_popup_cart();
        $this->register_section_style_price();
        
    }


    protected function register_section_style_icon() {
        $this->start_controls_section(
            'section_style_icon',
            [
                'label' => esc_html__('Style Icon', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->start_controls_tabs('tabs_style_icon');

        $this->start_controls_tab(
            'tab_icon_normal',
            [
                'label' => esc_html__('Normal', 'zota'),
            ]
        );
        $this->add_control(
            'color_icon',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-dropdown .cart-icon'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-dropdown .cart-icon'    => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_icon_hover',
            [
                'label' => esc_html__('Hover', 'zota'),
            ]
        );
        $this->add_control(
            'hover_color_icon',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-dropdown .cart-icon:hover'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'hover_bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-dropdown .cart-icon:hover'    => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    protected function register_section_style_text() {

        $this->start_controls_section(
            'section_style_text',
            [
                'label' => esc_html__('Style Text', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title_mini_cart' => 'yes'
                ]
            ]
        );
        $this->add_control(
            'margin_text_cart',
            [
                'label'     => esc_html__('Margin Text Cart', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .cart-dropdown .text-cart' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );  
        $this->start_controls_tabs('tabs_style_text');

        $this->start_controls_tab(
            'tab_text_normal',
            [
                'label' => esc_html__('Normal', 'zota'),
            ]
        );
        $this->add_control(
            'color_text',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-dropdown .text-cart'    => 'color: {{VALUE}}',
                ],
            ]
        );   

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_text_hover',
            [
                'label' => esc_html__('Hover', 'zota'),
            ]
        );
        $this->add_control(
            'hover_color_text',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-dropdown .text-cart:hover' => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    protected function register_section_style_popup_cart() {

        $this->start_controls_section(
            'section_style_popup_cart',
            [
                'label' => esc_html__('Style Popup', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'border_popup',
				'selector' => '{{WRAPPER}} .cart-popup.show .dropdown-menu',
			]
		);
        $this->add_control(
            'border_radius_popup_cart',
            [
                'label'     => esc_html__('Border Radius', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .cart-popup.show .dropdown-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );     
        $this->add_control(
            'position_popup_cart',
            [
                'label' => esc_html__('Position Popup', 'zota'),
                'type' => Controls_Manager::SLIDER,
				'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 150,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ]
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 64,
                ],
                'size_units' => [ 'px' ,'%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-popup.show .dropdown-menu'=> 'top: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );    
       
        $this->end_controls_section();
    }
    protected function register_section_style_price() {
        $this->start_controls_section(
            'section_style_price_cart',
            [
                'label' => esc_html__('Style Price', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'price_mini_cart' => 'yes'
                ]
            ]
        );
        
        $this->add_control(
            'color_cart_price',
			[
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocs_special_price_code' => 'color: {{VALUE}}',
                ],
            ]
		);
        $this->add_control(
            'color_cart_price_hover',
			[
                'label'     => esc_html__('Color Hover', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocs_special_price_code:hover' => 'color: {{VALUE}}',
                ],
            ]
		);
        
        $this->end_controls_section();
    }

    private function register_section_style_total() {
        $this->start_controls_section(
            'section_style_total',
            [
                'label' => esc_html__('Style Total', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'number_size',
            [
                'label' => esc_html__('Font Size', 'zota'),
                'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 20,
					],
                ],
                'default' => [
                    'unit' => 'px',
					'size' => 11
                ],
                'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .cart-icon span.mini-cart-items' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'number_font-weight',
            [
                'label' => esc_html__('Font Weight', 'zota'),
                'type' => Controls_Manager::SELECT,
				'options' => [
                    '100' => '100',
                    '200' => '200',
                    '300' => '300',
                    '400' => '400',
                    '500' => '500',
                    '600' => '600',
                    '700' => '700',
                ],
                'default' => '400',
				'selectors' => [
					'{{WRAPPER}} .cart-icon span.mini-cart-items' => 'font-weight: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'color_number',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-icon span.mini-cart-items'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        
        $this->add_control(
            'bg_total',
            [
                'label'     => esc_html__('Background', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-icon span.mini-cart-items'    => 'background: {{VALUE}}',
                ],
            ]
        );   
        $this->end_controls_section();
    }

    protected function render_woocommerce_mini_cart() {
        $settings = $this->get_settings_for_display();
        extract($settings);

        $args = [
            'icon_mini_cart'                 => $icon_mini_cart,
            'show_title_mini_cart'           => $show_title_mini_cart,
            'title_mini_cart'                => $title_mini_cart,
            'price_mini_cart'                => $price_mini_cart,
        ];
        
        zota_tbay_get_woocommerce_mini_cart($args);
    }
}
$widgets_manager->register(new Zota_Elementor_Mini_Cart());

