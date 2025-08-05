<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

class Zota_Elementor_Nav_Menu extends Zota_Elementor_Widget_Base {

    protected $nav_menu_index = 1;

    public function get_name() {
        return 'tbay-nav-menu';
    }

    public function get_title() {
        return esc_html__('Zota Nav Menu', 'zota');
    }

    public function get_icon() {
        return 'eicon-nav-menu';
    }

    public function get_script_depends() {
        $script = [];

        $script[]   = 'jquery-treeview';

        return $script;
    }

    public function on_export($element) {
        unset($element['settings']['menu']);

        return $element;
    }

    protected function get_nav_menu_index() {
        return $this->nav_menu_index++;
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Layout', 'zota'),
            ]
        );

        $menus = $this->get_available_menus();

        if (!empty($menus)) {
            $this->add_control(
                'menu',
                [
                    'label'        => esc_html__('Menu', 'zota'),
                    'type'         => Controls_Manager::SELECT,
                    'options'      => $menus,
                    'default'      => array_keys($menus)[0],
                    'save_default' => true,
                    'separator'    => 'after',
                    'description'  => sprintf(__('Go to the <a href="%s" target="_blank">Menus screen</a> to manage your menus.', 'zota'), admin_url('nav-menus.php')),
                ]
            );
        } else {
            $this->add_control(
                'menu',
                [
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => sprintf(__('<strong>There are no menus in your site.</strong><br>Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', 'zota'), admin_url('nav-menus.php?action=edit&menu=0')),
                    'separator'       => 'after',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );
        }

        $this->add_control(
            'layout',
            [
                'label'              => esc_html__('Layout Menu', 'zota'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'horizontal',
                'options'            => [
                    'horizontal' => esc_html__('Horizontal', 'zota'),
                    'vertical'   => esc_html__('Vertical', 'zota'),
                    'treeview'   => esc_html__('Tree View', 'zota'),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_responsive_control(
            'align_items', 
            [ 
                'label'        => esc_html__('Align', 'zota'),
                'type'         => Controls_Manager::CHOOSE,
                'options'      => [
                    'flex-start'    => [ 
                        'title' => esc_html__('Start', 'zota'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center'  => [ 
                        'title' => esc_html__('Center', 'zota'),
                        'icon'  => 'fa fa-align-center',
                    ], 
                    'flex-end'   => [ 
                        'title' => esc_html__('End', 'zota'),
                        'icon'  => 'fa fa-align-right',
                    ], 
                ],
                'prefix_class' => 'elementor-nav-menu%s__align-',
                'default'      => '',
                'condition' => [
                    'layout' => 'horizontal'
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu' => 'justify-content: {{VALUE}} !important',
                ]
            ]
        );

        $this->add_control(
            'hidden_indicator',
            [
                'label'        => esc_html__('Hidden Submenu Indicator', 'zota'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => true,
                'prefix_class' => 'hidden-indicator-',
                'condition' => [
                    'layout!' => 'treeview'
                ],
            ]
        );

        $this->add_control(
            'type_menu',
            [
                'label'              => esc_html__('Type Menu', 'zota'),
                'type'               => Controls_Manager::SELECT,
                'default'            => 'none',
                'options'            => [
                    'none'      => esc_html__('None', 'zota'),
                    'toggle'    => esc_html__('Toggle Menu', 'zota'),
                    'canvas'    => esc_html__('Canvas Menu', 'zota'),
                ],
                'frontend_available' => true,
                'condition' => [
                    'layout!'  => 'horizontal' 
                ]
            ]
        ); 

        $this->add_control(
            'show_canvas_menu_class',
            [
                'label' => esc_html__( 'Show Canvas Menu Class', 'zota' ),
                'type' => Controls_Manager::HIDDEN,
                'prefix_class' => 'width-auto-',
                'default' => 'yes', 
                'condition' => [
                    'type_menu' => 'canvas',
                    'layout!' => 'horizontal',
                ],
            ]
        );
        

        $this->end_controls_section();

        $this->register_section_toggle_menu(); 
        $this->register_section_style_menu_canvas();

        $this->register_section_vertical_menu();
        $this->register_section_canvas_menu();

        $this->register_section_style_main_menu();
        $this->register_section_style_menu_dropdown();
    }

    private function register_section_style_main_menu() {

        $this->start_controls_section(
            'section_style_main-menu',
            [
                'label' => esc_html__('Main Menu', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'bg_menu',
            [
                'label'     => esc_html__('Background Color Full', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu'    => 'background-color: {{VALUE}}',
                ],
            ]
        );     

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'menu_typography',
                'selector' => '{{WRAPPER}} .elementor-nav-menu--main >ul > li> a',
            ]
        );

        $this->start_controls_tabs('tabs_menu_item_style');

        $this->start_controls_tab(
            'tab_menu_item_normal',
            [
                'label' => esc_html__('Normal', 'zota'),
            ]
        );

        $this->add_control(
            'color_menu_item',
            [
                'label'     => esc_html__('Text Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--main >ul > li> a'=> 'color: {{VALUE}}',
                    '{{WRAPPER}} .elementor-nav-menu--main >ul > li > a i'=> 'color: {{VALUE}}',
                    '{{WRAPPER}} .elementor-nav-menu--main >ul > li> .caret:before'  => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'menu_item_box_shadow',
                'selector'  => '{{WRAPPER}} .elementor-nav-menu--main >ul > li> a',
                'condition' => [
                    'layout' => 'horizontal',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_menu_item_hover',
            [
                'label' => esc_html__('Hover', 'zota'),
            ]
        );
        $this->add_control(
            'bg_menu_item_hover',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--main >ul > li> a:hover,
                    {{WRAPPER}} .elementor-nav-menu--main >ul > li > a:focus,
                    {{WRAPPER}} .elementor-nav-menu--main >ul > li.active > a'    => 'background-color: {{VALUE}}',
                ],
            ]
        );        

        $this->add_control(
            'color_menu_item_hover',
            [
                'label'     => esc_html__('Text Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--main >ul > li> a:hover,
                    {{WRAPPER}} .tbay-element-nav-menu .elementor-nav-menu--main >ul > li:hover> a >.caret:before,
                    {{WRAPPER}} .tbay-element-nav-menu .elementor-nav-menu--main >ul > li:hover> a,
                    {{WRAPPER}} .elementor-nav-menu--main >ul > li> a:hover i,
                    {{WRAPPER}} .elementor-nav-menu--main >ul > li> a:focus i,
                    {{WRAPPER}} .elementor-nav-menu--main >ul > li> a.active i,
                    {{WRAPPER}} .elementor-nav-menu--main >ul > li > a:focus,
                    {{WRAPPER}} .elementor-nav-menu--main >ul > li.active > a'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'menu_item_box_shadow_hover',
                'selector'  => '{{WRAPPER}} .elementor-nav-menu--main >ul > li> a:hover',
                'condition' => [
                    'layout' => 'horizontal',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control(
            'padding_menu_item',
            [
                'label'     => esc_html__('Padding', 'zota'),
                'type'      => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--main > ul > li > a'        => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'no_padding_menu_item_first_item',
            [
                'label'        => esc_html__( 'No Margin-Left First Item', 'zota' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_off' => esc_html__( 'Off', 'zota' ),
                'label_on'  => esc_html__( 'On', 'zota' ),
                'default'   => '',
                'condition' => [
                    'layout' => 'horizontal',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--main > .megamenu > li:first-child >.elementor-item' => 'margin-left: 0',
                    '{{WRAPPER}} .elementor-nav-menu--main > .megamenu > li:first-child >.elementor-item + .dropdown-menu' => 'left: 0',
                    '.rtl {{WRAPPER}} .elementor-nav-menu--main > .megamenu > li:first-child >.elementor-item' => 'margin-right: 0',
                    '.rtl {{WRAPPER}} .elementor-nav-menu--main > .megamenu > li:first-child >.elementor-item + .dropdown-menu' => 'right: 0',
                ],
            ] 
        );  

        $this->add_responsive_control(
            'margin_menu_item',
            [
                'label'     => esc_html__('Margin', 'zota'),
                'type'      => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--main > .navbar-nav > li >.elementor-item'        => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_style_menu_canvas() {
        $this->start_controls_section(
            'section_style_canvas',
            [
                'label'     => esc_html__('Canvas', 'zota'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'type_menu' => 'canvas',
                ],
            ]
        );

        
        $this->add_control(
            'toggle_menu_canvas_icon_style_heading_content',
            [
                'label' => esc_html__('Icon content', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );  
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'icon_typography',
                'exclude'   => ['line_height'],
                'selector'  => '{{WRAPPER}} .canvas-menu-btn-wrapper > a > i',
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
			'toggle_menu_canvas_icon_padding',
			[
				'label' => esc_html__( 'Padding', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-canvas-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );

        $this->add_responsive_control(
			'toggle_menu_canvas_icon_margin',
			[
				'label' => esc_html__( 'Margin', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-canvas-menu' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );


        $this->add_control(
            'toggle_menu_canvas_icon_style_heading',
            [
                'label' => esc_html__('Icon', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );      

        $this->add_control(
            'toggle_canvas_icon_color',
            [
                'label' => esc_html__('Color', 'zota'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn-canvas-menu i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_menu_canvas_icon_size_style',
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
					'{{WRAPPER}} .btn-canvas-menu i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
			'toggle_canvas_icon_padding',
			[
				'label' => esc_html__( 'Padding', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .btn-canvas-menu i' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );

        $this->add_control(
            'toggle_menu_icon_title_style_heading',
            [
                'label' => esc_html__('Title next to the icon', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );   

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'toggle_canvas_icon_title_typography',
                'exclude'   => ['line_height'],
                'selector'  => '{{WRAPPER}} .toggle-canvas-icon-title',
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'toggle_canvas_title_color',
            [
                'label' => esc_html__('Color', 'zota'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn-canvas-menu .toggle-canvas-icon-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_canvas_icon_title_align',
            [
                'label' => esc_html__('Alignment Title', 'zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'zota'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'zota'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'default' => '', 
                'selectors' => [
                    '{{WRAPPER}} .toggle-canvas-icon-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
			'toggle_canvas_icon_title_margin',
			[
				'label' => esc_html__( 'Margin', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .toggle-canvas-icon-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );

        $this->add_responsive_control(
			'toggle_canvas_icon_title_padding',
			[
				'label' => esc_html__( 'Padding', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .toggle-canvas-icon-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );


        $this->add_control(
            'toggle_canvas_content_style_heading',
            [
                'label' => esc_html__('Positioning Content', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );     

        $this->add_control(
            'toggle_canvas_content_align',
            [
                'label' => esc_html__('Positioning Content', 'zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'zota'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'zota'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'default' => '', 
                'prefix_class' => 'canvas-position-',
            ]
        );    
        
        $this->add_control(
            'toggle_canvas_content_title_style_heading',
            [
                'label' => esc_html__('Title Content', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );  

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'toggle_canvas_content_title_typography',
                'exclude'   => ['line_height'],
                'selector'  => '{{WRAPPER}} .menu-canvas-content .toggle-canvas-title',
                'separator' => 'before',
            ] 
        );

        $this->add_responsive_control(
            'toggle_canvas_title_align',
            [
                'label' => esc_html__('Alignment Title', 'zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'zota'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'zota'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'default' => '', 
                'selectors' => [
                    '{{WRAPPER}} .menu-canvas-content .toggle-canvas-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_style_menu_dropdown() {
        $this->start_controls_section(
            'section_style_dropdown',
            [
                'label'     => esc_html__('Dropdown', 'zota'),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'dropdown_typography',
                'exclude'   => ['line_height'],
                'selector'  => '{{WRAPPER}} .navbar-nav .dropdown-menu > li > a',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'dropdown_Heading',
            [
                'label'     => esc_html__('Heading sub title megamenu', 'zota'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .active-mega-menu .elementor-widget-wp-widget-nav_menu > .elementor-widget-container > h5'       => 'font-size: {{SIZE}}{{UNIT}}',
                ],
            ]
        );


        $this->start_controls_tabs('tabs_dropdown_item_style');

        $this->start_controls_tab(
            'tab_dropdown_item_normal',
            [
                'label' => esc_html__('Normal', 'zota'),
            ]
        );

        $this->add_control(
            'color_dropdown_item',
            [
                'label'     => esc_html__('Text Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .dropdown-menu > li > a, 
                    {{WRAPPER}} .active-mega-menu .elementor-nav-menu > li > a, 
                    {{WRAPPER}} .active-mega-menu .menu > li> a' => 'color: {{VALUE}}',
                ],
            ]
        ); 

        $this->add_control(
            'background_color_dropdown_item',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [ 
                    '{{WRAPPER}} .active-mega-menu > .dropdown-menu, 
                    {{WRAPPER}} .elementor-nav-menu > li.dropdown > .dropdown-menu' => 'background-color: {{VALUE}}; border-color: {{VALUE}}',
                ],
                'separator' => 'none',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_dropdown_item_hover',
            [
                'label' => esc_html__('Hover', 'zota'),
            ]
        );

        $this->add_control(
            'color_dropdown_item_hover',
            [
                'label'     => esc_html__('Text Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .dropdown-menu > li > a:hover, 
                    {{WRAPPER}} .dropdown-menu > li:hover > a,
                    {{WRAPPER}} .active-mega-menu .menu > li> a:hover,
                    {{WRAPPER}} .active-mega-menu .menu > li:hover > a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'background_color_dropdown_item_hover',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .dropdown-menu > li:hover,
                    {{WRAPPER}} .active-mega-menu .menu > li:hover' => 'background-color: {{VALUE}}',
                ],
                'separator' => 'none',
            ]
        );

        $this->end_controls_tab();

         $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'dropdown_box_shadow',
                'exclude'  => [
                    'box_shadow_position',
                ],
                'selector' => '{{WRAPPER}} .navbar-nav li:hover > .dropdown-menu',
            ]
        );

        $this->add_responsive_control(
            'padding_horizontal_dropdown_item',
            [
                'label'     => esc_html__('Horizontal Padding', 'zota'),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .dropdown-menu > li, {{WRAPPER}} .active-mega-menu .menu > li'       => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',

            ]
        );

        $this->add_responsive_control(
            'padding_vertical_dropdown_item',
            [
                'label'     => esc_html__('Vertical Padding', 'zota'),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .dropdown-menu > li, {{WRAPPER}} .active-mega-menu .menu > li'       => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control( 
            'dropdown_padding',
            [
                'label'      => esc_html__('Padding', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .dropdown-menu, {{WRAPPER}} .active-mega-menu .menu'       => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_toggle_menu() {

        $this->start_controls_section(
            'section_toggle_menu',
            [
                'label' => esc_html__( 'Toggle Menu', 'zota' ),
                'condition' => [
                    'type_menu' => 'toggle',
                    'layout!' => 'horizontal',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_menu_align',
            [
                'label' => esc_html__('Alignment', 'zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'zota'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'zota'),
                        'icon' => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'zota'),
                        'icon' => 'fa fa-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__('Justified', 'zota'),
                        'icon' => 'fa fa-align-justify',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .toggle-menu-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'toggle_menu_title_heading',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );        

        $this->add_control(
            'toggle_menu_title',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Title', 'zota' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'toggle_menu_title_tag',
            [
                'label' => esc_html__( 'Title HTML Tag', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h3',
            ]
        );


        $this->add_control(
            'toggle_content_menu',
            [
                'label' => esc_html__( 'Toggle content menu', 'zota' ),
                'type' => Controls_Manager::SWITCHER,
                'prefix_class' => 'elementor-toggle-content-menu-',
                'default' => 'yes',
            ]
        );

        
        $this->add_control(
            'ajax_toggle',
            [
                'label' => esc_html__( 'Ajax Toggle Menu', 'zota' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'description' => esc_html__( 'Show/hidden Ajax Toggle Menu', 'zota' ), 
                'condition' => [
                    'toggle_content_menu' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_content_menu',
            [
                'label' => esc_html__( 'Show content menu', 'zota' ),
                'description' => esc_html__( 'Show content menu on home page', 'zota' ),
                'type' => Controls_Manager::SWITCHER,
                'prefix_class' => 'elementor-show-content-menu-',
                'default'      => 'no',
                'condition' => [
                    'toggle_content_menu!' => '', 
                ],
            ]
        );

        $this->add_control(
            'show_toggle_menu_icon',
            [
                'label' => esc_html__( 'Show Icon', 'zota' ),
                'type' => Controls_Manager::SWITCHER,
            ]
        );


        $this->add_control(
            'toggle_menu_icon_heading',
            [
                'label' => esc_html__('Icon', 'zota'),
                'type' => Controls_Manager::HEADING,
                'condition' => [
                    'show_toggle_menu_icon!' => '',
                ],
            ]
        );    

        $this->add_control(
            'toggle_menu_icon',
            [
                'label' => esc_html__('Icon', 'zota'),
                'type' => Controls_Manager::ICONS,
                'label_block' => true,
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
                ],
                'condition' => [
                    'show_toggle_menu_icon!' => '',
                ],
            ] 
        );

        $this->end_controls_section();

        $this->register_section_style_toggle_menu();
    }

    private function register_section_vertical_menu() {

        $this->start_controls_section(
            'section_vertical_menu',
            [
                'label' => esc_html__( 'Vertical Menu', 'zota' ),
                'condition' => [
                    'layout' => 'vertical',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_vertical_submenu_align',
            [
                'label' => esc_html__('Alignment Sub Menu', 'zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'zota'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'zota'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'default' => 'right',
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_canvas_menu() {

        $this->start_controls_section(
            'section_canvas_menu',
            [
                'label' => esc_html__( 'Canvas Menu', 'zota' ),
                'condition' => [
                    'type_menu' => 'canvas',
                    'layout!' => 'horizontal',
                ],
            ]
        );

        $this->add_control(
            'toggle_canvas_icon_heading',
            [
                'label' => esc_html__('Icon', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );  

        $this->add_control(
            'toggle_canvas_menu_icon',
            [
                'label' => esc_html__('Icon', 'zota'),
                'type' => Controls_Manager::ICONS,
                'label_block' => true,
				'default' => [
					'value' => 'tb-icon tb-icon-text-align-right',
					'library' => 'tbay-custom',
                ],
            ]
        );

        $this->add_control(
            'toggle_canvas_icon_title',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Title', 'zota' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'toggle_canvas_icon_title_tag',
            [
                'label' => esc_html__( 'Title HTML Tag', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h3',
            ]
        );

        $this->add_control(
            'toggle_canvas_title_heading',
            [
                'label' => esc_html__('Title Content', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );        

        $this->add_control(
            'toggle_canvas_title',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Title', 'zota' ),
                'label_block' => true,
            ]
        );
        $this->add_control(
            'toggle_canvas_title_tag',
            [
                'label' => esc_html__( 'Title HTML Tag', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h3',
            ]
        );

        $this->add_control(
            'ajax_canvas',
            [
                'label' => esc_html__( 'Ajax canvas Menu', 'zota' ),
                'type' => Controls_Manager::SWITCHER,
                'description' => esc_html__( 'Show/hidden Ajax canvas Menu', 'zota' ), 
                'default' => 'no',
                'condition' => [ 
                    'type_menu' => 'canvas',
                    'layout!' => 'horizontal',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_style_toggle_menu() {
        $this->start_controls_section(
            'section_style_toggle_menu',
            [
                'label' => esc_html__( 'Toggle Menu', 'zota' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'type_menu' => 'toggle',
                    'layout!' => 'horizontal',
                ],
            ]
        );

        
        $this->add_control(
            'toggle_menu_title_style_heading',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );  

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'style_toggle_menu_typography',
                'selector' => '{{WRAPPER}} .toggle-menu-title span',
            ]
        );

        
        $this->add_control(
            'toggle_menu_color',
            [
                'label'     => esc_html__('Text Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .toggle-menu-title, {{WRAPPER}} .toggle-menu-title > *'=> 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'toggle_menu_color_hover',
            [
                'label'     => esc_html__('Hover Text Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .toggle-menu-title:hover, {{WRAPPER}} .toggle-menu-title a:hover,
                    {{WRAPPER}} .open .toggle-menu-title a'=> 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'style_toggle_menu',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .toggle-menu-title'    => 'background-color: {{VALUE}}',
                ],
            ]
        );     

        $this->add_responsive_control(
			'toggle_menu_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .toggle-menu-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );

        $this->add_responsive_control(
			'toggle_menu_padding',
			[
				'label' => esc_html__( 'Padding', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .toggle-menu-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );

        $this->add_responsive_control(
			'toggle_menu_margin',
			[
				'label' => esc_html__( 'Margin', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .toggle-menu-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );

        $this->add_control(
            'toggle_menu_icon_style_heading',
            [
                'label' => esc_html__('Icon', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );  

        
        $this->add_control(
            'toggle_menu_icon_size_style',
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
					'{{WRAPPER}} .toggle-menu-title i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function render_get_toggle_menu() {
        $settings = $this->get_settings();

        extract( $settings );

        $ouput = '';

        if( $layout === 'horizontal' || $type_menu !== 'toggle' ) return;

        if( empty($toggle_menu_title) && !$show_toggle_menu_icon ) return;

        $ouput .= '<'. $toggle_menu_title_tag .'  class="toggle-menu-title category-inside-title menu-click">';
 
            if( !empty($toggle_content_menu) ) {
                $ouput .= '<a href="javascript:void(0);" class="click-show-menu">';
            }

                if( $show_toggle_menu_icon ) {
                    $ouput .= '<i class="'. $toggle_menu_icon['value'] .'"></i>';
                }
 
                if( !empty($toggle_menu_title) )  $ouput .= '<span>'. $toggle_menu_title .'</span>';

            if( !empty($toggle_content_menu) ) {
                $ouput .= '</a>';
            }
        
        $ouput .= '</'. $toggle_menu_title_tag .'>';

        return $ouput;
    }

    public function render_canvas_button_menu() {
        $settings = $this->get_settings();
        extract($settings);

        $ouput = '';   

        if( $layout === 'horizontal' || $type_menu !== 'canvas' ) return; 
      
        $ouput .= '<div class="canvas-menu-btn-wrapper">';
            $ouput .= '<a href="javascript:void(0);" class="btn-canvas-menu menu-click">';
                $ouput .= '<i class="'. $toggle_canvas_menu_icon['value'] .'"></i>';
                    $ouput .= '<'. $toggle_canvas_icon_title_tag .'  class="toggle-canvas-icon-title">';
                        $ouput .= $toggle_canvas_icon_title;
                    $ouput .= '</'. $toggle_canvas_icon_title_tag .'>';
            $ouput .= '</a>';
        $ouput .= '</div>';
        $ouput .= '<div class="canvas-overlay-wrapper"></div>';
      
        return $ouput;
    }

    public function render_get_toggle_canvas_menu() {
        $settings = $this->get_settings();

        extract( $settings );

        $ouput = '';

        if( $layout === 'horizontal' || $type_menu !== 'canvas' ) return; 

        if( empty($toggle_canvas_title) ) return;

        $ouput .= '<a class="canvas-close-tab" href="javascript:void(0)"><i class="tb-icon tb-icon-close-01"></i></a>';

        $ouput .= '<'. $toggle_canvas_title_tag .'  class="toggle-canvas-title">';

 
            $ouput .= $toggle_canvas_title;

        
        $ouput .= '</'. $toggle_canvas_title_tag .'>';

        return $ouput; 
    }
}
$widgets_manager->register(new Zota_Elementor_Nav_Menu());

