<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;


class Zota_Elementor_Woocommerce_Tags extends Zota_Elementor_Widget_Base {

    public function get_name() {
        return 'tbay-woocommerce-tags';
    }

    public function get_title() {
        return esc_html__( 'Zota Woocommerce Tags', 'zota' );
    }

    public function get_categories() {
        return [ 'zota-elements', 'woocommerce-elements'];
    }

    public function get_icon() {
        return 'eicon-tags';
    }

    public function get_keywords() {
        return [ 'woocommerce-elements', 'woocommerce-tags' ];
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
            'limit',
            [
                'label' => esc_html__('Number tag to show ( -1 = all, max = 50 )', 'zota'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min'  => -1,
                'max'  => 50, 
            ]
        );

        $this->end_controls_section();
    }
    public function render_item() {
        $settings = $this->get_settings_for_display();
        extract($settings);
    
        if ($limit === 0) {
            echo '<p>'. esc_html__('Please select the number of tags again', 'zota') .'</p>';
            return;
        }
    
        $taxonomy = 'product_tag';
        $cache_key = 'zota_woocommerce_tags_' . $limit;
        $list = wp_cache_get($cache_key);
    
        if (false === $list) {
            $args = array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
            );
    
            if ($limit !== -1) {
                $args['number'] = $limit;
            }
    
            $tags = get_terms($args);
    
            $list = '';
            if ($tags && is_array($tags)) {
                if (!empty($tags)) {
                    $list .= '<ul class="list-tags">';
                    foreach ($tags as $tag) {
                        $term_link = get_term_link($tag->term_id, $taxonomy);
                        $name =  $tag->name;
                        $list .= '<li><a class="category_links" href="' . esc_url($term_link) . '">' . trim($name) . '</a></li>';
                    }
                    $list .= '</ul>';
                }
            } else {
                $list .= '<p>'. esc_html__('Sorry, but no tags were found','zota') .'</p>';
            }
    
            wp_cache_set($cache_key, $list, '', 3600);
        }
    
        echo trim($list);
    }

}
$widgets_manager->register(new Zota_Elementor_Woocommerce_Tags());