<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;

 
class Zota_Elementor_Product_CountDown extends Zota_Elementor_Carousel_Base {

    public function get_name() {
        return 'tbay-product-count-down';
    }

    public function get_title() {
        return esc_html__( 'Zota Product CountDown', 'zota' );
    }

    public function get_categories() {
        return [ 'zota-elements', 'woocommerce-elements'];
    }

    public function get_icon() {
        return 'eicon-countdown';
    }

    /**
     * Retrieve the list of scripts the image carousel widget depended on.
     *
     * Used to set scripts dependencies required to run the widget.
     *
     * @since 1.3.0
     * @access public
     *
     * @return array Widget scripts dependencies.
     */
    public function get_script_depends()
    {
        return ['slick', 'zota-custom-slick', 'jquery-countdowntimer'];
    }

    public function get_keywords() {
        return [ 'woocommerce-elements', 'product', 'products', 'countdown'];
    }

    protected function register_controls() {
        $this->register_controls_heading();
        $this->start_controls_section(
            'general',
            [
                'label' => esc_html__( 'General', 'zota' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'countdown_title',
            [
                'label' => esc_html__('Title Date', 'zota'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Deals end in:', 'zota'),
                'label_block' => true,
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
            'product_style',
            [
                'label' => esc_html__('Product Style', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'inner',
                'options' => $this->get_template_product(),
                'prefix_class' => 'elementor-product-',
            ]
        );

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
                'default' => 'woocommerce_thumbnail', 
                'exclude' => [ 'custom' ],
                'separator'    => 'after',
            ]
        );  

        $products = $this->get_available_on_sale_products();
        
        if (!empty($products)) {
            $repeater = $this->register_products_sale_repeater();
            $this->add_control(
                'product_sale',
                [
                    'label' => esc_html__( 'Select products', 'zota' ),
                    'type' => Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'trending_field' => '{{{ product_sale_item }}}',
                ]
            );  
        } else {
            $this->add_control(
                'html_products',
                [
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => sprintf(__('You do not have any discount products. <br>Go to the <strong><a href="%s" target="_blank">Products screen</a></strong> to create one.', 'zota'), admin_url('edit.php?post_type=product')),
                    'separator'       => 'after',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                   
                ]
            );
        }   
        $this->end_controls_section(); 
        
        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);
    }
    protected function register_products_sale_repeater() {
        $repeater = new \Elementor\Repeater(); 

        if( zota_elementor_pro_activated() ) {
            $product_ids_on_sale    = wc_get_product_ids_on_sale();
            $product_ids_on_sale[]  = 0;
            $repeater->add_control(
                'product_sale_item',
                [
                    'label' => esc_html__('Product', 'zota'),
                    'type' => ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
                    'autocomplete' => [
                        'object' => ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
                        'query' => [
                            'post_type' => [ 'product' ],
                            'post__in' => $product_ids_on_sale,
                        ],
                    ],
                    'options' => [], 
                    'multiple' => false,
                    'label_block' => true,
                    'save_default' => true,
                    'description' => esc_html__('Only search for sale products', 'zota'),
                ]
            );
        } else {
            $products = $this->get_available_on_sale_products();
            $repeater->add_control (
                'product_sale_item', 
                [
                    'label' => esc_html__( 'Product', 'zota' ),
                    'type'         => Controls_Manager::SELECT,
                        'options'      => $products,
                        'default'      => array_keys($products)[0],
                        'multiple' => true,
                        'label_block' => true,
                        'save_default' => true,
                        'description' => esc_html__( 'Only search for sale products', 'zota' ),
                ]
            );
        }

        return $repeater;
    }

    public function render_content_product_count_down() {
        $settings = $this->get_settings_for_display();
        extract($settings);
        
        $ids = $this->get_id_products_countdown( $product_sale ); 

        if( !is_array($ids) ){
            $atts['ids'] = $ids;
        } else {
            if( is_countable($ids) && count($ids) === 0 ) {
                echo '<div class="not-product-count-down">'. esc_html__('Please select the show product', 'zota')  .'</div>';
                return;
            }

            $atts['ids'] = implode(',', $ids);
        }
        
        $atts['orderby'] = 'post__in';

        $type = 'products';

        /** Get Query Products with Transient */
        $transient_name = 'zota_product_count_down_loop_' . md5($this->get_id()) . '_' . md5(serialize($settings));
        $loop = get_transient($transient_name);

        if (false === $loop) {
            $shortcode = new WC_Shortcode_Products($atts, $type);
            $args = $shortcode->get_query_args();
    
            $loop = new WP_Query($args);  
            /** Set Transient */
            set_transient($transient_name, $loop, DAY_IN_SECONDS);
        }
       
        if( !$loop->have_posts() ) return;
        
        $this->add_render_attribute('row', 'class', ['products']);

        $attr_row = $this->get_render_attribute_string('row');

        wc_get_template( 'layout-products/layout-products.php' , array( 'loop' => $loop, 'product_style' => $product_style, 'countdown_title' => $countdown_title, 'countdown' => true, 'attr_row' => $attr_row, 'size_image' => $product_image_size) );
        
    }
    protected function get_id_products_countdown($product_sale) {

        $product_ids = array();

        foreach ($product_sale as $item ) :

            extract($item);
            array_push($product_ids, $product_sale_item);

        endforeach;

        return $product_ids;
    }

}
$widgets_manager->register(new Zota_Elementor_Product_CountDown());