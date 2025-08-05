<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Utils;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Zota_Elementor_Our_Team extends  Zota_Elementor_Carousel_Base{
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
        return 'tbay-our-team';
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
        return esc_html__( 'Zota Our Team', 'zota' );
    }

    public function get_script_depends() {
        return [ 'zota-custom-slick', 'slick' ];
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
        return 'eicon-person';
    }

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {
        $this->register_controls_heading();

        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'General', 'zota' ),
            ]
        );
 
        $this->add_control(
            'layout_type',
            [
                'label'     => esc_html__('Layout Type', 'zota'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'grid',
                'options'   => [
                    'grid'      => esc_html__('Grid', 'zota'), 
                    'carousel'  => esc_html__('Carousel', 'zota'), 
                ],
            ]
        );  
        
        
        $repeater = $this->register_our_team_repeater();

        $this->add_control(
            'our_team',
                [
                'label' => esc_html__( 'Our Team Items', 'zota' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => $this->register_set_our_team_default(),
                'max_items' => 20,
                'our_team_field' => '{{{ our_team_image }}}',
            ]
        );

        

        $this->end_controls_section();
        $this->style_our_team();
        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);

    }
    protected function style_our_team() {
        $this->start_controls_section(
            'section_style_our_team',
            [
                'label' => esc_html__( 'Style', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'our_team_align',
            [
                'label' => esc_html__('Align','zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left','zota'),
                        'icon' => 'fas fa-align-left'
                    ],
                    'center' => [
                        'title' => esc_html__('Center','zota'),
                        'icon' => 'fas fa-align-center'
                    ],
                    'right' => [
                        'title' => esc_html__('Right','zota'),
                        'icon' => 'fas fa-align-right'
                    ],   
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .info'  => 'text-align: {{VALUE}}',
                ]
            ]
        );  
        $this->add_responsive_control(
			'our_team_padding',
			[
				'label' => esc_html__( 'Padding "Name"', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .name-team' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
        );

        $this->add_control(
            'title_our_team_color',
            [
                'label' => esc_html__('Color Name','zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .name-team'    => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'job_our_team_color',
            [
                'label' => esc_html__('Color Job','zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .job'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_our_team_repeater() {
        $repeater = new \Elementor\Repeater();

        $repeater->add_control (
            'our_team_name', 
            [
                'label' => esc_html__( 'Name', 'zota' ),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control (
            'our_team_job', 
            [
                'label' => esc_html__( 'Job', 'zota' ),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control (
            'our_team_image', 
            [
                'label' => esc_html__( 'Choose Image', 'zota' ),
                'type' => Controls_Manager::MEDIA,
            ]
        );

        $repeater->add_control (
            'our_team_link_fb', 
            [
                'label' => esc_html__( 'FaceBook Link', 'zota' ),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        );
        $repeater->add_control (
            'our_team_link_tw', 
            [
                'label' => esc_html__( 'Twitter Link', 'zota' ),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        );
        $repeater->add_control (
            'our_team_link_gg', 
            [
                'label' => esc_html__( 'Goole Plus Link', 'zota' ),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        );
        $repeater->add_control (
            'our_team_link_linkin', 
            [
                'label' => esc_html__( 'Linkin Link', 'zota' ),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        );
        $repeater->add_control (
            'our_team_link_instaram', 
            [
                'label' => esc_html__( 'Instagram Link', 'zota' ),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        );

        return $repeater;
    }

    private function register_set_our_team_default() {
        $defaults = [
            [
                'our_team_name' => esc_html__( 'Name 1', 'zota' ),
                'our_team_job' => esc_html__( 'Job 1', 'zota' ),
                'our_team_image' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'our_team_link_fb' => [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_tw' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_gg' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_linkin' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_instaram' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
            ],
            [
                'our_team_name' => esc_html__( 'Name 2', 'zota' ),
                'our_team_job' => esc_html__( 'Job 2', 'zota' ),
                'our_team_image' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'our_team_link_fb' => [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_tw' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_gg' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_linkin' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_instaram' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
            ],
            [
                'our_team_name' => esc_html__( 'Name 3', 'zota' ),
                'our_team_job' => esc_html__( 'Job 3', 'zota' ),
                'our_team_image' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'our_team_link_fb' => [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_tw' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_gg' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_linkin' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_instaram' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
            ],
            [
                'our_team_name' => esc_html__( 'Name 4', 'zota' ),
                'our_team_job' => esc_html__( 'Job 4', 'zota' ),
                'our_team_image' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'our_team_link_fb' => [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_tw' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_gg' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_linkin' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
                'our_team_link_instaram' =>  [
					'url' => '#',
					'is_external' => true,
					'nofollow' => true,
				],
            ],
        ];

        return $defaults;
    }

    protected function render_item($item, $key_ul) {
        extract($item);
        ?> 
        <div class="inner"> 
           <?php 

                $array_link = [
                    'fb' => 'icon-social-facebook',
                    'tw' => 'icon-social-twitter',
                    'gg' => 'icon-social-google',
                    'linkin' => 'icon-social-linkedin',
                    'instaram' => 'icon-social-instagram'
                ];
           ?>
           
            <?php

                $check_link = (!empty($our_team_link_fb['url']) && isset($our_team_link_fb['url']) || !empty($our_team_link_tw['url']) && isset($our_team_link_tw['url']) || !empty($our_team_link_gg['url']) && isset($our_team_link_gg['url'])||
                !empty($our_team_link_linkin['url']) && isset($our_team_link_linkin['url']) || !empty($our_team_link_instaram['url']) && isset($our_team_link_instaram['url']) );

                if( $check_link || !empty($our_team_image['url']) && isset($our_team_image['url']) ) {
                    ?>
                     <div class="our-team-content">
                        <?php echo $this->get_widget_field_img($item['our_team_image']); ?>
                        <?php 
                            if( $check_link ) {
                                ?>
                                    <ul class="social-link">
                                    <?php 
                                        foreach ($array_link as $key => $value) {
                                            $link = $item['our_team_link_'.$key]['url'];

                                            $link_key = 'link_'. $key_ul .'_' . $key;

                                            if ( ! empty( $link )) {
                                                $this->add_link_attributes( $link_key, $item['our_team_link_'.$key] );
                                            }
                                            ?>
                                            <?php if(!empty($link) && isset($link) ) {
                                                ?>
                                                    <li>
                                                        <a <?php echo $this->get_render_attribute_string( $link_key ); ?> >
                                                            <i class="icons <?php echo esc_attr($value); ?>"></i>
                                                        </a>

                                                    </li>
                                                <?php
                                            } ?>
                                        <?php
                                        }
                                    ?>
                                    </ul>
                                <?php
                            }
                        ?>
                    </div>
                    <?php
                 }
            ?>
           <?php 
                if ( !empty( $our_team_name ) || !empty( $our_team_job ) ) {
                    ?>
                    <div class="info">
                        <?php
                            if( !empty( $our_team_name ) ) {
                                ?><h3 class="name-team"><?php echo trim($our_team_name) ?></h3> <?php
                            }
                        ?>
                        <?php
                            if( !empty( $our_team_job ) ) {
                                ?><p class="job"><?php echo trim($our_team_job) ?></p> <?php
                            }
                        ?>
                    </div>
                    <?php
                }
           ?>
        </div>
        <?php
    }      


}
$widgets_manager->register(new Zota_Elementor_Our_Team());
