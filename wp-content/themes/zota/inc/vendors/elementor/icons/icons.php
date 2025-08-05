<?php

if ( ! function_exists( 'zota_elementor_icon_control_simple_line_icons' ) ) {
	add_action( 'elementor/icons_manager/additional_tabs', 'zota_elementor_icon_control_simple_line_icons' );
	function zota_elementor_icon_control_simple_line_icons( $tabs ) {
		$tabs['simple-line-icons'] = [
			'name'          => 'simple-line-icons',
			'label'         => esc_html__( 'Simple Line Icons', 'zota' ),
			'prefix'        => 'icon-',
			'displayPrefix' => 'sim-icon',
			'labelIcon'     => 'fa fa-font-awesome',
			'ver'           => '2.4.0',
			'fetchJson'     => get_template_directory_uri() . '/inc/vendors/elementor/icons/json/simple-line-icons.json', 
			'native'        => true,
		];

		return $tabs;
	}
}

if ( ! function_exists( 'zota_elementor_icon_control_material_design_iconic' ) ) {
	add_action( 'elementor/icons_manager/additional_tabs', 'zota_elementor_icon_control_material_design_iconic' );
	function zota_elementor_icon_control_material_design_iconic( $tabs ) {
		$tabs['material-design-iconic'] = [
			'name'          => 'material-design-iconic',
			'label'         => esc_html__( 'Material Design Iconic', 'zota' ),
			'prefix'        => 'zmdi-',
			'displayPrefix' => 'zmdi',
			'labelIcon'     => 'fa fa-font-awesome',
			'ver'           => '2.2.0',
			'fetchJson'     => get_template_directory_uri() . '/inc/vendors/elementor/icons/json/material-design-iconic.json', 
			'native'        => true,
		];

		return $tabs;
	}
}


if ( ! function_exists( 'zota_elementor_icon_control_tbay_custom' ) ) {
	add_action( 'elementor/icons_manager/additional_tabs', 'zota_elementor_icon_control_tbay_custom' );
	function zota_elementor_icon_control_tbay_custom( $tabs ) {
		$tabs['tbay-custom'] = [
			'name'          => 'tbay-custom',
			'label'         => esc_html__( 'Thembay Custom', 'zota' ),
			'prefix'        => 'tb-icon-',
			'displayPrefix' => 'tb-icon',
			'labelIcon'     => 'fa fa-font-awesome',
			'ver'           => '1.0.0',
			'fetchJson'     => get_template_directory_uri() . '/inc/vendors/elementor/icons/json/tbay-custom.json', 
			'native'        => true,
		];

		return $tabs;
	}
}