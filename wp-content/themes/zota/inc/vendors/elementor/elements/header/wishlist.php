<?php

if ( ! defined( 'ABSPATH' ) || function_exists('Zota_Elementor_Wishlist') ) {
    exit; // Exit if accessed directly.
}


use Elementor\Controls_Manager;

class Zota_Elementor_Wishlist extends Zota_Elementor_Widget_Base {

    protected $nav_menu_index = 1;

    public function get_name() {
        return 'tbay-wishlist';
    }

    public function get_title() {
        return esc_html__('Zota Wishlist', 'zota');
    }

    public function get_icon() {
        return 'eicon-heart';
    }

    protected function get_html_wrapper_class() {
		return 'w-auto elementor-widget-' . $this->get_name();
	}

    protected function register_controls() {

        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Wishlist', 'zota'),
            ]
        );

        $this->add_control(
            'icon_wishlist',
            [
                'label'              => esc_html__('Icon', 'zota'),
                'type'               => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'tb-icon tb-icon-heart',
					'library' => 'tbay-custom',
                ],                
            ]
        );
        $this->add_control(
            'icon_wishlist_size',
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
					'{{WRAPPER}} .top-wishlist i' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'show_title_wishlist',
            [
                'label'              => esc_html__('Display Title', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'no'        
            ]
        );
        $this->add_control(
            'title_wishlist',
            [
                'label'              => esc_html__('Title', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'default' => esc_html__('My Wishlist','zota'),
                'condition' => [
                    'show_title_wishlist' => 'yes'
                ]        
            ]
        );
        $this->add_control(
            'show_total_wishlist',
            [
                'label'              => esc_html__('Show Total', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default'            => 'yes',
            ]
        );
    
        $this->end_controls_section();
        $this->register_section_style_icon();
        $this->register_section_style_text();
        $this->register_section_style_total();
    }

    private function register_section_style_icon() {

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
                    '{{WRAPPER}} .top-wishlist i'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .top-wishlist i'    => 'background-color: {{VALUE}}',
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
                    '{{WRAPPER}} .top-wishlist i:hover'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'hover_bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .top-wishlist i:hover'    => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    private function register_section_style_text() {

        $this->start_controls_section(
            'section_style_text',
            [
                'label' => esc_html__('Style Text', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title_wishlist' => 'yes'
                ]
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
                    '{{WRAPPER}} .title-wishlist'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .count-wishlist'    => 'color: {{VALUE}}',
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
                    '{{WRAPPER}} .title-wishlist:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .count-wishlist:hover'       => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->end_controls_tab();
        $this->end_controls_tabs();
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
					'{{WRAPPER}} .top-wishlist .count_wishlist' => 'font-size: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .top-wishlist .count_wishlist' => 'font-weight: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'color_number',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .top-wishlist .count_wishlist'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        
        $this->add_control(
            'bg_total',
            [
                'label'     => esc_html__('Background', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .top-wishlist .count_wishlist'    => 'background: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'position_left',
            [
                'label'     => esc_html__('Position Left', 'zota'),
                'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .top-wishlist .count_wishlist' => 'left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );   

        $this->end_controls_section();
    }
    

    public function render_item_wishlist() {
        $this->add_render_attribute('wishlist', 'class', 'wishlist');
        $settings = $this->get_settings();
        extract( $settings );
        $url_wishlist = YITH_WCWL()->get_wishlist_url();
        $wishlist_count = zota_get_wishlist_count();
        ?>
        <a href="<?php echo esc_url($url_wishlist)?>" <?php echo $this->get_render_attribute_string('wishlist'); ?>>
            <?php $this->render_item_icon($icon_wishlist); ?>
           <?php if($show_total_wishlist === 'yes') {
               ?>
                <span class="count_wishlist"><?php echo trim($wishlist_count) ?></span>
               <?php
           } ?>

           <?php if($show_title_wishlist === 'yes' && !empty($title_wishlist) && isset($title_wishlist)) {
               ?>
                <span class="title-wishlist"><?php echo trim($title_wishlist) ?></span>
               <?php
           } ?>


            
            
        </a>
        <?php
    }
}
$widgets_manager->register(new Zota_Elementor_Wishlist());

