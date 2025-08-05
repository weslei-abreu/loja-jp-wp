<?php
if (!defined('ABSPATH') || function_exists('Zota_Elementor_Carousel_Base') ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;

abstract class Zota_Elementor_Carousel_Base extends Zota_Elementor_Responsive_Base {

    public function get_name() {
        return 'tbay-carousel';
    }

    private function get_rows() {
        $transient_key = 'zota_elementor_rows';
        $rows = get_transient($transient_key);

        if (false === $rows) {
            $rows = apply_filters('zota_admin_elementor_rows', [
                1 => 1,
                2 => 2,
                3 => 3,
            ]);
            set_transient($transient_key, $rows, WEEK_IN_SECONDS);
        }

        return $rows;
    }

    protected function add_control_carousel($condition = array()) {
        $this->register_section_carousel_options($condition);
        $this->register_section_style_navigation($condition);
        $this->register_section_style_pagination($condition);
    }

    private function register_section_carousel_options($condition = []) {
        $this->start_controls_section('section_carousel_options', [
            'label' => esc_html__('Carousel Options', 'zota'),
            'condition' => $condition,
        ]);

        $controls = [
            'rows' => ['type' => Controls_Manager::SELECT, 'default' => 1, 'options' => $this->get_rows()],
            'speed' => ['type' => Controls_Manager::NUMBER, 'default' => 500, 'description' => esc_html__('Slide/Fade animation speed', 'zota')],
            'navigation' => ['type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => esc_html__('Show/hidden Navigation', 'zota')],
            'pagination' => ['type' => Controls_Manager::SWITCHER, 'default' => '', 'description' => esc_html__('Show/hidden Pagination', 'zota')],
            'loop' => ['type' => Controls_Manager::SWITCHER, 'default' => '', 'description' => esc_html__('Show/hidden Infinite Loop', 'zota')],
            'auto' => ['type' => Controls_Manager::SWITCHER, 'default' => 'no', 'description' => esc_html__('Show/hidden Autoplay', 'zota')],
            'autospeed' => ['type' => Controls_Manager::NUMBER, 'default' => 1000, 'condition' => ['auto' => 'yes']],
            'disable_mobile' => ['type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => esc_html__('To help load faster in mobile', 'zota')],
        ];

        foreach ($controls as $id => $args) {
            $this->add_control($id, array_merge(['label' => ucwords(str_replace('_', ' ', $id))], $args));
        }

        $this->end_controls_section();
    }

    private function register_section_style_navigation( $condition = array() ) {
        $condition['navigation'] = 'yes';

        $this->start_controls_section(
            'section_style_navigation',
            [
                'label' => esc_html__( 'Navigation', 'zota' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );

        $this->add_responsive_control(
            'arrows_width', 
            [
                'label' => esc_html__( 'Width', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow' => 'width: {{SIZE}}{{UNIT}}',
                ], 
                'condition' => [
                    'navigation' => [ 'yes' ],
                ],
            ]
        );        

        $this->add_responsive_control(
            'arrows_height', 
            [
                'label' => esc_html__( 'Height', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 80,
                    ],   
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow i' => 'line-height: {{SIZE}}{{UNIT}};',
                ], 
                'condition' => [
                    'navigation' => [ 'yes' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'arrows_size_icon',
            [
                'label' => esc_html__( 'Size Icon', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow i' => 'font-size: {{SIZE}}{{UNIT}};',
                ], 
                'condition' => [
                    'navigation' => [ 'yes' ],
                ],
            ]
        );        

        $this->add_responsive_control(
            'arrows_size_position',
            [
                'label' => esc_html__( 'Position', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => -100,
                        'max' => 100, 
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow.slick-prev' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow.slick-next' => 'margin-right: {{SIZE}}{{UNIT}};',
                ], 
                'condition' => [
                    'navigation' => [ 'yes' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'arrows_radius',
            [
                'label' => esc_html__( 'Border Radius', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS, 
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'arrows_text_color',
            [
                'label' => esc_html__( 'Text Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow i' => 'color: {{VALUE}};',
                ],
            ]
        );        

        $this->add_control(
            'arrows_text_color_hover',
            [
                'label' => esc_html__( 'Text Color Hover', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-arrow:hover i' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_section();
    }

    private function register_section_style_pagination( $condition = array() ) {
        $condition['pagination'] = 'yes';

        $this->start_controls_section(
            'section_style_pagination',
            [
                'label' => esc_html__( 'Pagination', 'zota' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );

        $this->add_responsive_control(
            'pagination_width',
            [
                'label' => esc_html__( 'Width', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-dots li button' => 'width: {{SIZE}}{{UNIT}}',
                ], 
            ]
        );

        $this->add_responsive_control(
            'pagination_height',
            [
                'label' => esc_html__( 'Height', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-dots li button' => 'height: {{SIZE}}{{UNIT}};',
                ], 
            ]
        );
        $this->add_responsive_control(
            'pagination_position',
            [
                'label' => esc_html__( 'Position', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => -100,
                        'max' => 150, 
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-dots' => 'margin-top: {{SIZE}}{{UNIT}};',
                ], 
            ]
        );
        $this->add_responsive_control(
            'pagination_radius',
            [
                'label' => esc_html__( 'Border Radius', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS, 
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .tbay-element .owl-carousel .slick-dots li button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }
}