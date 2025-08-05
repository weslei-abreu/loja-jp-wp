<?php

namespace WeDevs\DokanPro;

use WeDevs\DokanPro\Dependencies\Appsero\Client;
use WeDevs\DokanPro\Dependencies\Appsero\License;
use WeDevs\DokanPro\Dependencies\Appsero\Updater;

/**
 * Dokan Update class
 *
 * Performs license validation and update checking
 */
class Update {

    /**
     * Appsero License Instance
     *
     * @var License
     */
    private $license;

    /**
     * The license product ID
     *
     * @var string
     */
    private $product_id = 'dokan-pro';

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->init_appsero();

        add_action( 'init', [ $this, 'add_menu_page' ] );

        if ( is_multisite() ) {
            if ( is_main_site() ) {
                add_filter( 'dokan_admin_notices', [ $this, 'license_enter_notice' ] );
            }
        } else {
            add_filter( 'dokan_admin_notices', [ $this, 'license_enter_notice' ] );
        }

        add_action( 'in_plugin_update_message-' . plugin_basename( DOKAN_PRO_FILE ), [ $this, 'plugin_update_message' ] );
    }

    /**
     * Initialize the updater
     *
     * @return void
     */
    public function init_appsero() {
        $client = new Client( '8f0a1669-b8db-46eb-9fc4-02ac5bfe89e7', 'Dokan Pro', DOKAN_PRO_FILE );

        // track plugin install
        $insights = $client->insights();

        if ( false === $insights->tracking_allowed() ) {
            $insights->optin();
        }

        $insights->add_extra(
            function () {
                return [
                    'dokan_pro_version' => DOKAN_PRO_PLUGIN_VERSION,
                    'dokan_pro_plan'    => dokan_pro()->get_plan(),
                    'available_modules' => dokan_pro()->module->get_available_modules(),
                    'activate_modules'  => dokan_pro()->module->get_active_modules(),
                    'wc_version'        => function_exists( 'WC' ) ? WC()->version : null,
                    'dokan_version'     => DOKAN_PLUGIN_VERSION,
                    'dokan_plan_active' => dokan_pro()->license->is_valid() ? 'yes' : 'no',
                ];
            }
        );

        $insights->hide_notice()->init_plugin();

        $this->license = $client->license();

        // just to be safe if old Appsero SDK is being used
        if ( method_exists( $this->license, 'set_option_key' ) ) {
            $this->license->set_option_key( 'dokan_pro_license' );
        }

        // Active automatic updater
        Updater::init( $client );
    }

    /**
     * Add the menu page for the license
     *
     * @return void
     */
    public function add_menu_page() {
        $args = [
            'type'        => 'submenu',
            'menu_title'  => __( 'License', 'dokan' ),
            'page_title'  => __( 'Dokan Pro License', 'dokan' ),
            'capability'  => 'manage_options',
            'parent_slug' => 'dokan',
            'menu_slug'   => 'dokan_updates',
        ];

        $this->license->add_settings_page( $args );
    }

    /**
     * Prompts the user to add license key if it's not already filled out
     *
     * @param array $notices
     *
     * @return array
     */
    public function license_enter_notice( $notices ) {
        if ( $this->has_license_key() ) {
            return $notices;
        }

        $notices[] = [
            'type'        => 'alert',
            'title'       => __( 'Activate Dokan Pro License', 'dokan' ),
            // translators: %1$s is the URL to the Dokan Pro license activation page.
            'description' => sprintf( __( 'Please <a href="%1$s">enter</a> your valid <strong>Dokan Pro</strong> plugin license key to unlock more features, premium support and future updates.', 'dokan' ), admin_url( 'admin.php?page=dokan_updates' ) ),
            'priority'    => 1,
            'actions'     => [
                [
                    'type'   => 'primary',
                    'text'   => __( 'Activate License', 'dokan' ),
                    'action' => admin_url( 'admin.php?page=dokan_updates' ),
                ],
            ],
            'scope' => 'global',
        ];

        return $notices;
    }

    /**
     * Show plugin udpate message
     *
     * @since  2.7.1
     *
     * @param array $args
     *
     * @return void
     */
    public function plugin_update_message( $args ) {
        if ( $this->is_valid() ) {
            return;
        }

        $upgrade_notice = sprintf(
            '</p><p class="dokan-pro-plugin-upgrade-notice" style="background: #dc4b02;color: #fff;padding: 10px;">Please <a href="%s" target="_blank">activate</a> your license key for getting regular updates and support',
            admin_url( 'admin.php?page=dokan_updates' )
        );

        echo apply_filters( $this->product_id . '_in_plugin_update_message', wp_kses_post( $upgrade_notice ) );
    }

    /**
     * If license is valid.
     *
     * @since 3.10.0
     *
     * @return bool|null
     */
    public function is_valid() {
        return wc_string_to_bool( $this->license->is_valid() );
    }

    /**
     * Get the count of days that after the license will expire.
     *
     * @since 3.10.0
     *
     * @return integer|bool
     */
    public function get_expiry_days() {
        $license_data = $this->license->get_license();

        if ( isset( $license_data['expiry_days'] ) ) {
            return $license_data['expiry_days'];
        }

        return 0;
    }

    /**
     * Refresh dokan pro license
     *
     * @since 3.10.0
     *
     * @return void
     */
    public function refresh_license() {
        $this->license->check_license_status();
    }

    /**
     * Returns license source id.
     *
     * @since 3.10.0
     *
     * @return string
     */
    public function get_license_source_id() {
        $license_data = $this->license->get_license();

        if ( ! empty( $license_data['source_id'] ) ) {
            return $license_data['source_id'];
        }

        return '';
    }

    /**
     * Returns dokan pro license plan.
     *
     * @since 3.10.0
     *
     * @return array|string|string[]
     */
    public function get_plan() {
        $subject = $this->get_license_source_id();
        if ( empty( $subject ) ) {
            $subject = dokan_pro()->get_plan();
        }

        $search       = array( 'dokan-litetime-deal-', 'dokan-' );
        $replace      = array( '', '' );
        $license_plan = str_replace( $search, $replace, $subject );

        return $license_plan;
    }

    /**
     * Returns dokan pro license has key or not.
     *
     * @since 3.10.0
     *
     * @return bool
     */
    public function has_license_key() {
        $license_data = $this->license->get_license();

        return ! empty( $license_data['key'] );
    }
}
