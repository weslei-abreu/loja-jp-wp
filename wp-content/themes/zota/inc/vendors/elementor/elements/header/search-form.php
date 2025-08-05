<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;


class Zota_Elementor_Search_Form extends Zota_Elementor_Widget_Base {

    protected $nav_menu_index = 1;

    public function get_name() {
        return 'tbay-search-form';
    }

    public function get_title() {
        return esc_html__('Zota Search Form', 'zota');
    }
    
    public function get_icon() {
        return 'eicon-search';
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Search Form', 'zota'),
            ]
        ); 
       
        $this->_register_form_search();
        $this->_register_button_search();
        $this->_register_category_search();

        $this->add_control(
            'advanced_show_result',
            [
                'label' => esc_html__('Show Result', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_control(
            'show_image_search',
            [
                'label'   => esc_html__('Show Image of Search Result', 'zota'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );
        $this->add_control(
            'show_price_search',
            [
                'label'              => esc_html__('Show Price of Search Result', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );
        $this->register_section_trending_search();
        $this->end_controls_section();
        $this->register_section_style_search_form();
        
    }

    protected function register_section_style_search_form() {
        $this->start_controls_section(
            'section_style_search_form',
            [
                'label' => esc_html__('Style Search Form', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'search_form_line_height',
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
                    '{{WRAPPER}} .tbay-search-form .tbay-search,
                    {{WRAPPER}} .tbay-search-form .select-category,{{WRAPPER}} .tbay-search-form .button-search:not(.icon),
                    {{WRAPPER}} .tbay-search-form .select-category > select' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbay-search-form .select-category,{{WRAPPER}} .tbay-search-form .button-search:not(.icon),
                    {{WRAPPER}} .tbay-preloader,{{WRAPPER}} .tbay-search-form .button-search:not(.icon) i,{{WRAPPER}} .tbay-search-form .SumoSelect' => 'line-height: {{SIZE}}{{UNIT}}'
                ],
            ]
        );
        $this->add_control(
            'search_form_width',
            [
                'label' => esc_html__('Width', 'zota'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 300,
                        'max' => 1000,
                    ],
                    '%' => [
                        'min' => 50,
                        'max' => 100,
                    ]
                ],
                'size_units' => [ 'px' ,'%'],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .form-group .input-group,
                    {{WRAPPER}}' => 'width: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_control(
            'border_style_tbay_search_form',
            [
                'label' => esc_html__( 'Border Type', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'None', 'zota' ),
                    'solid' => esc_html__( 'Solid', 'zota' ),
                    'double' => esc_html__( 'Double', 'zota' ),
                    'dotted' => esc_html__( 'Dotted', 'zota' ),
                    'dashed' => esc_html__( 'Dashed', 'zota' ),
                    'groove' => esc_html__( 'Groove', 'zota' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .form-group .input-group' => 'border-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'border_width_tbay_search_form',
            [
                'label' => esc_html__( 'Width', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],

                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .form-group .input-group' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .SumoSelect.open>.optWrapper,{{WRAPPER}} .autocomplete-suggestions' => 'margin-top: {{BOTTOM}}{{UNIT}};'
                ],
                'condition' => [
                    'border_style_tbay_search_form!' => '',
                ],
            ]
        );
        $this->add_control(
            'border_color_tbay_search_form',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .form-group .input-group' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'border_style_tbay_search_form!' => '',
                ],
            ]
        );

        $this->register_border_radius_tbay_search_form();
        
        
        $this->add_control(
            'advanced_categories_search_style',
            [
                'label' => esc_html__('Categories Search', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator'    => 'before',
                'condition' => [
                    'enable_categories_search' => 'yes'
                ]
            ]
        );
        $this->add_control(
            'bg_category_search',
            [
                'label'     => esc_html__('Background', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'enable_categories_search' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .select-category'    => 'background: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'color_category_search',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'enable_categories_search' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .select-category','{{WRAPPER}} .tbay-search-form .select-category > select'    => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'border_style_category_search',
            [
                'label' => esc_html__( 'Border Type', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'None', 'zota' ),
                    'solid' => esc_html__( 'Solid', 'zota' ),
                    'double' => esc_html__( 'Double', 'zota' ),
                    'dotted' => esc_html__( 'Dotted', 'zota' ),
                    'dashed' => esc_html__( 'Dashed', 'zota' ),
                    'groove' => esc_html__( 'Groove', 'zota' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .select-category' => 'border-style: {{VALUE}};',
                ],
                'condition' => [
                    'border_style_tbay_search_form' => '',
                    'enable_categories_search' => 'yes'
                ],
            ]
        );
        $this->add_control(
            'border_width_category_search',
            [
                'label' => esc_html__( 'Width', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],

                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .select-category' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    
                ],
                'condition' => [
                    'border_style_category_search!' => '',
                ],
            ]
        );
        $this->add_control(
            'border_color_category_search',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .select-category' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'border_style_category_search!' => '',
                ],
            ]
        );
        
        $this->add_control(
            'border_radius_category_search',
            [
                'label'     => esc_html__('Border Radius Search Form', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .select-category' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    
                ],
                'condition' => [
                    'border_style_category_search!' => '',
                ],
            ]
        ); 

        $this->add_control(
            'advanced_btn_search_style',
            [
                'label' => esc_html__('Button Search', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator'    => 'before',
            ]
        );
        $this->add_control(
            'padding_btn',
            [
                'label'     => esc_html__('Padding Button Search', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .button-search:not(.icon)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        ); 
        $this->add_control(
            'border_style_btn_search',
            [
                'label' => esc_html__( 'Border Type', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'None', 'zota' ),
                    'solid' => esc_html__( 'Solid', 'zota' ),
                    'double' => esc_html__( 'Double', 'zota' ),
                    'dotted' => esc_html__( 'Dotted', 'zota' ),
                    'dashed' => esc_html__( 'Dashed', 'zota' ),
                    'groove' => esc_html__( 'Groove', 'zota' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .button-group' => 'border-style: {{VALUE}};',
                ],
                'condition' => [
                    'border_style_tbay_search_form' => '',
                ],
            ]
        );
        $this->add_control(
            'border_width_btn_search',
            [
                'label' => esc_html__( 'Width', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],

                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .button-group' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    
                ],
                'condition' => [
                    'border_style_btn_search!' => '',
                ],
            ]
        );
        $this->add_control(
            'border_color_btn_search',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .button-group' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'border_style_btn_search!' => '',
                ],
            ]
        );

        $this->add_control(
            'border_radius_btn_search',
            [
                'label'     => esc_html__('Border Radius Button Search', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .button-search:not(.icon)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        ); 
        
        $this->add_control(
            'btn_search_line_height',
            [
                'label' => esc_html__('Line Height Button', 'zota'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .button-search:not(.icon)' => 'line-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->start_controls_tabs('tabs_style_btn_search');


        $this->start_controls_tab(
            'tab_btn_search_normal',
            [
                'label' => esc_html__('Normal', 'zota'),
            ]
        ); 
        $this->add_control(
            'color_btn_search',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .button-search i,
                    {{WRAPPER}} .tbay-search-form .button-search .text, 
                    {{WRAPPER}} .tbay-search-form .button-group:before'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'bg_btn_search',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .button-search'    => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_btn_search_hover',
            [
                'label' => esc_html__('Hover', 'zota'),
            ]
        );
        $this->add_control(
            'hover_color_btn_search',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .button-search:hover i,
                    {{WRAPPER}} .tbay-search-form .button-search:hover .text'    => 'color: {{VALUE}}',
                ], 
            ]
        );   
        $this->add_control(
            'hover_bg_btn_search',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .button-search:hover'    => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
       
        $this->add_control(
            'advanced_input_search_style',
            [
                'label' => esc_html__('Input Search', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator'    => 'before',
            ]
        );
        $this->add_control(
            'bg_input',
            [
                'label'     => esc_html__('Background Input Search', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .tbay-search'    => 'background: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'color_input',
            [
                'label'     => esc_html__('Color Input Search', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .tbay-search'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .form-control::placeholder'    => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'border_style_input_search',
            [
                'label' => esc_html__( 'Border Type', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'None', 'zota' ),
                    'solid' => esc_html__( 'Solid', 'zota' ),
                    'double' => esc_html__( 'Double', 'zota' ),
                    'dotted' => esc_html__( 'Dotted', 'zota' ),
                    'dashed' => esc_html__( 'Dashed', 'zota' ),
                    'groove' => esc_html__( 'Groove', 'zota' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .tbay-search' => 'border-style: {{VALUE}};',
                ],
                'condition' => [
                    'border_style_tbay_search_form' => '',
                ],
            ]
        );
        $this->add_control(
            'border_width_input_search',
            [
                'label' => esc_html__( 'Width', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],

                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .tbay-search' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    
                ],
                'condition' => [
                    'border_style_input_search!' => '',
                ],
            ]
        );
        $this->add_control(
            'border_color_input_search',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbay-search-form .tbay-search' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'border_style_input_search!' => '',
                ],
            ]
        );
        
        $this->add_control(
            'border_radius_input_search',
            [
                'label'     => esc_html__('Border Radius Search Form', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .tbay-search' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    
                ],
                'condition' => [
                    'border_style_input_search!' => '',
                ],
            ]
        ); 

        $this->add_responsive_control(
            'input_search_padding',
            [
                'label'      => esc_html__( 'Padding', 'zota' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .tbay-search-form .tbay-search' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();

        $this->_register_style_trending_search();
        
    }

    protected function register_border_radius_tbay_search_form() {
        $active_theme           = zota_tbay_get_theme();

        if( $active_theme === 'auto-part' ) {
            $this->add_control(
                'border_radius_tbay_search_form',
                [
                    'label'     => esc_html__('Border Radius Search Form', 'zota'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .tbay-search-form .form-group .input-group' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            ); 
        } else if( $active_theme === 'fashion' ) {
            $this->add_control(
                'border_radius_tbay_search_form',
                [
                    'label'     => esc_html__('Border Radius Search Form', 'zota'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .tbay-search-form .form-group .input-group' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .tbay-search-form .select-category .CaptionCont' => 'border-radius: {{TOP}}{{UNIT}} 0 0 {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .tbay-search-form .button-group,{{WRAPPER}} .tbay-search-form .button-search:not(.icon)' => 'border-radius: {{TOP}}{{UNIT}} 0  0 {{LEFT}}{{UNIT}} ;',
                        '.rtl {{WRAPPER}} .tbay-search-form .button-group, .rtl {{WRAPPER}} .tbay-search-form .button-search:not(.icon)' => 'border-radius: 0 {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 0;',
                    ],
                ]
            );  
        } else {
            $this->add_control(
                'border_radius_tbay_search_form',
                [
                    'label'     => esc_html__('Border Radius Search Form', 'zota'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .tbay-search-form .form-group .input-group' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .tbay-search-form .select-category .CaptionCont' => 'border-radius: {{TOP}}{{UNIT}} 0 0 {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .tbay-search-form .button-group,{{WRAPPER}} .tbay-search-form .button-search:not(.icon)' => 'border-radius: 0 {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 0 ;',
                        '.rtl {{WRAPPER}} .tbay-search-form .button-group, .rtl {{WRAPPER}} .tbay-search-form .button-search:not(.icon)' => 'border-radius: {{RIGHT}}{{UNIT}} 0 0 {{BOTTOM}}{{UNIT}};',
                    ],
                ]
            );  
        }

    }

    protected function _register_form_search() {
        $this->add_control(
            'advanced_type_search',
            [
                'label' => esc_html__('Form', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_control(
            'search_type',
            [
                'label'              => esc_html__('Search Result', 'zota'),
                'type'               => Controls_Manager::SELECT,
                'default' => 'product',
                'options' => [
                    'product'  => esc_html__('Product','zota'),
                    'post'  => esc_html__('Blog','zota')
                ]
            ]
        );

        
        $this->add_control(
            'autocomplete_search',
            [
                'label'              => esc_html__('Auto-complete Search', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'yes', 
            ]
        );
        $this->add_control(
            'placeholder_text',
            [
                'label'              => esc_html__('Placeholder Text', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'default'            => esc_html__('Search products...', 'zota'),
            ]
        );  
        $this->add_control(
            'vali_input_search',
            [
                'label'              => esc_html__('Text Validate Input Search', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'default'            => esc_html__('Enter at least 2 characters', 'zota'),
            ]
        );
        $this->add_control(
            'min_characters_search',
            [
                'label'              => esc_html__('Search Min Characters', 'zota'),
                'type'               => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 6,
                        'step' => 1,
                    ],
                    
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 2,
                ],
            ]
        );
        $this->add_control(
            'search_max_number_results',
            [
                'label'              => esc_html__('Max Number of Search Results', 'zota'),
                'type'               => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 2,
                        'max' => 10,
                        'step' => 1,
                    ],
                    
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
            ]
        );

    }

    protected function _register_button_search() {
        $this->add_control(
            'advanced_button_search',
            [
                'label' => esc_html__('Button Search', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator'    => 'before',
            ]
        );
        $this->add_control(
            'text_button_search',
            [
                'label'              => esc_html__('Button Search Text', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'default' => '',
            ]
        );
        $this->add_control(
            'icon_button_search',
            [
                'label'              => esc_html__('Button Search Icon', 'zota'),
                'type'               => Controls_Manager::ICONS,
                'default' => [
                    'library' => 'tb-icon',
                    'value'   => 'tb-icon tb-icon-search'
                ],
            ]
        );
        $this->add_control(
            'icon_button_search_size',
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
                    '{{WRAPPER}} .button-search i' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
    }

    protected function _register_category_search() {
        $this->add_control(
            'advanced_categories_search',
            [
                'label'         => esc_html__('Categories Search', 'zota'),
                'type'          => Controls_Manager::HEADING,
                'separator'     => 'before',
            ]
        );
        $this->add_control(
            'enable_categories_search',
            [
                'label'              => esc_html__('Enable Search in Categories', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        $this->add_control(
            'text_categories_search',
            [
                'label'              => esc_html__('Search in Categories Text', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'default'            =>  esc_html__('All Categories', 'zota'),
                'condition' => [
                    'enable_categories_search' => 'yes'
                ]
            ]
        );
        $this->add_control(
            'count_categories_search',
            [
                'label'              => esc_html__('Show count in Categories', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'enable_categories_search' => 'yes'
                ]
            ]
        );
    }
    protected function register_section_trending_search() {
        
        $this->add_control(
            'advanced_trending_search',
            [
                'label'         => esc_html__('Trending Search', 'zota'),
                'type'          => Controls_Manager::HEADING,
                'separator'     => 'before',
            ]
        );
        $this->add_control(
            'enable_trending_search',
            [
                'label'              => esc_html__('Show Trending Search', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        $repeater = $this->register_trending_search_repeater();
        $this->add_control(
            'trending_search',
            [
                'label' => esc_html__( 'Trending Search Items', 'zota' ),
                'type' => Controls_Manager::REPEATER,
                'condition' => [
                    'enable_trending_search' => 'yes'
                ],
                'fields' => $repeater->get_controls(),
                'trending_field' => '{{{ trending_search_item }}}',
                'default' => $this->register_set_trending_search_default(),
            ]
        );   
    }

    private function register_trending_search_repeater() {
        $repeater = new \Elementor\Repeater();
        $repeater->add_control (
            'trending_search_item', 
            [
                'label' => esc_html__( 'Text Trending', 'zota' ),
                'type'      => Controls_Manager::TEXT,
            ]
        );

        return $repeater;
    }
    protected function register_set_trending_search_default() {
        $defaults = [
            [
                'trending_search_item' => esc_html__( 'Search 1', 'zota' ),
            ],
            [
                'trending_search_item' => esc_html__( 'Search 2', 'zota' ),
            ],
        ];
        return $defaults;
    }

    
    public function get_script_depends() {
        return ['jquery-sumoselect'];
    }
    public function get_style_depends() {
        return ['sumoselect'];
    }
    
    public function _register_style_trending_search() {
        $this->start_controls_section(
            'section_style_trending_search',
            [
                'label' => esc_html__('Style Trending Search', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_trending_search' => 'yes'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'trending_search_typography',
                'selector' => '{{WRAPPER}} .trending-search > ul li a',
            ]
        );

        $this->add_control(
            'space_trending_search',
            [
                'label'     => esc_html__('Space Trending Search', 'zota'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50, 
                    ], 
                ],
                'selectors' => [
                    '{{WRAPPER}} .trending-search > ul li + li' => 'padding-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_style_trending_search');

        $this->start_controls_tab(
            'trending_search_normal',
            [
                'label' => esc_html__('Normal', 'zota'),
            ]
        );
        $this->add_control(
            'color_trending_search',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .trending-search > ul li a'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        

        $this->end_controls_tab();

        $this->start_controls_tab(
            'trending_search_hover',
            [
                'label' => esc_html__('Hover', 'zota'),
            ]
        );
        $this->add_control(
            'hover_color_trending_search',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .trending-search > ul li a:hover'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }

    public function render_search_form() {
        $settings = $this->get_settings_for_display();
        extract($settings);
        $_id                    = zota_tbay_random_key();
        $class_active_ajax      = ( zota_switcher_to_boolean($autocomplete_search) ) ? 'zota-ajax-search' : '';
        $active_theme           = zota_tbay_get_theme();

        $this->add_render_attribute(
            'search_form',
            [
                'class' => [
                    $class_active_ajax,
                    'searchform'
                ],
                'data-thumbnail' => zota_switcher_to_boolean($show_image_search),
                'data-appendto' => '.search-results-'.$_id,
                'data-price' => zota_switcher_to_boolean($show_price_search),
                'data-minChars' => $min_characters_search['size'],
                'data-post-type' => $search_type,
                'data-count' => $search_max_number_results['size'],
            ]
        );

        ?>
            <div class="tbay-search-form">
                <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" <?php echo $this->get_render_attribute_string( 'search_form' ); ?> >
                    <div class="form-group">
                        <div class="input-group">

                            <?php if( $active_theme === 'fashion' ) {
                                $this->searchform_the_button($icon_button_search, $text_button_search);
                            } ?>
                            
                            <?php if ( $enable_categories_search === 'yes' ): ?>
                                <div class="select-category input-group-addon">
                                    <?php if ( class_exists( 'WooCommerce' ) && $search_type === 'product' ) :
                                        $args = array(
                                            'show_option_none'   => $text_categories_search,
                                            'show_count' => zota_switcher_to_boolean($count_categories_search),
                                            'hierarchical' => true,
                                            'id' => 'product-cat-'.$_id,
                                            'show_uncategorized' => 0
                                        );
                                    ?> 
                                    <?php wc_product_dropdown_categories( $args ); ?>
                                    
                                    <?php elseif ( $search_type === 'post' ):
                                        $args = array(
                                            'show_option_all' => $text_categories_search,
                                            'show_count' => zota_switcher_to_boolean($count_categories_search),
                                            'hierarchical' => true,
                                            'show_uncategorized' => 0,
                                            'name' => 'category',
                                            'id' => 'blog-cat-'.$_id,
                                            'class' => 'postform dropdown_product_cat',
                                        );
                                    ?>
                                        <?php wp_dropdown_categories( $args ); ?>
                                    <?php endif; ?>

                                </div>
                            <?php endif; ?>

                            <input data-style="right" type="text" placeholder="<?php echo esc_attr($placeholder_text); ?>" name="s" required oninvalid="this.setCustomValidity('<?php echo esc_attr($vali_input_search) ?>')" oninput="setCustomValidity('')" class="tbay-search form-control input-sm"/>
                            
                            <div class="search-results-wrapper">
                                <div class="zota-search-results search-results-<?php echo esc_attr( $_id );?>" ></div>
                            </div>

                           <?php if( $active_theme !== 'fashion' ) {
                                $this->searchform_the_button($icon_button_search, $text_button_search);
                            } ?>


                            <input type="hidden" name="post_type" value="<?php echo esc_attr($search_type); ?>" class="post_type" />
                        </div>
                        
                    </div>
                </form>
            </div>           
        <?php
        if( $enable_trending_search === "yes" ) {
            $this->trending_search();
        }
    }

    public function searchform_the_button($icon_button_search, $text_button_search) {
        ?>
        <div class="button-group input-group-addon">
            <button type="submit" class="button-search btn btn-sm>">
                <?php $this->render_item_icon($icon_button_search) ?>
                <?php if(!empty($text_button_search) && isset($text_button_search) ) {
                    ?>
                        <span class="text"><?php echo trim($text_button_search); ?></span>
                    <?php
                } ?>
            </button>
            <div class="tbay-preloader"></div>
        </div>
        <?php
    }
    
    public function trending_search() {
        ?>
            <div class="trending-search">
                <ul>
                    <?php $this->get_item_trending_search(); ?>
                </ul>
            </div>
        <?php
    }
    public function get_item_trending_search() {
        $settings = $this->get_settings_for_display();
        extract($settings);
        foreach ( $trending_search as $item ) :
            ?>
                <li><a href="<?php echo home_url() ?>/?s=<?php echo urlencode($item['trending_search_item']);?>&post_type=<?php echo trim($search_type) ?>"><?php echo trim($item['trending_search_item']);?></a></li>
            <?php
        endforeach;
    }
}
$widgets_manager->register(new Zota_Elementor_Search_Form());

