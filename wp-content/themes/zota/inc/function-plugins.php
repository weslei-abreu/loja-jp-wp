<?php

if ( !function_exists('zota_register_required_plugins') ) {
	function zota_register_required_plugins() {

		$plugins[] =(array(
			'name'                     => 'Cmb2',
		    'slug'                     => 'cmb2',
		    'required'                 => true,
		));

		$plugins[] =(array(
			'name'                     => 'WooCommerce',
		    'slug'                     => 'woocommerce',
		    'required'                 => true,
		));

		$plugins[] =(array(
			'name'                     => 'MailChimp',
		    'slug'                     => 'mailchimp-for-wp',
		    'required'                 =>  false
		));	

		$plugins[] =(array(
			'name'                     => 'WPForms Lite',
		    'slug'                     => 'wpforms-lite',
		    'required'                 => false,
		));

		$plugins[] =(array(
			'name'                     => 'Redux Framework',
		    'slug'                     => 'redux-framework',
		    'required'                 => false,
		));
		
		$plugins[] =(array(
			'name'                     => 'Thembay Elementor',
			'slug'                     => 'tbay-elementor',
			'required'                 => true ,
			'source'         		   => esc_url( 'plugins.thembay.com/tbay-elementor.zip' ),
		));

		$plugins[] =(array(
			'name'                     => 'YITH WooCommerce Wishlist',
		    'slug'                     => 'yith-woocommerce-wishlist',
		    'required'                 =>  true
		));

		$plugins[] =(array(
			'name'                     => 'WooCommerce Variation Swatches',
		    'slug'                     => 'woo-variation-swatches',
		    'required'                 =>  true,
		    'source'         		   => esc_url( 'downloads.wordpress.org/plugin/woo-variation-swatches.zip' ),
		));		

		$plugins[] =(array(
			'name'                     => 'WooCommerce Products Filter',
		    'slug'                     => 'woocommerce-products-filter',
			'required'                 =>  false,
			'source'         		   => esc_url( 'plugins.thembay.com/woocommerce-products-filter.zip' ),
		));	
		
		$plugins[] =(array(
			'name'                     => 'Elementor',
	        'slug'                     => 'elementor',
	        'required'                 => true,
		));				

		$plugins[] =(array(
			'name'                     => 'Revolution Slider',
	        'slug'                     => 'revslider',
	        'required'                 => true ,
	        'source'         		   => esc_url( 'plugins.thembay.com/revslider.zip' ),
		));

		/*
		 * Array of configuration settings. Amend each line as needed.
		 *
		 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
		 * strings available, please help us make TGMPA even better by giving us access to these translations or by
		 * sending in a pull-request with .po file(s) with the translations.
		 *
		 * Only uncomment the strings in the config array if you want to customize the strings.
		*/
		$config = array(
			'id'           => 'zota',
			// Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',
			// Default absolute path to bundled plugins.
			'has_notices'  => true,
			// Show admin notices or not.
			'dismissable'  => true,
			// If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',
			// If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,
			// Automatically activate plugins after installation or not.
			'message'      => '',
			// Message to output right before the plugins table.
		);
		 
		tgmpa( $plugins, $config );
	}
	add_action( 'tgmpa_register', 'zota_register_required_plugins' );
}