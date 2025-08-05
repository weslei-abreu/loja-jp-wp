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
class Zota_Elementor_Product_Categories_Tabs extends  Zota_Elementor_Carousel_Base{
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
        return 'tbay-product-categories-tabs';
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
        return esc_html__( 'Zota Product Categories Tabs', 'zota' );
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
        return 'eicon-product-tabs';
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
        return [ 'woocommerce-elements', 'product-categories' ];
    }

    protected function register_controls() {
        $this->register_controls_heading();
        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'Product Categories', 'zota' ),
            ]
        );        

        $this->add_control(
            'limit',
            [
                'label' => esc_html__('Number of products', 'zota'),
                'type' => Controls_Manager::NUMBER,
                'description' => esc_html__( 'Number of products to show ( -1 = all )', 'zota' ),
                'default' => 6,
                'min'  => -1,
            ]
        );

        $this->add_control(
            'advanced',
            [
                'label' => esc_html__('Advanced', 'zota'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->register_woocommerce_order();

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'product_image',
                'exclude' => [ 'custom' ],
                'default' => 'woocommerce_thumbnail', 
            ]
        );  

        $this->add_control(
            'product_type',
            [   
                'label'   => esc_html__('Product Type','zota'),
                'type'     => Controls_Manager::SELECT,
                'options' => $this->get_product_type(),
                'default' => 'newest'
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
                'label' => esc_html__( 'Ajax Categories Tabs', 'zota' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => esc_html__( 'Show/hidden Ajax Categories Tabs', 'zota' ), 
            ]
        );

        $repeater = $this->register_category_repeater();
        $this->add_control(
            'categories_tabs',
            [
                'label' => esc_html__( 'Categories Items', 'zota' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'categories_field' => '{{{ categories }}}',
            ]
        );   

        $this->add_control(
            'product_style',
            [
                'label' => esc_html__('Product Style', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'inner',
                'options' => $this->get_template_product(),
                'prefix_class' => 'elementor-product-'
            ]
        );


        $this->register_button();
        $this->end_controls_section();
        $this->add_control_banner_image();
        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);
        $this->register_style_heading();
    }

    private function register_category_repeater() {
        $repeater = new \Elementor\Repeater();
        $categories = $this->get_product_categories();
        $repeater->add_control (
            'category', 
            [
                'label' => esc_html__( 'Select Category', 'zota' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => array_keys($categories)[0],
                'label_block' => true,
                'options'   => $categories,   
            ]
        );

        return $repeater;
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
				'selector' => '{{WRAPPER}} .wrapper-heading-tab .product-categories-tabs-title a',
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
                    '{{WRAPPER}} .wrapper-heading-tab > ul' => 'justify-content: {{VALUE}};',
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
    protected function register_button() {
        $this->add_control(
            'show_more',
            [
                'label'     => esc_html__('Display Show More', 'zota'),
                'type'      => Controls_Manager::SWITCHER,
                'default' => 'no'
            ]
        );  
        $this->add_control(
            'text_button',
            [
                'label'     => esc_html__('Text Button', 'zota'),
                'type'      => Controls_Manager::TEXT,
                'condition' => [
                    'show_more' => 'yes'
                ]
            ]
        );  
        $this->add_control(
            'icon_button',
            [
                'label'     => esc_html__('Icon Button', 'zota'),
                'type'      => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'tb-icon tb-icon-arrow-right',
					'library' => 'tbay-custom',
                ],
                'condition' => [
                    'show_more' => 'yes'
                ]
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
   

    public function get_template_product() {
        return apply_filters( 'zota_get_template_product', 'inner' );
    }

    public function render_tabs_title($categories_tabs, $random_id) {
        $settings = $this->get_settings_for_display();
        $cat_operator = $product_type = $limit = $orderby = $order = '';
        extract($settings);

        if ($ajax_tabs === 'yes') {
            $this->add_render_attribute('row', 'class', ['products']);
            $attr_row = $this->get_render_attribute_string('row'); 

            $json = array(
                'product_type'                  => $product_type,
                'cat_operator'                  => $cat_operator,
                'limit'                         => $limit,
                'orderby'                       => $orderby,
                'order'                         => $order,
                'product_style'                 => $product_style,
                'attr_row'                      => $attr_row,
                'product_image_size'            => $product_image_size,
            ); 

            $encoded_settings  = wp_json_encode( $json );

            $tabs_data = 'data-atts="'. esc_attr( $encoded_settings ) .'"';
        } else {
            $tabs_data = '';
        }
        ?>
            
            <?php
                if(!empty($title_cat_tab) || !empty($sub_title_cat_tab) ) {
                    ?>
                    <h3 class="heading-tbay-title">
                        <?php if( !empty($title_cat_tab) ) : ?>
                            <span class="title"><?php echo trim($title_cat_tab); ?></span>
                        <?php endif; ?>	    	
                        <?php if( !empty($sub_title_cat_tab) ) : ?>
                            <span class="subtitle"><?php echo trim($sub_title_cat_tab); ?></span>
                        <?php endif; ?>
                    </h3>
                    <?php
                }
            ?>

            <ul class="product-categories-tabs-title tabs-list nav nav-tabs" <?php echo trim($tabs_data); ?>>
                <?php $_count = 0; ?>
                <?php foreach ( $categories_tabs as $item ) : ?>
                    <?php $this->render_product_tab($item['category'], $item['_id'], $_count, $random_id); ?>
                    <?php $_count++; ?>
                <?php endforeach; ?> 
            </ul>
            
        <?php
    }
    public function render_product_tab($item, $_id, $_count, $random_id) {
        
        ?>
        <?php 
            $active = ($_count == 0) ? 'active' : '';
            $category = get_term_by( 'slug', $item, 'product_cat' );
            $title =  ( !empty( $category->name ) ) ? $category->name : '';
        ?>
        <li >
            <a class="<?php echo esc_attr( $active ); ?>" data-value="<?php echo esc_attr($item); ?>" href="#<?php echo esc_attr($item).'-'. esc_attr($random_id) .'-'. esc_attr($_id); ?>" data-toggle="tab" aria-controls="<?php echo esc_attr($item.'-'. $random_id .'-'.$_id); ?>"><?php echo trim($title);?></a>
        </li>

       <?php
    }
    public function render_product_tabs_content($categories_tabs, $random_id)  
    {
        $settings = $this->get_settings_for_display();
        extract( $settings );
        ?>
            <div class="content-product-category-tab">
                <?php 
                    if( zota_tbay_get_theme() === 'auto-part' ) {
                        $this->render_content_banner();
                    }
                
                ?>
                <div class="tbay-addon-content tab-content woocommerce">
                <?php  
                    $_count = 0;
                    foreach ($categories_tabs as $key) {
                        $tab_active = ($_count == 0) ? ' active active-content current' : ''; 
                        ?> 
                        <div class="tab-pane <?php echo esc_attr($tab_active); ?>" id="<?php echo esc_attr($key['category'].'-'. $random_id .'-'.$key['_id']); ?>"> 
                        <?php 
                        if( $_count === 0 || $ajax_tabs !== 'yes' ) {
                            $this->render_content_tab($key['category'], $tab_active, $key['_id'], $random_id);
                        }
                        $_count++; 
                        ?>
                        </div>
                        <?php
                    } 
                ?>
                </div>
            </div>
        <?php
    }

    private function render_content_banner() {
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

    private function  render_content_tab($key, $tab_active, $_id, $random_id) {
        $settings = $this->get_settings_for_display();
        $cat_operator = $product_type = $limit = $orderby = $order = '';
        extract( $settings );

        /** Get Query Products with Transient */
        $transient_name = 'zota_product_categories_tab_loop_' . md5($this->get_id()) . '_' . md5($key) . '_' . md5(serialize($settings));
        $loop = get_transient($transient_name);

        if (false === $loop) {
            /** Get Query Products */
            $loop = zota_get_query_products($key,  $cat_operator, $product_type, $limit, $orderby, $order);
            /** Set Transient */
            set_transient($transient_name, $loop, DAY_IN_SECONDS);
        }

        // $loop = zota_get_query_products($key,  $cat_operator, $product_type, $limit, $orderby, $order);

        $this->add_render_attribute('row', 'class', ['products']);

        $attr_row = $this->get_render_attribute_string('row');
        wc_get_template( 'layout-products/layout-products.php' , array( 'loop' => $loop, 'product_style' => $product_style, 'attr_row' => $attr_row, 'size_image' => $product_image_size) );
    }
    
    public function render_item_button() {
        $settings = $this->get_settings_for_display();
        extract( $settings );
        $url_category =  get_permalink(wc_get_page_id('shop'));
        if(isset($text_button) && !empty($text_button)) {?>
            <div class="readmore-wrapper"><a href="<?php echo esc_url($url_category)?>" class="btn show-all"><?php echo trim($text_button) ?>
                <?php 
                    $this->render_item_icon($icon_button);
                ?>
            </a></div>
            <?php
        }
        
    }

}
$widgets_manager->register(new Zota_Elementor_Product_Categories_Tabs());
