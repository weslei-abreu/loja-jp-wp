<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Utils;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Zota_Elementor_Product_Tabs extends  Zota_Elementor_Carousel_Base{
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
        return 'tbay-product-tabs';
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
        return esc_html__( 'Zota Product Tabs', 'zota' );
    }

    public function get_categories() {
        return [ 'zota-elements', 'woocommerce-elements'];
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
        return 'eicon-tabs';
    }

    /**
     * Register tabs widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    public function get_script_depends()
    {
        return ['slick', 'zota-custom-slick'];
    }

    public function get_keywords() {
        return [ 'woocommerce-elements', 'product', 'products', 'tabs' ];
    }

    protected function register_controls() {
        $this->register_controls_heading();

        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'Product Tabs', 'zota' ),
            ]
        );
        $this->add_control(
            'limit',
            [
                'label' => esc_html__('Number of products ( -1 = all, max = 50 )', 'zota'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min'  => -1,
                'max'  => 50, 
            ]
        );
        $this->add_control(
            'layout_type',
            [
                'label'     => esc_html__('Layout', 'zota'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'grid',
                'options'   => [
                    'grid'      => esc_html__('Grid', 'zota'), 
                    'carousel'  => esc_html__('Carousel', 'zota'), 
                ],
            ]
        ); 

        $this->register_woocommerce_categories_operator();

        $this->add_control(
            'heading_product_image',
            [
                'label' => esc_html__('Product Image', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator'    => 'before',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'product_image',
                'exclude' => [ 'custom' ],
                'default' => 'woocommerce_thumbnail', 
            ]
        );  

        $this->add_control(
            'product_style',
            [
                'label' => esc_html__('Product Style', 'zota'),
                'type' => Controls_Manager::SELECT,
                'separator'    => 'before',
                'default' => 'inner',
                'options' => $this->get_template_product(),
                'prefix_class' => 'elementor-product-'
            ]
        );

        if( zota_tbay_get_theme() === 'auto-part' ) {
            $this->add_control(
                'show_banner_image',
                [
                    'label' => esc_html__( 'Show Banner Image', 'zota' ),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => 'yes',
                    'description' => esc_html__( 'Show/hidden Banner Image Tabs', 'zota' ), 
                ]
            );
        }

        $this->add_control(
            'ajax_tabs',
            [
                'label' => esc_html__( 'Ajax Product Tabs', 'zota' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => esc_html__( 'Show/hidden Ajax Product Tabs', 'zota' ), 
            ]
        );

        $this->register_controls_product_tabs();
        $this->add_control(
            'advanced',
            [
                'label' => esc_html__('Advanced', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        
        $this->add_control(
            'orderby',
            [
                'label' => esc_html__('Order By', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => $this->get_woo_order_by(),
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__('Order', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'asc',
                'options' => $this->get_woo_order(),
            ]
        );
        
        $this->end_controls_section();
        $this->add_control_banner_image();
        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);
        $this->register_style_heading();
    }

    public function register_controls_product_tabs() {
        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'product_tabs_title',
            [
                'label' => esc_html__( 'Title', 'zota' ),
                'type' => Controls_Manager::TEXT,
            ]
        );
        $repeater->add_control(
            'product_tabs',
            [
                'label' => esc_html__('Show Tabs', 'zota'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_product_type(),
                'default' => 'newest',
            ]
        );  
        $this->add_control(
            'list_product_tabs',
            [
                'label' => esc_html__('Tab Item','zota'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ product_tabs_title }}}',
            ]
        );
        
    }

    private function add_control_banner_image() {
        if( zota_tbay_get_theme() !== 'auto-part' ) return;

        $this->start_controls_section(
            'section_banner_image',
            [
                'label' => esc_html__( 'Banner Image Settings', 'zota' ),
                'type' => Controls_Manager::SECTION,
                'condition' => [
                    'show_banner_image' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'heading_banner_01',
            [
                'label' => esc_html__('Banner 1', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator'   => 'before',
            ]
        ); 

        $this->add_control(
			'media_image_01',
			[
				'label'   => esc_html__( 'Choose image banner 1', 'zota' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'default' => 'full',
                'type' => Controls_Manager::HIDDEN,
                'name' => 'size_image_01',
            ]
        );

        $this->add_control(
            'link_image_01',
            [
                'label'         => esc_html__('Link to banner 1', 'zota'),
                'type'          => Controls_Manager::URL,
                'placeholder'   => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        ); 

        $this->add_control(
            'heading_banner_02',
            [
                'label' => esc_html__('Banner 2', 'zota'),
                'type' => Controls_Manager::HEADING,
                'separator'   => 'before',
            ]
        ); 

        $this->add_control(
			'media_image_02',
			[
				'label'   => esc_html__( 'Choose image banner 2', 'zota' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'default' => 'full',
                'type' => Controls_Manager::HIDDEN,
                'name' => 'size_image_02',
            ]
        );

        $this->add_control(
            'link_image_02',
            [
                'label'         => esc_html__('Link to banner 2', 'zota'),
                'type'          => Controls_Manager::URL,
                'placeholder'   => esc_html__( 'https://your-link.com', 'zota' ),
            ]
        ); 


        $this->end_controls_section();
    }

    protected function register_style_heading() {
        $this->start_controls_section(
            'section_style_heading_categories_tab',
            [
                'label' => esc_html__('Heading Product Categories Tabs', 'zota'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'style_heading_tab',
            [
                'label' => esc_html__('Style Heading Tab', 'zota'),
                'type' => Controls_Manager::SELECT2,
                'options' => [
                    'style-inline' => esc_html__('Inline','zota'),
                    'style-block' => esc_html__('Block','zota'),
                ],
                'default' => 'style-block',
                'prefix_class' => 'heading-tab-'
            ]
        );

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'heading_categories_tab_typography',
				'selector' => '{{WRAPPER}} .wrapper-heading-tab .product-tabs-title a',
			]
        );

        $this->add_responsive_control(
            'heading_categories_tab_align',
            [
                'label' => esc_html__('Alignment', 'zota'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'zota'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'zota'),
                        'icon' => 'fa fa-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('Right', 'zota'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'condition' => [
                    'style_heading_tab' => 'style-block',
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .tbay-element-product-tabs > .wrapper-heading-tab > ul' => 'justify-content: {{VALUE}};',
                ],
            ]
        );
     
        $this->add_responsive_control(
            'heading_categories_tab_padding',
            [
                'label'      => esc_html__( 'Padding', 'zota' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ], 
                'selectors'  => [
                    '{{WRAPPER}} .wrapper-heading-tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_section();

    }
    public function get_template_product() {
        return apply_filters( 'zota_get_template_product', 'inner' );
    }

    public function render_product_tabs($product_tabs, $_id, $title, $active) {
       ?>
            <li>
                <a href="#<?php echo esc_attr($product_tabs.'-'.$_id); ?>" data-value="<?php echo esc_attr($product_tabs); ?>" class="<?php echo esc_attr( $active ); ?>" data-toggle="tab" data-title="<?php echo esc_attr($title);?>" ><?php echo trim($title)?></a>
            </li>

       <?php

    }

    public function render_content_banner() {
        $settings = $this->get_settings_for_display();
        extract( $settings );

        if( $show_banner_image !== 'yes' ) return;

        if( empty($media_image_01['id']) && empty($media_image_02['id']) ) return;

        ?>
        <div class="tbay-addon-banner">
            <?php 
                if( !empty($media_image_01['id']) ) {
                    ?>
                        <div class="banner-image banner-image-01">
                            <?php 
                                if ( ! empty( $link_image_01['url'] ) ) {
                                    $this->add_link_attributes( 'link_image_01', $link_image_01 );

                                    ?>
                                    <a <?php echo $this->get_render_attribute_string( 'link_image_01' ); ?>>
                                    <?php
                                }
                                    echo  Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'size_image_01', 'media_image_01' ); 

                                    if ( ! empty( $link_image_01['url'] ) ) {
                                        echo '</a>';
                                    }
                            ?> 
                        </div>
                    <?php
                }

                if( !empty($media_image_02['id']) ) {
                    ?>
                        <div class="banner-image banner-image-02">
                            <?php 
                                if ( ! empty( $link_image_02['url'] ) ) {
                                    $this->add_link_attributes( 'link_image_02', $link_image_02 );

                                    ?>
                                    <a <?php echo $this->get_render_attribute_string( 'link_image_02' ); ?>>
                                    <?php
                                }
                                    echo  Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'size_image_02', 'media_image_02' ); 

                                    if ( ! empty( $link_image_02['url'] ) ) {
                                        echo '</a>';
                                    }
                            ?> 
                        </div>
                    <?php
                }
            ?>
        </div>
        <?php
    }

    public function  render_content_tab($product_tabs,$tab_active,$_id) {

        $settings = $this->get_settings_for_display();
        extract( $settings );
        
        $this->add_render_attribute('row', 'class', $this->get_name_template());

        if( isset($rows) && !empty($rows) ) {
            $this->add_render_attribute( 'row', 'class', 'row-'. $rows);
        }

        $product_type = $product_tabs;

        $transient_name = 'zota_product_tabs_loop_' . md5($this->get_id()) . '_' . md5(serialize($settings)). '_' . md5(serialize($product_type));
        $loop = get_transient($transient_name);

        if (false === $loop) {
            /** Get Query Products */
            $loop = zota_get_query_products($categories,  $cat_operator, $product_type, $limit, $orderby, $order);
            /** Set Transient */
            set_transient($transient_name, $loop, DAY_IN_SECONDS);
        }

        $this->add_render_attribute('row', 'class', ['products']);

        $attr_row = $this->get_render_attribute_string('row');

        wc_get_template( 'layout-products/layout-products.php' , array( 'loop' => $loop, 'product_style' => $product_style, 'attr_row' => $attr_row, 'size_image' => $product_image_size) );
    }
}
$widgets_manager->register(new Zota_Elementor_Product_Tabs());
