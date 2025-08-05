<?php
if (!defined('ABSPATH') || function_exists('Zota_Elementor_Widget_Image') ) {
    exit; // Exit if accessed directly.
}

use Elementor\Widget_Image;


abstract class Zota_Elementor_Widget_Image extends Widget_Image {
	public function get_name_template() {
        return str_replace('zota-', '', $this->get_name());
    }

    public function get_categories() {
        return [ 'zota-elements' ];
    }

    public function get_name() {
        return 'tbay-base';
    }

    /**
	 * Get view template
	 *
	 * @param string $tpl_name
	 */
	protected function get_view_template( $tpl_slug, $tpl_name, $settings = [] ) {
		$located   = '';
		$templates = [];
		

		if ( ! $settings ) {
			$settings = $this->get_settings_for_display();
		} 

		if ( !empty($tpl_name) ) {
			$tpl_name  = trim( str_replace( '.php', '', $tpl_name ), DIRECTORY_SEPARATOR );
			$templates[] = 'elementor_templates/' . $tpl_slug . '-' . $tpl_name . '.php';
			$templates[] = 'elementor_templates/' . $tpl_slug . '/' . $tpl_name . '.php';
		}

		$templates[] = 'elementor_templates/' . $tpl_slug . '.php';
 
		foreach ( $templates as $template ) {
			if ( file_exists( ZOTA_THEMEROOT . '/' . $template ) ) {
				$located = locate_template( $template );
				break;
			} else {
				$located = false;
			}
		}

		if ( $located ) {
			include $located;
		} else {
			echo sprintf( __( 'Failed to load template with slug "%s" and name "%s".', 'zota' ), $tpl_slug, $tpl_name );
		}
	}

	protected function render() {
        $settings = $this->get_settings_for_display();
        $this->add_render_attribute('wrapper', 'class', ['tbay-element', 'tbay-element-' . $this->get_name_template()]);
        
        if (!method_exists($this, 'get_view_template')) {
            parent::render();
        } else {
            $this->get_view_template($this->get_name_template(), '', $settings);
        }
    }
}