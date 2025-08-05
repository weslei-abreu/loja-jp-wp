<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Zota_Elementor_Testimonials extends  Zota_Elementor_Carousel_Base{
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
        return 'tbay-testimonials';
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
        return esc_html__( 'Zota Testimonials', 'zota' );
    }

    public function get_script_depends() {
        return [ 'zota-custom-slick', 'slick', 'before-after-image' ];
    } 

    public function get_style_depends() {
        return [ 'before-after-image' ];
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
        return 'eicon-testimonial';
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
        $this->add_control(
            'testimonials_align',
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
                    '{{WRAPPER}} .item .testimonials-body'  => 'text-align: {{VALUE}} !important',
                ]
            ]
        );  

        $repeater = $this->register_testimonials_repeater();

        $this->add_control(
            'testimonials',
            [
                'label' => esc_html__( 'Testimonials Items', 'zota' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => $this->register_set_testimonial_default(),
                'testimonials_field' => '{{{ testimonials_image }}}',
            ]
        );    

        $this->end_controls_section();

        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);
        $this->register_section_style_testimonial();

    }

    private function register_testimonials_repeater() {
        $repeater = new \Elementor\Repeater();
        
        $repeater->add_control (
            'testimonial_title', 
            [
                'label' => esc_html__( 'Title', 'zota' ),
                'type' => Controls_Manager::TEXT,
            ]
        );
        $repeater->add_control (
            'testimonial_subtitle', 
            [
                'label' => esc_html__( 'Subtitle', 'zota' ),
                'type' => Controls_Manager::TEXT,
            ]
        );
        $repeater->add_control (
            'testimonial_excerpt', 
            [
                'label' => esc_html__( 'Excerpt', 'zota' ),
                'type' => Controls_Manager::TEXTAREA,
            ]
        );

        $repeater->add_control (
            'testimonial_name', 
            [
                'label' => esc_html__( 'Name', 'zota' ),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control (
            'testimonial_icon', 
            [
                'label' => esc_html__( 'Choose Icon', 'zota' ),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'tb-icon tb-icon-quote',
					'library' => 'tbay-custom',
                ],  
            ]
        );
        

        $skin = zota_tbay_get_theme();
        if( $skin === 'beauty' ) {
            $repeater->add_control(
                'image_before',
                [
                    'label' => esc_html__('Image Before', 'zota'),
                    'type' => Controls_Manager::MEDIA,
                ]
            );
            $repeater->add_control(
                'image_after',
                [
                    'label' => esc_html__('Image After', 'zota'),
                    'type' => Controls_Manager::MEDIA,
                ]
            );
        }

        return $repeater;
    }

    private function register_set_testimonial_default() {
        $defaults = [
            [
                'testimonial_title' => esc_html__( 'Title 1', 'zota' ),
                'testimonial_subtitle' => esc_html__( 'Subtitle 1', 'zota' ),
                'testimonial_excerpt' => esc_html__( 'Lorem ipsum dolor sit amet, in mel unum delicatissimi conclusionemque', 'zota' ),
                'testimonial_name' => esc_html__('Name 1', 'zota'),
            ],
            [
                'testimonial_title' => esc_html__( 'Title 2', 'zota' ),
                'testimonial_subtitle' => esc_html__( 'Subtitle 2', 'zota' ),
                'testimonial_excerpt' => esc_html__( 'Lorem ipsum dolor sit amet, in mel unum delicatissimi conclusionemque', 'zota' ),
                'testimonial_name' => esc_html__('Name 2', 'zota'),
            ],
            [
                'testimonial_title' => esc_html__( 'Title 3', 'zota' ),
                'testimonial_subtitle' => esc_html__( 'Subtitle 3', 'zota' ),
                'testimonial_excerpt' => esc_html__( 'Lorem ipsum dolor sit amet, in mel unum delicatissimi conclusionemque', 'zota' ),
                'testimonial_name' => esc_html__('Name 3', 'zota'),
            ],
            [
                'testimonial_title' => esc_html__( 'Title 4', 'zota' ),
                'testimonial_subtitle' => esc_html__( 'Subtitle 4', 'zota' ),
                'testimonial_excerpt' => esc_html__( 'Lorem ipsum dolor sit amet, in mel unum delicatissimi conclusionemque', 'zota' ),
                'testimonial_name' => esc_html__('Name 4', 'zota'),
            ],
            
        ];

        return $defaults;
    }

    protected function render_item( $item ) {
        ?> 
            <div class="testimonials-body"> 
                <?php $this->render_item_subtitle( $item ); ?>
                <?php $this->render_item_title( $item ); ?>
                <?php $this->render_item_excerpt( $item ); ?>
                <?php $this->render_item_name( $item ); ?>

                <?php if( !empty ($item['testimonial_icon']) ) {
                    ?>
                        <div class="testimonial-icon"> 
                            <?php $this->render_item_icon($item['testimonial_icon']); ?>
                        </div>
                    <?php
                }
                ?> 
            </div>
        <?php 

        $skin = zota_tbay_get_theme();

        if( $skin === 'beauty' ) {
            $this->render_image_beauty($item);
        }
        
    }    
    
    
    private function render_image_beauty( $item ) {

        $image_before =  $item['image_before'];
        $image_after =  $item['image_after'];
        if ( !empty($image_before['url']) && !empty($image_after['url']) ) {
            ?>
            <div class="zota-before-after-wrapper">
                <div class="beforeafterdefault zota-before-after">
                    <div data-type="data-type-image">
                        <div data-type="before"><?php echo wp_get_attachment_image($image_before['id'], 'full'); ?></div>
                        <div data-type="after"><?php echo wp_get_attachment_image($image_after['id'], 'full'); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
        
    }

    private function render_item_title( $item ) {
        $testimonial_title  = $item['testimonial_title'];
        if(isset($testimonial_title) && !empty($testimonial_title)) {
            ?>
                <span class="testimonial-title"><?php echo trim($testimonial_title) ?></span>
            <?php
        }
    }

    private function render_item_subtitle( $item ) {
        $testimonial_subtitle  = $item['testimonial_subtitle'];
        if(isset($testimonial_subtitle) && !empty($testimonial_subtitle)) {
            ?>
                <span class="testimonial-subtitle"><?php echo trim($testimonial_subtitle) ?></span>
            <?php
        }
    }

    private function render_item_excerpt( $item ) {
        $testimonial_excerpt  = $item['testimonial_excerpt'];

        if(isset($testimonial_excerpt) && !empty($testimonial_excerpt)) {
            ?>
                <span class="excerpt"><?php echo trim($testimonial_excerpt) ?></span>
            <?php
        }
    }
    private function render_item_name( $item ) {
        $testimonial_name  = $item['testimonial_name'];
        if(isset($testimonial_name) && !empty($testimonial_name)) {
            ?>
                <span class="testimonial-name"><?php echo trim($testimonial_name) ?></span>
            <?php
        }
    }

    protected function register_section_style_testimonial() {
        $this->start_controls_section(
            'section_style_testimonial',
            [
                'label' => esc_html__('Style', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'advanced_testimonial_title',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_testimonial_title',
                'selector' => '{{WRAPPER}} .testimonial-title',
            ]
        );
        $this->add_control(
            'color_testimonial_title',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .testimonial-title'    => 'color: {{VALUE}}',
                ],
                'separator'    => 'after',
            ]
        );   


        $this->add_control(
            'advanced_testimonial_subtitle',
            [
                'label' => esc_html__('SubTitle', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_testimonial_subtitle',
                'selector' => '{{WRAPPER}} .testimonial-subtitle',
            ]
        );
        $this->add_control(
            'color_testimonial_subtitle',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .testimonial-subtitle'    => 'color: {{VALUE}}',
                ],
                'separator'    => 'after',
            ]
        );   
        

        $this->add_control(
            'advanced_testimonial_excerpt',
            [
                'label' => esc_html__('Excerpt', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_testimonial_excerpt',
                'selector' => '{{WRAPPER}} .excerpt',
            ]
        );
        $this->add_control(
            'color_testimonial_excerpt',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .excerpt'    => 'color: {{VALUE}}',
                ],
                'separator'    => 'after',
            ]
        );   


        $this->add_control(
            'advanced_testimonial_name',
            [
                'label' => esc_html__('Name', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_testimonial_name',
                'selector' => '{{WRAPPER}} .testimonial-name',
            ]
        );
        $this->add_control(
            'color_testimonial-name',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .testimonial-name'    => 'color: {{VALUE}}',
                ],
                'separator'    => 'after',
            ]
        );   

        $this->add_control(
            'advanced_testimonial_icon',
            [
                'label' => esc_html__('Icon', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_testimonial_icon',
                'selector' => '{{WRAPPER}} .testimonial-icon i',
            ]
        );
        $this->add_control(
            'color_testimonial_icon',
            [
                'label'     => esc_html__('Color', 'zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .testimonial-icon i'    => 'color: {{VALUE}}',
                ],
            ]
        );   

        $this->end_controls_section();
    }
}
$widgets_manager->register(new Zota_Elementor_Testimonials());
