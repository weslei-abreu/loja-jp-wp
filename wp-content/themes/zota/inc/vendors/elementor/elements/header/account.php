<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Group_Control_Border;
use Elementor\Controls_Manager;

class Zota_Elementor_Account extends Zota_Elementor_Widget_Base {

    protected $nav_menu_index = 1;

    public function get_name() {
        return 'tbay-account';
    }

    public function get_title() {
        return esc_html__('Zota Account', 'zota');
    }

    public function get_icon() {
        return 'eicon-user-circle-o';
    }

    protected function get_html_wrapper_class() {
		return 'w-auto elementor-widget-' . $this->get_name();
    }
    
    public function get_keywords() {
        return ['account', 'login'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Account', 'zota'),
            ]
        );

        $this->add_control(
            'icon_account',
            [
                'label'              => esc_html__('Icon', 'zota'),
                'type'               => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'tb-icon tb-icon-account',
					'library' => 'tbay-custom',
                ],                
            ]
        );
        
        $this->add_control(
            'show_text_account',
            [
                'label'              => esc_html__('Display Text Account', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'no'        
            ]
        );
        $this->add_control(
            'text_before',
            [
                'label'              => esc_html__('Text Before Login', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'condition'          => [
                    'show_text_account' => 'yes'
                ]     
            ]
        );
        $this->add_control(
            'text_after',
            [
                'label'              => esc_html__('Text After Login', 'zota'),
                'type'               => Controls_Manager::TEXT,
                'condition'          => [
                    'show_text_account' => 'yes'
                ]        
            ]
        );
        $this->add_control(
            'show_sub_account',
            [
                'label'              => esc_html__('Display Sub Menu', 'zota'),
                'type'               => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $menus = $this->get_available_menus();

        if (!empty($menus)) {
            $this->add_control(
                'sub_menu_account',
                [
                    'label'        => esc_html__('Choose Menu', 'zota'),
                    'type'         => Controls_Manager::SELECT,
                    'options'      => $menus,
                    'default'      => array_keys($menus)[0],
                    'save_default' => true,
                    'separator'    => 'after',
                    'condition'    => [
                        'show_sub_account'  => 'yes'
                    ],
                    'description'  => sprintf(__('Go to the <a href="%s" target="_blank">Menus screen</a> to manage your menus.', 'zota'), admin_url('nav-menus.php')),
                ]
            );
        } else {
            $this->add_control(
                'sub_menu_account',
                [
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => sprintf(__('<strong>There are no menus in your site.</strong><br>Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', 'zota'), admin_url('nav-menus.php?action=edit&menu=0')),
                    'separator'       => 'after',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );
        } 
        $this->end_controls_section();
        $this->register_section_style_icon();
        $this->register_section_style_text();
    }
    protected function register_section_style_icon() {
        $this->start_controls_section(
            'section_style_icon',
            [
                'label' => esc_html__('Style Icon', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'icon_account_size',
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
					'{{WRAPPER}} .tbay-login a i' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'padding_icon_account',
            [
                'label'     => esc_html__('Padding Icon Account', 'zota'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .tbay-login a i' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .tbay-login a i'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-login a i'    => 'background-color: {{VALUE}}',
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
                    '{{WRAPPER}} .tbay-login a i:hover'    => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->add_control(
            'hover_bg_icon',
            [
                'label'     => esc_html__('Background Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbay-login a i:hover'    => 'background-color: {{VALUE}}',
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
                    'show_text_account' => 'yes',
                ]
            ]
        );
        $this->add_control(
            'text_account_size',
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
					'{{WRAPPER}} .tbay-login > a span' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_control(
            'text_account_line_height',
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
					'{{WRAPPER}} .tbay-login > a span' => 'line-height: {{SIZE}}{{UNIT}} !important;',
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
                    '{{WRAPPER}} .text-account'    => 'color: {{VALUE}}',
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
                    '{{WRAPPER}} .text-account:hover' => 'color: {{VALUE}}',
                ],
            ]
        );   
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }

    protected function is_user_logged_in() {
		$user = wp_get_current_user();

		return $user->exists();
    }
    
    protected function get_nav_menu_index() {
        return $this->nav_menu_index++;
    }

    public function check_login($show_text_account,$text_after,$text_before) {
        if(is_user_logged_in()) {
            $current_user 	= wp_get_current_user(); 
            $name = $current_user->display_name;
            if(!empty($text_after)) {
                $name = $text_after.','.' '.$name;
            }else {
                $name = esc_html__('Hi,','zota').' '.$name;
            }
        }
        else {
            if(!empty($text_before)) {
                $name = $text_before;
            }else {
                $name = esc_html__('Login Or Register','zota');
            }
        }

        if ($show_text_account === 'yes') {
            ?><span class="text-account"> <?php echo trim($name); ?> </span><?php
        }
    }
    public function render_item_account() {
        $settings = $this->get_settings_for_display();
        extract($settings);

        $this->render_item_icon($icon_account);
        $this->check_login($show_text_account,$text_after,$text_before);
    }

    public function render_sub_menu() {
        $settings = $this->get_settings_for_display();
        extract($settings);
        
        $transient_key = 'zota_menu_account_' . md5($sub_menu_account);
        $menu_output = get_transient($transient_key);

        if (false === $menu_output) {
            ob_start();
            $args = [
                'menu'        => $sub_menu_account,
                'menu_id'     => 'menu-' . $this->get_nav_menu_index(),
                'echo'        => false,
            ];
            $menu_output = wp_nav_menu($args);
            set_transient($transient_key, $menu_output, WEEK_IN_SECONDS);
            ob_end_clean();
        }

        echo $menu_output;
    }
}
$widgets_manager->register(new Zota_Elementor_Account());

