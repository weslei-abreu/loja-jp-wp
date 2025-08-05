<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Zota_Elementor_Canvas_Menu_Template extends  Zota_Elementor_Widget_Base{
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
        return 'tbay-canvas-menu-template';
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
        return esc_html__( 'Zota Canvas Menu Template', 'zota' );
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
        return 'eicon-nav-menu';
    }

    public function get_categories() {
        return [ 'zota-elements', 'zota-header'];
    }

    protected function get_html_wrapper_class() {
		return 'w-auto elementor-widget-' . $this->get_name();
	}

    protected function register_controls() {

        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'General', 'zota' ),
            ]
        );
        
        $this->add_control(
            'icon_menu_canvas',
            [
                'label' => esc_html__( 'Choose Icon', 'zota' ),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'linear-icon-text-align-right',
					'library' => 'linear-icons',
                ],  
            ]
        );
        
        $templates = Elementor\Plugin::instance()->templates_manager->get_source( 'local' )->get_items();

        if ( empty( $templates ) ) {

            $this->add_control(
                'no_templates',
                [
                    'label' => false,
                    'type' => Controls_Manager::RAW_HTML,
                    'raw'             => sprintf(__('<strong>There are no templates in your site.</strong><br>Go to the <a href="%s" target="_blank">Templates screen</a> to create one.', 'zota'), admin_url('edit.php?post_type=elementor_library&tabs_group=library')),
                ]
            );

            return;
        }

        $options = [
            '0' => '— ' . esc_html__( 'Select', 'zota' ) . ' —',
        ];

        $types = [];

        foreach ( $templates as $template ) {
            $options[ $template['template_id'] ] = $template['title'] . ' (' . $template['type'] . ')';
            $types[ $template['template_id'] ] = $template['type'];
        }

        $this->add_control(
            'template_id',
            [
                'label' => esc_html__( 'Choose Template', 'zota' ),
                'type' => Controls_Manager::SELECT,
                'default' => '0',
                'options' => $options,
                'types' => $types,
                'label_block'  => 'true',
            ]
        );
        
        $this->add_responsive_control(
            'canvas_menu_align',
            [
                'label' => esc_html__('Content Align','zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left','zota'),
                        'icon' => 'fas fa-align-left'
                    ],
                    'right' => [
                        'title' => esc_html__('Right','zota'),
                        'icon' => 'fas fa-align-right'
                    ],   
                ],
                'default' => 'left',
                'prefix_class' => 'menu-canvas-',
            ]
        );

        $this->end_controls_section();
        $this->register_style_canvas_menu();
    }
    public function register_style_canvas_menu() {
        $this->start_controls_section(
            'section_style_icon',
            [
                'label' => esc_html__('General', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'icon_menu_size',
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
					'{{WRAPPER}} .btn-canvas-menu > i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
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
                    '{{WRAPPER}} .btn-canvas-menu > i'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn-canvas-menu > i'    => 'background-color: {{VALUE}}',
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
                    '{{WRAPPER}} .btn-canvas-menu > i:hover'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'hover_bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn-canvas-menu > i:hover'    => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        
        $this->end_controls_section();
    }
    public function render_content_template() {
        $template_id = $this->get_settings( 'template_id' );

        if( empty($template_id) ) return; 
        ?>
        <div class="canvas-menu-content">
        <div class="sidebar-header"><a href="javascript:void(0);" class="close-canvas-menu"><i class="zmdi zmdi-close"></i></a></div>
        <div class="canvas-content">
        <?php
        echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, zota_get_elementor_css_print_method() );
        echo "</div></div>";
            
    }
    public function render_canvas_menu() {
        $settings = $this->get_settings_for_display();
        extract( $settings );
        ?>
        <div class="canvas-menu-sidebar">
            <a href="javascript:void(0);" class="btn-canvas-menu">
                <?php $this->render_item_icon($icon_menu_canvas) ?>
            </a>
            
            <?php $this->render_content_template(); ?> 
        </div><?php

    }
}
$widgets_manager->register(new Zota_Elementor_Canvas_Menu_Template());
