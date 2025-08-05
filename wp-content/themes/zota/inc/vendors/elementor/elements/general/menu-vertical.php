<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;


class Zota_Elementor_Menu_Vertical extends Zota_Elementor_Widget_Base {

    public function get_name() {
        return 'tbay-menu-vertical';
    }

    public function get_title() {
        return esc_html__('Zota Menu Vertical', 'zota');
    }

    public function get_icon() {
        return 'eicon-nav-menu';
    }

    public function on_export($element) {
        unset($element['settings']['menu']);

        return $element;
    }

    protected function register_controls() {
        $this->register_controls_heading();
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('General', 'zota'),
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
                    'description'  => esc_html__('Note does not apply to Mega Menu.', 'zota'),
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
        $this->end_controls_section();
        $this->style_menu_vertical();
    }
    protected function style_menu_vertical() {
        $this->start_controls_section(
            'section_style_menu_vertical',
            [
                'label' => esc_html__( 'Style Menu Vertical', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

       
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'menu_vertical_typography',
                'selector' => '{{WRAPPER}} .menu-vertical > li > a',
            ]
        );

       

        $this->start_controls_tabs( 'menu_vertical_tabs' );

        $this->start_controls_tab(
            'menu_vertical_tab_normal',
            [
                'label' => esc_html__( 'Normal', 'zota' ),
            ]
        );

        $this->add_control(
            'menu_vertical_color',
            [
                'label' => esc_html__( 'Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .menu-vertical > li > a' => 'color: {{VALUE}} !important;',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'menu_vertical_tab_hover',
            [
                'label' => esc_html__( 'Hover', 'zota' ),
            ]
        );

        $this->add_control(
            'menu_vertical_color_hover',
            [
                'label' => esc_html__( 'Hover Color', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}:hover .menu-vertical > li > a:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
        
    }

    
}
$widgets_manager->register(new Zota_Elementor_Menu_Vertical());

