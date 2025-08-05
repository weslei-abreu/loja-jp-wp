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
class Zota_Elementor_Features extends  Zota_Elementor_Carousel_Base{
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
        return 'tbay-features';
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
        return esc_html__( 'Zota Features', 'zota' );
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
        return 'eicon-star-o';
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
        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'General', 'zota' ),
            ]
        );
 
        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'feature_title',
            [
                'label' => esc_html__( 'Title', 'zota' ),
                'type' => Controls_Manager::TEXT,
            ]
        );
        
        $repeater->add_control(
            'feature_desc',
            [
                'label' => esc_html__( 'Description', 'zota' ),
                'type' => Controls_Manager::TEXTAREA,
            ]
        );
        
        $repeater->add_control(
            'feature_type',
            [
                'label' => esc_html__( 'Display Type', 'zota' ),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'icon',
                'options' => [
                    'image' => [
                        'title' => esc_html__('Image', 'zota'),
                        'icon' => 'fa fa-image',
                    ],
                    'icon' => [
                        'title' => esc_html__('Icon', 'zota'),
                        'icon' => 'fa fa-info',
                    ],
                ],
            ]
        );
        
        $repeater->add_control(
            'type_icon',
            [
                'label' => esc_html__('Choose Icon','zota'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'tb-icon tb-icon-gift',
					'library' => 'tbay-custom',
                ],
                'condition' => [
                    'feature_type' => 'icon'
                ]
            ]
        );
        $repeater->add_control(
            'type_image',
            [
                'label' => esc_html__('Choose Image','zota'),
                'type' => Controls_Manager::MEDIA,
                'condition' => [
                    'feature_type' => 'image'
                ]
            ]
        );    

        $this->add_control(
            'features',
            [
                'label' => esc_html__('Feature Item','zota'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ feature_title }}}',
            ]
        );
     
        $this->add_responsive_control(
			'feature_font_size_icon',
			[
				'label' => esc_html__('Font Size "Icon"', 'zota'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 80,
                    ],
                ],
				'default' => [
					'unit' => 'px',
					'size' => 24,
				],
                'selectors' => [
                    '{{WRAPPER}} .features .fbox-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                
			]
        );  
        
        $this->add_control(
            'feature_color_icon',
            [
                'label' => esc_html__( 'Color Icon', 'zota' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .features .fbox-icon' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
			'feature_margin_icon',
			[
				'label' => esc_html__( 'Margin "Icon"', 'zota' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .features .fbox-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
			]
        );
        
        $this->end_controls_section();
        $this->style_features();
        $this->add_control_responsive();
    }
    protected function style_features() {
        $this->start_controls_section(
            'section_style_features',
            [
                'label' => esc_html__( 'Style', 'zota' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'heading_feature_title',
            [
                'label' => esc_html__('Title', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );  
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'features_title_typography',
                'selector' => '{{WRAPPER}} .features .ourservice-heading',
            ]
        );
        $this->add_control(
            'feature_title_color',
            [
                'label' => esc_html__('Color','zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .features .ourservice-heading'    => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'spacing_title',
            [
                'label' => esc_html__('Spacing title','zota'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} .tbay-element-features .ourservice-heading' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'heading_feature_desc',
            [
                'label' => esc_html__('Description', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );  
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'features_title_desc',
                'selector' => '{{WRAPPER}} .features .description',
            ]
        );
        $this->add_control(
            'feature_desc_color',
            [
                'label' => esc_html__('Color','zota'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .features .description'    => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'spacing_desc',
            [
                'label' => esc_html__('Spacing Description','zota'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ], 
                'selectors' => [
                    '{{WRAPPER}} .features .description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'feature_align',
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
                    '{{WRAPPER}} .item .inner' => 'text-align: {{VALUE}} !important',
                ]
            ]
        );


        $this->end_controls_section();
    }
    protected function render_item($item) {
        extract($item);
        ?> 
        <div class="inner"> 
            <?php
                $this->render_item_fbox($feature_type,$type_image,$type_icon);
                $this->render_item_content($feature_title,$feature_desc);     
            ?>
        </div>
        <?php
    }      
    public function render_item_content($feature_title,$feature_desc) {
        ?>
            <div class="fbox-content">
                <?php

                if( isset($feature_title) && !empty($feature_title) ) {
                    echo '<h3 class="ourservice-heading">'. trim($feature_title) .'</h3>';
                }

                if(isset($feature_desc) && !empty($feature_desc)) {
                    echo '<p class="description">'. trim($feature_desc) .'</p>';
                } 

                ?>
            </div>
        <?php
    }
    
    public function render_item_fbox($feature_type,$type_image,$type_icon){
        $image_id = $type_image['id'];

        $fbox_class = '';
        $fbox_class .= 'fbox-'.$feature_type;
        if($feature_type === 'image') {
            $type_icon = '';
        } 

        ?>
        <?php
            if ( !empty($feature_type) && isset($feature_type) ) {
                ?>
                <div class="<?php echo esc_attr($fbox_class); ?>">
                    <?php if(isset($type_icon) && !empty($type_icon)): ?>
                        <?php $this->render_item_icon($type_icon) ?>
                    <?php elseif(isset($image_id) && !empty($image_id)): ?>
                        <?php echo  wp_get_attachment_image($image_id, 'full'); ?>
                    <?php endif;?>
                </div>
                <?php 
            }
        ?>
        

        <?php

    }

}
$widgets_manager->register(new Zota_Elementor_Features());
