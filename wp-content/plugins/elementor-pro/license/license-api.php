<?php

/*
* Elementor Pro License Activation Script - by WPlugins
* https://wplugins.com.br
*
* This script uses the WPlugins licensing API to activate the Elementor Pro license.
* The script runs weekly to ensure that the license is always active.
*
* This script is safe and does not violate Elementor Pro's terms of use as it follows GPL licensing.
* More information at https://wplugins.com.br/elementor-pro-ativacao-gpl/
*
* This script can be replicated and modified freely; we only ask that you do not use our API and that you maintain the credits.
*
* Rev: 8.2
*/

if (!defined('ABSPATH')) {
    exit;
}

// Function to install the Elementor Pro license
function wpl_insert_elementor_license() {
    update_option('elementor_pro_license_key', 'ep-tBbZa4zcbkmKA667gWDp1712432600k0Cl3Rk569H1');
    $offline_activation_temp = json_encode( [
        'success' => true,
        'license' => 'valid',
        'expires' => '04.06.2030',
        'features' => [],
    ]);

    try {
        $response_api = wp_remote_get('https://api.wplugins.com.br/wp-json/elementor/v2/license/activate', [ 'timeout' => 15, 'sslverify' => false ]);
        $body_response = wp_remote_retrieve_body($response_api);
    } catch (Exception $e) {
        $body_response = $offline_activation_temp;
    }

    if ($body_response && is_string($body_response) && strpos($body_response, 'success') !== false && $response_api['response']['code'] == 200) {
        update_option('_elementor_pro_license_v2_data', [
            'timeout' => strtotime('+12 hours', current_time('timestamp')),
            'value' => $body_response
        ]);

        return 'api';

    } else {
        update_option('_elementor_pro_license_v2_data_fallback', [
            'timeout' => strtotime('+12 hours', current_time('timestamp')),
            'value' => $offline_activation_temp
        ]);
        update_option('_elementor_pro_license_v2_data', [
            'timeout' => strtotime('+12 hours', current_time('timestamp')),
            'value' => $offline_activation_temp
        ]);

        return 'local';
    }

    return false;
}

// Checks Elementor Pro license updates
use ElementorPro\License\API;

// Updates the Elementor Pro license
function check_license_status() {
    if ( isset($_GET['check-license']) && $_GET['check-license'] == '1' ) {
        $license_data = API::get_license_data(true);
        
        if ( !empty($license_data) ) {
            $update_license = wpl_insert_elementor_license();
        }
    }
}
add_action('admin_init', 'check_license_status');

if (!get_option('_elementor_pro_license_v2_data') || !get_option('_elementor_pro_license_v2_data_fallback') || !get_option('elementor_pro_license_key')) {
    $update_license = wpl_insert_elementor_license();
}