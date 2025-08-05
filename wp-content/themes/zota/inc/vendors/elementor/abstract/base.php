<?php
if (!defined('ABSPATH') || function_exists('Zota_Elementor_Widget_Base') ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

abstract class Zota_Elementor_Widget_Base extends Elementor\Widget_Base {
    protected $cache = [];

	public function get_name_template() {
        return str_replace('tbay-', '', $this->get_name());
    }

    public function get_categories() {
        return [ 'zota-elements' ];
    }

    public function get_name() {
        return 'zota-base';
    }

    /**
	 * Get view template
	 *
	 * @param string $tpl_name
	 */
    protected function get_view_template( $tpl_slug, $tpl_name, $settings = [] ) {
        $transient_key = 'zota_template_' . md5($tpl_slug . '_' . $tpl_name);
        $located = get_transient($transient_key); 

        if (defined('WP_DEBUG') && WP_DEBUG === true || false === $located) {
            $templates = [];
    
            if (!$settings) {
                $settings = $this->get_settings_for_display();
            }
    
            if (!empty($tpl_name)) {
                $tpl_name = trim(str_replace('.php', '', $tpl_name), DIRECTORY_SEPARATOR);
                $templates[] = 'elementor_templates/' . $tpl_slug . '-' . $tpl_name . '.php';
                $templates[] = 'elementor_templates/' . $tpl_slug . '/' . $tpl_name . '.php';
            }
    
            $templates[] = 'elementor_templates/' . $tpl_slug . '.php';
    
            $located = false;
            foreach ($templates as $template) {
                if (file_exists(ZOTA_THEMEROOT . '/' . $template)) {
                    $located = locate_template($template);
                    break;
                }
            }
    
            set_transient($transient_key, $located, MONTH_IN_SECONDS);
        }
    
        if ($located) {
            include $located;
        } else {
            echo sprintf(__('Failed to load template with slug "%s" and name "%s".', 'zota'), $tpl_slug, $tpl_name);
        }
    }

	protected function render() {
        $settings = $this->get_settings_for_display();
        $this->add_render_attribute('wrapper', 'class', 'tbay-element tbay-element-'. $this->get_name_template() );

        $this->get_view_template($this->get_name_template(), '', $settings);
	}
	
    protected function register_controls_heading($condition = array()) {

        $this->start_controls_section(
            'section_heading',
            [
                'label' => esc_html__( 'Heading', 'zota' ),
                'condition' => $condition,
            ]
        );


        $this->register_section_heading_alignment();

        $this->add_control(
            'heading_title',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::TEXT,
            ]
        );


        $this->add_control(
            'heading_title_tag',
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
            'heading_subtitle',
            [
                'label' => esc_html__('Sub Title', 'zota'),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->register_section_heading_icon();


        $this->end_controls_section();

        $this->register_section_styles_title($condition);

        $this->register_section_styles_icon($condition);
        
        $this->register_section_styles_sub_title($condition);
        $this->register_section_styles_content($condition);
    }

    private function register_section_heading_icon() {

        $skin = zota_tbay_get_theme();

        if( $skin !== 'electronics' ) return;

        $default = [
            'value' => 'tb-icon tb-icon-window',
            'library' => 'tbay-custom',    
        ];

        $this->add_control(
            'heading_icon',
            [
                'label' => esc_html__( 'Choose Icon', 'zota' ),
                'type' => Controls_Manager::ICONS,
                'default' => $default,
            ]
        );

    }

    private function register_section_heading_alignment() {

        $this->add_responsive_control(
            'align',
            [
                'label' => esc_html__('Alignment', 'zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('left', 'zota'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('center', 'zota'),
                        'icon' => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('right', 'zota'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );
    }
    


    private function register_section_styles_content($condition) {
        $this->start_controls_section(
            'section_style_heading_content',
            [
                'label' => esc_html__( 'Heading Content', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );

        $this->add_responsive_control(
            'heading_style_margin',
            [
                'label' => esc_html__( 'Margin', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );         

        $this->add_responsive_control(
            'heading_style_padding',
            [
                'label' => esc_html__( 'Padding', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        ); 

        $this->add_control(
            'heading_style_bg',
            [
                'label' => esc_html__( 'Background', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    private function register_section_styles_title($condition) {
        $this->start_controls_section(
            'section_style_heading_title',
            [
                'label' => esc_html__( 'Heading Title', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'heading_title_typography',
                'selector' => '{{WRAPPER}} .heading-tbay-title .title',
            ]
        );

        $this->start_controls_tabs( 'heading_title_tabs' );

        $this->start_controls_tab(
            'heading_title_tab_normal',
            [
                'label' => esc_html__( 'Normal', 'zota' ),
            ]
        );

        $this->add_control(
            'heading_title_color',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title .title' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'heading_title_tab_hover',
            [
                'label' => esc_html__( 'Hover', 'zota' ),
            ]
        );

        $this->add_control(
            'heading_title_color_hover',
            [
                'label' => esc_html__( 'Hover Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}:hover .heading-tbay-title .title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'heading_title_style_padding', 
            [
                'label' => esc_html__( 'Padding', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'separator'    => 'before',
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};', 
                ],
            ]
        ); 

        $this->add_responsive_control(
            'heading_title_bottom_space',
            [
                'label' => esc_html__( 'Spacing', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }     

    private function register_section_styles_sub_title($condition) {

        $this->start_controls_section(
            'section_style_heading_subtitle',
            [
                'label' => esc_html__( 'Heading Sub Title', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'heading_subtitle_typography',
                'selector' => '{{WRAPPER}} .heading-tbay-title .subtitle',
            ]
        );

        $this->start_controls_tabs( 'heading_subtitle_tabs' );

        $this->start_controls_tab(
            'heading_subtitle_tab_normal',
            [
                'label' => esc_html__( 'Normal', 'zota' ),
            ]
        );

        $this->add_control(
            'heading_subtitle_color',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title .subtitle' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'heading_subtitle_tab_hover',
            [
                'label' => esc_html__( 'Hover', 'zota' ),
            ]
        );

        $this->add_control(
            'heading_subtitle_color_hover',
            [
                'label' => esc_html__( 'Hover Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'heading_title!' => ''
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}:hover .heading-tbay-title .subtitle' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'heading_subtitle_bottom_space',
            [
                'label' => esc_html__( 'Spacing', 'zota' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title .subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }     

    private function register_section_styles_icon($condition) {

        $skin = zota_tbay_get_theme();

        if( $skin !== 'electronics' ) return;

        $this->start_controls_section(
            'section_style_heading_icon',
            [
                'label' => esc_html__( 'Heading Icon', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );
        
        $this->add_responsive_control(
            'heading_icon_size',
            [
                'label' => esc_html__('Font Size', 'zota'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 300,
                    ],
                ],
				'default' => [
					'unit' => 'px',
					'size' => 22,
				],
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'heading_icon_line_height',
            [
                'label' => esc_html__('Line Height', 'zota'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title i' => 'line-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_icon_margin',
            [
                'label' => esc_html__( 'Margin', 'zota' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );    

        $this->start_controls_tabs( 'heading_icon_tabs' );

        $this->start_controls_tab(
            'heading_icon_tab_normal',
            [
                'label' => esc_html__( 'Normal', 'zota' ),
            ]
        );

        $this->add_control(
            'heading_icon_color',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .heading-tbay-title i' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'heading_icon_tab_hover',
            [
                'label' => esc_html__( 'Hover', 'zota' ),
            ]
        );

        $this->add_control(
            'heading_icon_color_hover',
            [
                'label' => esc_html__( 'Hover Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'heading_title!' => ''
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}:hover .heading-tbay-title i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
			'heading_icon_position',
			[
				'label' => esc_html__( 'Position', 'zota' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Default', 'zota' ),
					'absolute' => esc_html__( 'Absolute', 'zota' ),
				],  
                'separator'    => 'after',
				'prefix_class' => 'heading-icon-',
				'frontend_available' => true,
			]
		);

        $start = is_rtl() ? esc_html__( 'Right', 'zota' ) : esc_html__( 'Left', 'zota' );
		$end = ! is_rtl() ? esc_html__( 'Right', 'zota' ) : esc_html__( 'Left', 'zota' );

		$this->add_control(
			'heading_icon_offset_orientation_h',
			[
				'label' => esc_html__( 'Horizontal Orientation', 'zota' ),
				'type' => Controls_Manager::CHOOSE,
				'toggle' => false,
				'default' => 'start',
				'options' => [
					'start' => [
						'title' => $start,
						'icon' => 'eicon-h-align-left',
					],
					'end' => [
						'title' => $end,
						'icon' => 'eicon-h-align-right',
					],
				],
				'classes' => 'elementor-control-start-end',
				'render_type' => 'ui',
				'condition' => [
					'heading_icon_position!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'heading_icon_offset_x',
			[
				'label' => esc_html__( 'Offset', 'zota' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -200,
						'max' => 200,
						'step' => 1,
					],
				],
				'default' => [
					'size' => '0',
				],
				'size_units' => [ 'px' ],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}} .title i' => 'left: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}} .title i' => 'right: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'heading_icon_offset_orientation_h!' => 'end',
					'heading_icon_position!' => '',
				],
			]
		); 

		$this->add_responsive_control(
			'heading_icon_offset_x_end',
			[
				'label' => esc_html__( 'Offset', 'zota' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -200,
						'max' => 200,
						'step' => 1,
					],
				],
				'default' => [
					'size' => '0',
				],
				'size_units' => [ 'px' ],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}} .title i' => 'right: {{SIZE}}{{UNIT}}',
					'body.rtl {{WRAPPER}} .title i' => 'left: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'heading_icon_offset_orientation_h' => 'end',
					'heading_icon_position!' => '',
				],
			]
		);

        $this->add_control(
			'heading_icon_offset_orientation_v',
			[
				'label' => esc_html__( 'Vertical Orientation', 'zota' ),
				'type' => Controls_Manager::CHOOSE,
				'toggle' => false,
				'default' => 'start',
				'options' => [
					'start' => [
						'title' => esc_html__( 'Top', 'zota' ),
						'icon' => 'eicon-v-align-top',
					],
					'end' => [
						'title' => esc_html__( 'Bottom', 'zota' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'render_type' => 'ui',
				'condition' => [
					'heading_icon_position!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'heading_icon_offset_y',
			[
				'label' => esc_html__( 'Offset', 'zota' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -200,
						'max' => 200,
						'step' => 1,
					],
				],
				'size_units' => [ 'px' ],
				'default' => [
					'size' => '0',
				],
				'selectors' => [
					'{{WRAPPER}} .title i' => 'top: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'heading_icon_offset_orientation_v!' => 'end',
					'heading_icon_position!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'heading_icon_offset_y_end',
			[
				'label' => esc_html__( 'Offset', 'zota' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -200,
						'max' => 200,
						'step' => 1,
					],
				],
				'size_units' => [ 'px' ],
				'default' => [
					'size' => '0',
				],
				'selectors' => [
					'{{WRAPPER}} .title i' => 'bottom: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'heading_icon_offset_orientation_v' => 'end',
					'heading_icon_position!' => '',
				],
			]
		);


        $this->end_controls_section();
    }     


    /**
     * Get available pages with caching.
     *
     * @return array Page options.
     */
    protected function get_available_pages() {
        $cache_key = 'zota_available_pages';
        if (false === ($pages = get_transient($cache_key))) {
            $pages = wp_list_pluck(get_pages(), 'post_title', 'ID');
            set_transient($cache_key, $pages, WEEK_IN_SECONDS);
        }
        return $pages;
    }

    protected function get_available_on_sale_products() {
        $transient_key = 'zota_on_sale_products';
        $options = get_transient($transient_key);

        if ($options) {
            return $options;
        }

        $product_ids_on_sale = wc_get_product_ids_on_sale();
        if (empty($product_ids_on_sale)) {
            return [];
        }

        $product_ids_on_sale[] = 0;

        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => apply_filters('zota_on_sale_products_limit', 100),
            'post__in'       => $product_ids_on_sale,
            'no_found_rows'  => true,
        ];

        $query = new WP_Query($args);
        $options = wp_list_pluck($query->posts, 'post_title', 'ID');
        wp_reset_postdata();
        
        set_transient($transient_key, $options, WEEK_IN_SECONDS);

        return $options;
    }

    /**
     * Get available menus with caching and WPML support.
     *
     * @return array Menu options.
     */
    protected function get_available_menus() {
        return zota_wpml_is_activated() ? $this->get_available_menus_wpml() : $this->get_available_menus_default();
    }
    
    /**
     * Get available menus without WPML with caching.
     *
     * @return array Menu options.
     */
    protected function get_available_menus_default() {
        $cache_key = 'zota_available_menus';
        if (false === ($options = get_transient($cache_key))) {
            $menus = wp_get_nav_menus();
            $options = $menus ? wp_list_pluck($menus, 'name', 'slug') : [];
            set_transient($cache_key, $options, WEEK_IN_SECONDS);
        }
        return $options;
    }

        /**
     * Get available menus with WPML and caching.
     *
     * @return array Menu options.
     */
    protected function get_available_menus_wpml() {
        global $sitepress;
        $cache_key = 'zota_menus_wpml_' . apply_filters('wpml_current_language', null);
        if (false === ($options = get_transient($cache_key))) {
            $menus = wp_get_nav_menus();
            $options = [];
            $current_lang = apply_filters('wpml_current_language', null);

            foreach ($menus as $menu) {
                $menu_details = $sitepress->get_element_language_details($menu->term_taxonomy_id, 'tax_nav_menu');
                if (isset($menu_details->language_code) && $menu_details->language_code === $current_lang) {
                    $options[zota_get_transliterate($menu->slug)] = $menu->name;
                }
            }
            set_transient($cache_key, $options, HOUR_IN_SECONDS);
        }
        return $options;
    }
	
	public function render_element_heading() {
        $skin = zota_tbay_get_theme();
        
        if( $skin === 'hand-made' && $skin === 'auto-part' ) {
            return $this->render_element_heading_reverse();
        } else {
            return $this->render_element_heading_base();
        }
    }

    private function render_element_heading_base() {
        $heading_icon = $heading_title = $heading_title_tag = $heading_subtitle = '';
        $settings = $this->get_settings_for_display();
        extract( $settings );

        if( !empty($heading_subtitle) || !empty($heading_title) ) : ?>
			<<?php echo trim($heading_title_tag); ?> class="heading-tbay-title">
				<?php if( !empty($heading_title) ) : ?>
					<span class="title"><?php $this->render_item_icon($heading_icon); ?><?php echo trim($heading_title); ?></span>
				<?php endif; ?>	    	
				<?php if( !empty($heading_subtitle) ) : ?>
					<span class="subtitle"><?php echo trim($heading_subtitle); ?></span>
				<?php endif; ?>
			</<?php echo trim($heading_title_tag); ?>>
		<?php endif;
    }

    private function render_element_heading_reverse() {
        $heading_icon = $heading_title = $heading_title_tag = $heading_subtitle = '';
        $settings = $this->get_settings_for_display();
        extract( $settings );

        if( !empty($heading_subtitle) || !empty($heading_title) ) : ?>
			<<?php echo trim($heading_title_tag); ?> class="heading-tbay-title">
                <?php if( !empty($heading_subtitle) ) : ?>
					<span class="subtitle"><?php echo trim($heading_subtitle); ?></span>
				<?php endif; ?>
                
				<?php if( !empty($heading_title) ) : ?>
					<span class="title"><?php $this->render_item_icon($heading_icon); ?><?php echo trim($heading_title); ?></span>
				<?php endif; ?>	    	
			</<?php echo trim($heading_title_tag); ?>>
		<?php endif;
    }
      

    protected function get_template_product() {
        return apply_filters( 'zota_get_template_product', 'inner' );
    }

    protected function get_product_type() {
        $type = [
            'newest' => esc_html__('Newest Products', 'zota'),
            'on_sale' => esc_html__('On Sale Products', 'zota'),
            'best_selling' => esc_html__('Best Selling', 'zota'),
            'top_rated' => esc_html__('Top Rated', 'zota'),
            'featured' => esc_html__('Featured Product', 'zota'),
            'random_product' => esc_html__('Random Product', 'zota'),
        ];

        return apply_filters( 'zota_woocommerce_product_type', $type);
    }

    protected function get_title_product_type($key) {
        $array = $this->get_product_type();

        return $array[$key];
    }

    /**
     * Get product categories with caching.
     *
     * @param int $number Number of categories to retrieve.
     * @return array Category options.
     */
    protected function get_product_categories() {
        $cache_key = 'zota_product_categories_all';
        $categories = get_transient($cache_key);
        if (false === $categories ) {
            $args = [
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
            ];

            $terms = get_terms($args);
            $categories = is_wp_error($terms) ? [] : array_reduce($terms, function ($result, $term) {
                $result[zota_get_transliterate($term->slug)] = sprintf('%s (%d)', $term->name, $term->count);
                return $result;
            }, []);
            set_transient($cache_key, $categories, WEEK_IN_SECONDS);
        }
        return $categories;
    }

        /**
     * Get category term object with caching
     *
     * @param string $category_slug Category slug
     * @return object|false
     */
    protected function get_category_term($category_slug) {
        $cache_key = 'product_cat_' . md5($category_slug);
        $term = get_transient($cache_key);

        if (false === $term) {
            $term = get_term_by('slug', $category_slug, 'product_cat');
            if (is_object($term)) {
                set_transient($cache_key, $term, WEEK_IN_SECONDS);
            }
        }

        return $term;
    }

    protected function get_cat_operator() {
        $operator = [
            'AND' => esc_html__('AND', 'zota'),
            'IN' => esc_html__('IN', 'zota'),
            'NOT IN' => esc_html__('NOT IN', 'zota'),
        ];

        return apply_filters( 'zota_woocommerce_cat_operator', $operator);
    }

    protected function get_woo_order_by() { 
        $oder_by = [
            'date' => esc_html__('Date', 'zota'),
            'title' => esc_html__('Title', 'zota'),
            'id' => esc_html__('ID', 'zota'),
            'price' => esc_html__('Price', 'zota'),
            'popularity' => esc_html__('Popularity', 'zota'),
            'rating' => esc_html__('Rating', 'zota'),
            'rand' => esc_html__('Random', 'zota'),
            'menu_order' => esc_html__('Menu Order', 'zota'),
        ];

        return apply_filters( 'zota_woocommerce_oder_by', $oder_by);
    }

    protected function get_woo_order() {
        $order = [
            'asc' => esc_html__('ASC', 'zota'), 
            'desc' => esc_html__('DESC', 'zota'),
        ];

        return apply_filters( 'zota_woocommerce_order', $order);
    }

    protected function register_woocommerce_order() {
        $this->add_control(
            'orderby',
            [
                'label' => esc_html__('Order By', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => $this->get_woo_order_by(),
                'conditions' => [
					'relation' => 'AND',
					'terms' => [
						[
							'name' => 'product_type',
							'operator' => '!==',
							'value' => 'top_rated',
						],
						[
							'name' => 'product_type',
							'operator' => '!==',
							'value' => 'random_product',
						],
						[
							'name' => 'product_type',
							'operator' => '!==',
							'value' => 'best_selling',
						],
					],
				],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__('Order', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'asc',
                'options' => $this->get_woo_order(),
                'conditions' => [
					'relation' => 'AND',
					'terms' => [
						[
							'name' => 'product_type',
							'operator' => '!==',
							'value' => 'top_rated',
						],
						[
							'name' => 'product_type',
							'operator' => '!==',
							'value' => 'random_product',
						],
						[
							'name' => 'product_type',
							'operator' => '!==',
							'value' => 'best_selling',
						],
					],
				],
            ]
        );
    }

    protected function register_woocommerce_categories_operator() {
        $categories = $this->get_product_categories();

        $this->add_control(
            'categories', 
            [
                'label' => esc_html__('Categories', 'zota'),
                'type' => Controls_Manager::SELECT2, 
                'default'   => array_keys($categories)[0],
                'options'   => $categories,   
                'label_block' => true,
                'multiple' => true,
            ]
        );

        $this->add_control(
            'cat_operator',
            [
                'label' => esc_html__('Category Operator', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'IN',
                'options' => $this->get_cat_operator(),
                'condition' => [
                    'categories!' => ''
                ],
            ]
        );
    }

    protected function get_woocommerce_tags() {
        $cache_key = 'zota_woocommerce_tags';
        $tags = array();
    
        $cached_tags = get_transient($cache_key);
        if ($cached_tags !== false) {
            return $cached_tags; 
        }
    
        if (!class_exists('WooCommerce')) {
            return $tags;
        }
    
        $args = array(
            'taxonomy'   => 'product_tag',
            'hide_empty' => false,
            'order'      => 'ASC',
            'orderby'    => 'name',
            'number' => apply_filters('zota_woocommerce_products_per_page', 100),
        );
    
        $product_tags = get_terms($args);
    
        if (is_wp_error($product_tags) || empty($product_tags)) {
            set_transient($cache_key, $tags, DAY_IN_SECONDS); 
            return $tags;
        }
    
        foreach ($product_tags as $tag) {
            $tags[$tag->slug] = $tag->name . ' (' . $tag->count . ')';
        }
    
        set_transient($cache_key, $tags, DAY_IN_SECONDS); 
    
        return $tags;
    }

    protected function settings_carousel($settings) {
        $column_tablet  = ( !empty($settings['column_tablet']) ) ? $settings['column_tablet'] : 3;
        $column_mobile  = ( !empty($settings['column_mobile']) ) ? $settings['column_mobile'] : 2;
        $rows           = ( !empty($settings['rows']) ) ? $settings['rows'] : 1;

        $this->add_render_attribute('row', 'class', ['owl-carousel', 'scroll-init']); 
        $this->add_render_attribute('row', 'data-carousel', 'owl');

        $this->add_render_attribute('row', 'data-items', $settings['column']);
        $this->add_render_attribute('row', 'data-desktopslick', $settings['col_desktop']);
        $this->add_render_attribute('row', 'data-desktopsmallslick', $settings['col_desktopsmall']);
        $this->add_render_attribute('row', 'data-tabletslick', $column_tablet);
        $this->add_render_attribute('row', 'data-landscapeslick', $settings['col_landscape']);
        $this->add_render_attribute('row', 'data-mobileslick', $column_mobile);
        $this->add_render_attribute('row', 'data-rows', $rows);

        $this->add_render_attribute('row', 'data-speed', $settings['speed']  ); 

        $this->add_render_attribute('row', 'data-nav', $settings['navigation'] === 'yes' ? true : false);  
        $this->add_render_attribute('row', 'data-pagination', $settings['pagination'] === 'yes' ? true : false);  
        $this->add_render_attribute('row', 'data-loop', $settings['loop'] === 'yes' ? true : false);  

        if( !empty($settings['autospeed']) ) {
            $this->add_render_attribute('row', 'data-autospeed', $settings['autospeed']  );  
        }
  
        $this->add_render_attribute('row', 'data-auto', $settings['auto'] === 'yes' ? true : false);  
        $this->add_render_attribute('row', 'data-unslick', $settings['disable_mobile'] === 'yes' ? true : false);  
    } 

    protected function settings_responsive($settings) { 

        /*Add class reponsive grid*/
        $this->add_render_attribute(
            'row',
            [
                'class' => [ 'row', 'grid' ],
                'data-xlgdesktop' =>  $settings['column'],
                'data-desktop' =>  $settings['col_desktop'],
                'data-desktopsmall' =>  $settings['col_desktopsmall'],
            ]
        );


        $column_tablet = ( !empty($settings['column_tablet']) ) ? $settings['column_tablet'] : 3;
        $column_mobile = ( !empty($settings['column_mobile']) ) ? $settings['column_mobile'] : 2;

        $this->add_render_attribute('row', 'data-tablet', $column_tablet);
        $this->add_render_attribute('row', 'data-landscape', $settings['col_landscape']);
        $this->add_render_attribute('row', 'data-mobile', $column_mobile);
    } 

    protected function settings_layout() {
        $settings = $this->get_settings_for_display();
        extract( $settings );

        if( !isset($layout_type) ) return;

        $this->add_render_attribute('row', 'class', $this->get_name_template());

        if( isset($rows) && !empty($rows) ) {
            $this->add_render_attribute( 'row', 'class', 'rows-'. $rows);
        }

        if($layout_type === 'carousel') { 
            $this->settings_carousel($settings);    
        }else{
            $this->settings_responsive($settings);
        }
    }
    
    protected function get_widget_field_img( $image ) {
        $image_id   = $image['id'];
        $img  = '';

        if( !empty($image_id) ) {
            $img = wp_get_attachment_image($image_id, 'full');    
        } else if( !empty($image['url']) ) {
            $img = '<img src="'. $image['url'] .'">';
        }

        return $img;
    }

    protected function render_item_icon($selected_icon) {
        if ( ! isset( $selected_icon['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$selected_icon['icon'] = 'fa fa-star';
        }
        $has_icon = ! empty( $selected_icon['icon'] );

        if ( $has_icon ) {
			$this->add_render_attribute( 'i', 'class', $selected_icon['icon'] );
			$this->add_render_attribute( 'i', 'aria-hidden', 'true' );
        }
        
        if ( ! $has_icon && ! empty( $selected_icon['value'] ) ) {
			$has_icon = true;
		}
		$migrated = isset( $selected_icon['__fa4_migrated']['selected_icon'] );
        $is_new = ! isset( $selected_icon['icon'] ) && Icons_Manager::is_migration_allowed();
        
        Icons_Manager::enqueue_shim();

        if( !$has_icon ) return;  
        
        if ( $is_new || $migrated ) {
            Icons_Manager::render_icon( $selected_icon, [ 'aria-hidden' => 'true' ] );
        } elseif ( ! empty( $selected_icon['icon'] ) ) {
            ?><i <?php echo $this->get_render_attribute_string( 'i' ); ?>></i><?php
        }
    }
    

}

