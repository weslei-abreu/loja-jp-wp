<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Zota_Elementor_Product_Category extends  Zota_Elementor_Carousel_Base{
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
        return 'tbay-product-category';
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
        return esc_html__( 'Zota Product Category', 'zota' );
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
        return 'eicon-product-categories';
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
        return [ 'woocommerce-elements', 'product', 'products', 'category' ];
    }

    protected function register_controls() {
        $this->register_controls_heading();

        $this->start_controls_section(
            'section_general',
            [
                'label' => esc_html__( 'Product Category', 'zota' ),
            ]
        );
        $this->add_control(
            'limit',
            [
                'label' => esc_html__('Number of products', 'zota'),
                'type' => Controls_Manager::NUMBER,
                'description' => esc_html__( 'Number of products to show ( -1 = all )', 'zota' ),
                'default' => 6,
                'min'  => -1
            ]
        );
        $this->add_control(
            'feature_image',
            [
                'label'     => esc_html__('Feature Image', 'zota'),
                'type'      => Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => Elementor\Utils::get_placeholder_image_src(),
                ]
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

        $categories = $this->get_product_categories();

        $this->add_control( 
            'category',
            [
                'label'     => esc_html__('Category', 'zota'),
                'type'      => Controls_Manager::SELECT, 
                'default'   => array_keys($categories)[0],
                'options'   => $categories,
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
                'exclude' => [ 'custom' ],
                'default' => 'woocommerce_thumbnail', 
            ]
        );  

        $this->add_control(
            'product_type',
            [
                'label' => esc_html__('Product Type', 'zota'),
                'type' => Controls_Manager::SELECT,
                'default' => 'newest',
                'separator'    => 'before',
                'options' => $this->get_product_type(),
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
        $this->add_control_responsive();
        $this->add_control_carousel(['layout_type' => 'carousel']);
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
                'default'   => esc_html__( 'Show More', 'zota' ),
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
    
    public function render_item_image($settings) {
        $image_id           = $settings['feature_image']['id'];
        if(empty($image_id)) {
            return;
        }
        ?>
            <div class="product-category-image">
                <?php echo wp_get_attachment_image($image_id, 'full'); ?>
            </div>
        <?php
        
    }
    public function render_item_button() {
        $settings = $this->get_settings_for_display();
        extract( $settings );

        $category = get_term_by('slug', $category, 'product_cat');
        $url_category =  get_term_link($category);
        if(isset($text_button) && !empty($text_button)) {?>
            <a href="<?php echo esc_url($url_category)?>" class="show-all"><?php echo trim($text_button) ?>
                <?php 
                    $this->render_item_icon($icon_button);
                ?>
                
            </a>
            <?php
        }
        
    }

}
$widgets_manager->register(new Zota_Elementor_Product_Category());
