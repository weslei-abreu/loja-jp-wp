<?php
if ( !function_exists( 'zota_tbay_page_metaboxes' ) ) {
	function zota_tbay_page_metaboxes(array $metaboxes) {

        $sidebars = zota_sidebars_array();

        $footers = array_merge( array('global' => esc_html__( 'Global Setting', 'zota' )), zota_tbay_get_footer_layouts() );
        $headers = array_merge( array('global' => esc_html__( 'Global Setting', 'zota' )), zota_tbay_get_header_layouts() );


		$prefix = 'tbay_page_';
	    $fields = array( 
			array(
				'name' => esc_html__( 'Select Layout', 'zota' ),
				'id'   => $prefix.'layout',
				'type' => 'select',
				'options' => array(
					'main' => esc_html__('Main Content Only', 'zota'),
					'left-main' => esc_html__('Left Sidebar - Main Content', 'zota'),
					'main-right' => esc_html__('Main Content - Right Sidebar', 'zota'),
				)
			),
            array(
                'id' => $prefix.'left_sidebar',
                'type' => 'select',
                'name' => esc_html__('Left Sidebar', 'zota'),
                'options' => $sidebars
            ),
            array(
                'id' => $prefix.'right_sidebar',
                'type' => 'select',
                'name' => esc_html__('Right Sidebar', 'zota'),
                'options' => $sidebars
            ),
            array(
                'id' => $prefix.'show_breadcrumb',
                'type' => 'select',
                'name' => esc_html__('Show Breadcrumb?', 'zota'),
                'options' => array(
                    'no' => esc_html__('No', 'zota'),
                    'yes' => esc_html__('Yes', 'zota')
                ),
                'default' => 'yes',
            ),
            array(
                'name' => esc_html__( 'Select Breadcrumbs Layout', 'zota' ),
                'id'   => $prefix.'breadcrumbs_layout',
                'type' => 'select',
                'options' => array(
                    'image' => esc_html__('Background Image', 'zota'),
                    'color' => esc_html__('Background color', 'zota'),
                    'text' => esc_html__('Just text', 'zota')
                ),
                'default' => 'text',
            ),
            array(
                'id' => $prefix.'breadcrumb_color',
                'type' => 'colorpicker',
                'name' => esc_html__('Breadcrumb Background Color', 'zota')
            ),
            array(
                'id' => $prefix.'breadcrumb_image',
                'type' => 'file',
                'name' => esc_html__('Breadcrumb Background Image', 'zota')
            ),
    	);

        $after_array = array(
            array(
                'id' => $prefix.'header_type',
                'type' => 'select', 
                'name' => esc_html__('Header Layout Type', 'zota'),
                'description' => esc_html__('Choose a header for your website.', 'zota'),
                'options' => $headers,
                'default' => 'global'
            ),            
            array(
                'id' => $prefix.'footer_type',
                'type' => 'select',
                'name' => esc_html__('Footer Layout Type', 'zota'),
                'description' => esc_html__('Choose a footer for your website.', 'zota'),
                'options' => $footers,
                'default' => 'global'
            ),
            array(
                'id' => $prefix.'extra_class',
                'type' => 'text',
                'name' => esc_html__('Extra Class', 'zota'),
                'description' => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'zota')
            )
        );
        $fields = array_merge($fields, $after_array); 
		
	    $metaboxes[$prefix . 'display_setting'] = array(
			'id'                        => $prefix . 'display_setting',
			'title'                     => esc_html__( 'Display Settings', 'zota' ),
			'object_types'              => array( 'page' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'fields'                    => $fields
		);

	    return $metaboxes;
	}
}
add_filter( 'cmb2_meta_boxes', 'zota_tbay_page_metaboxes' ); 

if ( !function_exists( 'zota_tbay_cmb2_style' ) ) {
	function zota_tbay_cmb2_style() {
		wp_enqueue_style( 'zota-cmb2', ZOTA_THEME_DIR . '/inc/vendors/cmb2/assets/cmb2.css', array(), '1.0' );
	}
    add_action( 'admin_enqueue_scripts', 'zota_tbay_cmb2_style', 10 );
}

