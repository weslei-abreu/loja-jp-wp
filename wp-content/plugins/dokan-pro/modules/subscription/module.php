<?php

namespace WeDevs\DokanPro\Modules\ProductSubscription;

use DokanPro\Modules\Subscription\Helper;
use DokanPro\Modules\Subscription\SubscriptionInvoice;
use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Dashboard\ManualOrders\Manager;
use WeDevs\DokanPro\Modules\Subscription\SubscriptionOrderMetaBuilder;

class Module {

    /**
     * Class constructor
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        require_once dirname( __FILE__ ) . '/includes/classes/class-dps-product-pack.php';

        $this->define_constants();
        $this->file_includes();

        // load subscription class
        add_filter( 'dokan_get_class_container', [ __CLASS__, 'load_subscription_class' ] );
        add_action( 'dokan_vendor', [ __CLASS__, 'add_vendor_subscription' ] );

        // Activation and Deactivation hook
        add_action( 'dokan_activated_module_product_subscription', [ $this, 'activate' ] );
        add_action( 'dokan_deactivated_module_product_subscription', [ $this, 'deactivate' ] );
        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
        // Add localize script.
        add_filter( 'dokan_admin_localize_script', array( $this, 'add_subscription_packs_to_localize_script' ) );
        add_filter( 'dokan_get_dashboard_nav_template_dependency', [ $this, 'get_subscription_nav_template_dependency' ] );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'init', array( $this, 'register_scripts' ) );

        // enable the settings only when the subscription is ON
        $enable_option = get_option( 'dokan_product_subscription', array( 'enable_pricing' => 'off' ) );

        if ( ! isset( $enable_option['enable_pricing'] ) || $enable_option['enable_pricing'] != 'on' ) {
            return;
        }

        $this->init_hooks();
    }

    /**
     * Init hooks
     *
     * @return void
     */
    public function init_hooks() {
        // Loads all actions
        add_filter( 'dokan_can_add_product', array( $this, 'seller_add_products' ), 1, 1 );
        add_filter( 'dokan_vendor_can_duplicate_product', array( $this, 'vendor_can_duplicate_product' ) );
        add_filter( 'dokan_update_product_post_data', array( $this, 'make_product_draft' ), 1 );
        add_filter( 'dokan_post_status', [ $this, 'set_product_status' ], 2, 99 );
        add_action( 'dokan_can_post_notice', array( $this, 'display_product_pack' ) );
        add_filter( 'dokan_can_post', array( $this, 'can_post_product' ) );
        add_filter( 'dokan_product_cat_dropdown_args', [ __CLASS__, 'filter_category' ] );
        add_filter( 'dokan_multistep_product_categories', [ $this, 'filter_multistep_category' ] );

        // filter product types
        add_filter( 'dokan_product_types', [ __CLASS__, 'filter_product_types' ], 99 );

        // filter capapbilies of accessing pages
        add_filter( 'map_meta_cap', [ __CLASS__, 'filter_capability' ], 20, 2 );

        // filter gallery iamge uploading
        add_action( 'dokan_product_gallery_image_count', [ $this, 'restrict_gallery_image_count' ] );
        add_action( 'dokan_add_product_js_template_end', [ $this, 'restrict_gallery_image_count' ] );
        add_action( 'woocommerce_before_single_product', [ $this, 'restrict_added_image_display' ] );
        add_filter( 'dokan_new_product_popup_args', [ $this, 'restrict_gallery_image_on_product_create' ], 21, 2 );
        add_filter( 'dokan_restrict_product_image_gallery_on_edit', [ $this, 'restrict_gallery_image_on_product_edit' ], 10, 1 );

        add_action( 'dps_schedule_pack_update', array( $this, 'schedule_task' ) );
        add_action( 'dokan_before_listing_product', array( $this, 'show_custom_subscription_info' ) );
        add_filter( 'woocommerce_register_post_type_product', [ __CLASS__, 'disable_creating_new_product' ] );

        add_filter( 'dokan_get_dashboard_nav', [ __CLASS__, 'add_new_page' ], 11 );
        add_filter( 'dokan_set_template_path', array( $this, 'load_subscription_templates' ), 11, 3 );
        add_action( 'dokan_load_custom_template', array( $this, 'load_template_from_plugin' ) );

        add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'order_needs_processing' ), 10, 2 );
        add_filter( 'woocommerce_add_to_cart_redirect', [ __CLASS__, 'add_to_cart_redirect' ] );
        add_filter( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'maybe_empty_cart' ], 10, 3 );
        add_filter( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'remove_addons_validation' ], 1, 3 );

        add_filter( 'woocommerce_checkout_order_processed', [ $this, 'process_subscription_ordermeta' ], 10, 1 );
        add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'process_subscription_ordermeta' ], 10, 1 );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'filter_payment_gateways' ] );
        add_action( 'woocommerce_order_status_changed', [ $this, 'process_order_pack_product' ], 10, 3 );
        add_action( 'woocommerce_after_checkout_validation', [ $this, 'validate_billing_email' ], 10, 2 );

        add_action( 'template_redirect', array( $this, 'maybe_cancel_or_activate_subscription' ) );
        add_action( 'dps_cancel_recurring_subscription', array( $this, 'cancel_recurring_subscription' ), 10, 2 );
        add_action( 'dps_cancel_non_recurring_subscription', array( $this, 'cancel_non_recurring_subscription' ), 10, 3 );

        add_filter( 'dokan_query_var_filter', [ $this, 'add_subscription_endpoint' ] );

        // Handle popup error if subscription outdated
        add_action( 'dokan_new_product_popup_args', [ __CLASS__, 'can_create_product' ], 20, 2 );

        // remove subscripton product from vendor product listing page
        add_filter( 'dokan_product_listing_exclude_type', array( $this, 'exclude_subscription_product' ) );
        add_filter( 'dokan_count_posts', array( $this, 'exclude_subscription_product_count' ), 10, 3 );

        // remove min max rules for vendor subscription.
        add_filter( 'dokan_validate_min_max_rules_for_product', [ $this, 'remove_min_max_for_subscription_packs' ], 10, 2 );

        // remove subscription product from best selling and top rated product query
        add_filter( 'dokan_best_selling_products_query', array( $this, 'exclude_subscription_product_query' ) );
        add_filter( 'dokan_top_rated_products_query', array( $this, 'exclude_subscription_product_query' ) );

        // Allow vendor to import only allowed number of products
        add_filter( 'woocommerce_product_import_pre_insert_product_object', [ __CLASS__, 'import_products' ] );

        // include rest api class
        add_filter( 'dokan_rest_api_class_map', [ __CLASS__, 'rest_api_class_map' ] );

        // include email class
        add_action( 'dokan_loaded', [ __CLASS__, 'load_emails' ], 20 );

        //Category import restriction if category restriction enable, for XML
        add_filter( 'wp_import_post_data_raw', [ $this, 'restrict_category_on_xml_import' ] );

        //For csv
        add_action( 'woocommerce_product_import_before_process_item', [ $this, 'restrict_category_on_csv_import' ] );

        // for disabling email verification
        add_filter( 'dokan_maybe_email_verification_not_needed', [ $this, 'disable_email_verification' ], 10, 1 );

        // Duplicating product based on subscription
        add_filter( 'dokan_can_duplicate_product', [ $this, 'dokan_can_duplicate_product_on_subscription' ], 10, 1 );

        // Do not allow creating new product if vendor do not have any product remaining.
        add_filter( 'dokan_add_new_product_redirect', [ $this, 'redirect_to_product_edit_screen' ], 10, 2 );

        add_filter( 'dokan_vendor_shop_data', array( $this, 'add_currently_subscribed_pack_info_to_shop_data' ), 10, 2 );
        add_filter( 'dokan_vendor_to_array', array( $this, 'add_currently_subscribed_pack_info_to_shop_data' ), 10, 2 );
        add_action( 'dokan_before_update_vendor', array( $this, 'update_vendor_subscription_data' ), 10, 2 );

        // Order and payment processing for Mangopay
        add_filter( 'dokan_mangopay_needs_cart_validation', [ $this, 'skip_cart_validation_for_mangopay' ] );
        add_filter( 'dokan_mangopay_disburse_payment', [ $this, 'skip_payment_disbursement_for_mangopay' ], 10, 2 );
        add_filter( 'dokan_mangopay_payin_data', [ $this, 'modify_mangopay_payin_data' ] );
        add_filter( 'dokan_catalog_mode_hide_add_to_cart_button', [ $this, 'remove_catalogue_mode_restriction_on_dokan_subscription_product' ], 99, 3 );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'remove_unsupported_payment_gateways_on_dokan_subscription_product' ], 99 );

        // Stores REST API.
        add_filter( 'dokan_rest_api_store_collection_params', [ $this, 'add_params_to_store_collection' ] );
        add_filter( 'dokan_rest_get_stores_args', [ $this, 'rest_get_stores_args' ], 10, 2 );

        // Order Listing Table Actions.
        add_filter( 'woocommerce_my_account_my_orders_actions', [ $this, 'remove_request_warranty_button' ], 20, 2 );
        add_filter( 'dokan_my_account_my_sub_orders_actions', [ $this, 'remove_request_warranty_button' ], 20, 2 );

        // Update subscription list page status
        add_action( 'init', array( $this, 'update_subscriptions_list_page_status' ) );

        // Add filter to check if manual order creation is enabled for a specific subscription package
        add_filter( 'dokan_manual_orders_is_enabled', [ $this, 'is_manual_order_enabled' ], 10, 3 );
    }

    /**
     * Load email classes
     *
     * @return void
     */
    public static function load_emails() {
        add_filter( 'dokan_email_classes', [ __CLASS__, 'register_email_class' ] );
        add_filter( 'dokan_email_actions', [ __CLASS__, 'register_email_action' ] );
        add_filter( 'dokan_email_list', [ __CLASS__, 'register_email_templates' ] );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        do_action( 'dps_schedule_pack_update' );

        if ( false === wp_next_scheduled( 'dps_schedule_pack_update' ) ) {
            wp_schedule_event( time(), 'daily', 'dps_schedule_pack_update' );
        }

        // flush rewrite rules after the plugin is activated
        $this->flush_rewrite_rules();

        // Verify that, is previously product subscription page creation ok or not.
        $saved_page_id = dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );

        if ( empty( $saved_page_id ) || null === get_post( $saved_page_id ) ) {
            $dashboard_page_data  = dokan_get_option( 'dashboard', 'dokan_pages' );
            $dashboard_page_id    = apply_filters( 'dokan_get_dashboard_page_id', $dashboard_page_data );

            $post_id = wp_insert_post(
                [
                    'post_title'   => wp_strip_all_tags( __( 'Product Subscription', 'dokan' ) ),
                    'post_content' => '[dps_product_pack]',
                    'post_status'  => 'auto-draft',
                    'post_parent'  => $dashboard_page_id,
                    'post_type'    => 'page',
                ]
            );

            // Update dokan product subscription settings.
            $subscription_settings   = get_option( 'dokan_product_subscription', array() );
            $subscription_settings['subscription_pack'] = $post_id;
            update_option( 'dokan_product_subscription', $subscription_settings );
        }
    }

    /**
     * Updates the subscription list page status to published if it's in draft.
     *
     * This method ensures the subscription list page is available to users by changing
     * its status from auto-draft to publish. This is important for new installations or
     * when the page was created but not published.
     *
     * @note: the Rank Math SEO and Yoast SEO plugins have similar methods to ensure
     *       their pages are published.
     *
     * @since 3.14.2
     *
     * @return void
     */
    public function update_subscriptions_list_page_status() {
        $page_id = (int) dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );

        // Early return if no page ID is set
        if ( empty( $page_id ) ) {
            return;
        }

        $page = get_post( $page_id );

        // Early return if page doesn't exist or is not in draft status
        if ( ! $page instanceof \WP_Post || 'auto-draft' !== $page->post_status ) {
            return;
        }

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_status' => 'publish',
            ]
        );
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        add_filter( 'dokan_query_var_filter', [ $this, 'add_subscription_endpoint' ] );
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * Placeholder for deactivation function
     */
    public function deactivate() {
        $users = get_users(
            [
                'role'   => 'seller',
                'fields' => [ 'ID', 'user_email' ],
            ]
        );

        foreach ( $users as $user ) {
            Helper::make_product_publish( $user->ID );
        }

        wp_clear_scheduled_hook( 'dps_schedule_pack_update' );
    }

    /**
     * Check is Dokan is plugin or nor
     * @return boolean true|false
     */
    public static function is_dokan_plugin() {
        return defined( 'DOKAN_PLUGIN_VERSION' );
    }

    /**
     * Define constants
     *
     * @return void
     */
    function define_constants() {
        define( 'DPS_PATH', dirname( __FILE__ ) );
        define( 'DPS_URL', plugins_url( '', __FILE__ ) );
    }

    /**
     * Includes required files
     *
     * @return void
     */
    function file_includes() {
        if ( is_admin() ) {
            require_once DPS_PATH . '/includes/admin/admin.php';
        }

        require_once DPS_PATH . '/includes/classes/Helper.php';
        require_once DPS_PATH . '/includes/classes/class-dps-paypal-standard-subscriptions.php';
        require_once DPS_PATH . '/includes/classes/Shortcode.php';
        require_once DPS_PATH . '/includes/classes/Registration.php';
        require_once DPS_PATH . '/includes/Abstracts/VendorSubscription.php';
        require_once DPS_PATH . '/includes/classes/SubscriptionPack.php';
        require_once DPS_PATH . '/includes/classes/ProductStatusChanger.php';
        require_once DPS_PATH . '/includes/classes/SubscriptionOrderMetaBuilder.php';
        require_once DPS_PATH . '/includes/classes/SubscriptionInvoice.php';
    }

    /**
     * Register Scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dps-custom-style', DPS_URL . '/assets/css/style' . $suffix . '.css', [], $version );
        wp_register_script( 'dps-custom-js', DPS_URL . '/assets/js/script' . $suffix . '.js', array( 'jquery' ), $version, true );
        wp_localize_script(
            'dps-custom-js', 'dokanSubscription', array(
                'cancel_string'   => __( 'Do you really want to cancel the subscription?', 'dokan' ),
                'activate_string' => __( 'Want to activate the subscription again?', 'dokan' ),
            )
        );

        $script_assets = DPS_PATH . '/assets/js/frontend-components.asset.php';

        if ( file_exists( $script_assets ) ) {
            $assets = include $script_assets;

            wp_register_style(
                'dokan-vendor-subscription-dashboard',
                DPS_URL . '/assets/js/frontend-components.css',
                [ 'dokan-react-components', 'dps-custom-style' ],
                $assets['version'],
                'all'
            );

            wp_register_script(
                'dokan-vendor-subscription-dashboard',
                DPS_URL . '/assets/js/frontend-components.js',
                array_merge( $assets['dependencies'], [ 'dokan-react-components' ] ),
                $assets['version'],
                true
            );
        }
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     */
    public function enqueue_scripts() {
        global $wp, $typenow, $post;

        $is_subscription_page = dokan_is_seller_dashboard() && isset( $wp->query_vars['subscription'] );
        $is_new_product_page  = is_admin() && isset( $_GET['post_type'] ) && 'product' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ); //phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification

        if ( ! $is_subscription_page && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'dps_product_pack' ) ) {
            $is_subscription_page = true;
        }

        if ( ! $is_subscription_page && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'dokan-vendor-registration' ) ) {
            $is_subscription_page = true;
        }

        if ( $is_subscription_page || 'product' === $typenow || $is_new_product_page || is_account_page() ) {
            wp_enqueue_style( 'dps-custom-style' );
        }

        if ( $is_subscription_page || is_account_page() ) {
            wp_enqueue_script( 'dps-custom-js' );
        }

        if ( dokan_is_seller_dashboard() ) {
            wp_enqueue_script( 'dokan-vendor-subscription-dashboard' );
            wp_enqueue_style( 'dokan-vendor-subscription-dashboard' );
            wp_set_script_translations( 'dokan-vendor-subscription-dashboard', 'dokan' );
        }
    }

    /**
     * Show_custom_subscription_info in Listing products
     */
    public function show_custom_subscription_info() {
        $vendor_id = dokan_get_current_user_id();

        if ( dokan_is_seller_enabled( $vendor_id ) ) {
            $remaining_product = Helper::get_vendor_remaining_products( $vendor_id );

            if ( true === $remaining_product ) {
                return printf( '<p class="dokan-info">%s</p>', __( 'You can add unlimited products', 'dokan' ) );
            }

            if ( $remaining_product == 0 || ! self::can_post_product() ) {
                if ( self::is_dokan_plugin() ) {
                    $permalink = dokan_get_navigation_url( 'subscription' );
                } else {
                    $page_id   = dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );
                    $permalink = get_permalink( $page_id );
                }
                // $page_id = dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );
                $info = sprintf( __( 'Sorry! You can not add or publish any more product. Please <a href="%s">update your package</a>.', 'dokan' ), $permalink );
                echo "<p class='dokan-info'>" . $info . '</p>';
                echo '<style>.dokan-add-product-link{display : none !important}</style>';
            } else {
                echo "<p class='dokan-info'>" . sprintf( __( 'You can add %d more product(s).', 'dokan' ), $remaining_product ) . '</p>';
            }
        }
    }

    /**
     * Add Subscription endpoint to the end of Dashboard
     * @param array $query_var
     * @return array
     */
    public function add_subscription_endpoint( $query_var ) {
        $query_var[] = 'subscription';

        return $query_var;
    }

    /**
     * Get plugin path
     *
     * @since 2.8
     *
     * @return void
     **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Load Dokan subscription templates
     *
     * @since 2.8
     *
     * @return void
     **/
    public function load_subscription_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_subscription'] ) && $args['is_subscription'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Load template for the dashboard
     *
     * @param  array $query_vars
     *
     * @return void
     */
    function load_template_from_plugin( $query_vars ) {
        if ( ! isset( $query_vars['subscription'] ) ) {
            return $query_vars;
        }

        if ( current_user_can( 'vendor_staff' ) ) {
            return dokan_get_template_part( 'global/no-permission' );
        }

        $installed_version = get_option( 'dokan_theme_version' );

        dokan_get_template_part( 'subscription/product_subscription_plugin', '', array( 'is_subscription' => true ) );
    }

    /**
     * Get Vendor Subscription Nav Template Dependency.
     *
     * @since 4.0.0
     *
     * @param array $dependencies
     *
     * @return array
     */
    public function get_subscription_nav_template_dependency( array $dependencies ): array {
        $dependencies['subscription'] = [
            [
                'slug' => 'dashboard/index',
                'name' => '',
                'args' => [
                    'is_subscription' => true,
                ],
            ],
            [
                'slug' => 'dashboard/order-listing',
                'name' => '',
                'args' => [
                    'is_subscription' => true,
                ],
            ],
            [
                'slug' => 'dashboard/pack-listing',
                'name' => '',
                'args' => [
                    'is_subscription' => true,
                ],
            ],

        ];

        return $dependencies;
    }

    /**
     * Add new menu in seller dashboard
     *
     * @param array   $urls
     * @return array
     */
    public static function add_new_page( $urls ) {
        if ( self::is_dokan_plugin() ) {
            $permalink = dokan_get_navigation_url( 'subscription' );
        } else {
            $page_id = dokan_get_option( 'subscription_pack', 'dokan_product_subscription' );
            $permalink = get_permalink( $page_id );
        }

        if ( current_user_can( 'vendor_staff' ) ) {
            return $urls;
        }

        $urls['subscription'] = array(
            'title'       => __( 'Subscription', 'dokan' ),
            'icon'        => '<i class="fas fa-book"></i>',
            'url'         => $permalink,
            'pos'         => 180,
            'react_route' => 'subscription',
        );

        return $urls;
    }

    /**
     * Restriction for adding product for seller
     *
     * @param array   $errors
     * @return string
     */
    public function seller_add_products( $errors ) {
        $user_id = dokan_get_current_user_id();

        if ( dokan_is_user_seller( $user_id ) ) {
            $remaining_product = Helper::get_vendor_remaining_products( $user_id );

            if ( true === $remaining_product ) {
                return;
            }

            if ( $remaining_product <= 0 ) {
                $errors[] = __( 'Sorry your subscription exceeds your package limits please update your package subscription', 'dokan' );
                return $errors;
            } else {
                update_user_meta( $user_id, 'product_no_with_pack', $remaining_product - 1 );
                return $errors;
            }
        }
    }

    /**
     * Vendor can duplicate product
     *
     * @return boolean
     */
    public function vendor_can_duplicate_product() {
        $vendor_id = dokan_get_current_user_id();

        if ( ! Helper::get_vendor_remaining_products( $vendor_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Make product status draft when vendor's remaining product is zero
     *
     * @param array $data
     *
     *  @return array
     */
    public function make_product_draft( $data ) {
        $vendor_id = dokan_get_current_user_id();

        if ( empty( $vendor_id ) && ! empty( $data['ID'] ) ) {
            $vendor_id = dokan_get_vendor_by_product( $data['ID'], true );
        }

        if ( Helper::get_vendor_remaining_products( $vendor_id ) ) {
            return $data;
        }

        // if product status was not publish and pending then make it draft
        $product = wc_get_product( $data['ID'] );

        if ( 'publish' !== $product->get_status() && 'pending' !== $product->get_status() ) {
            $data['post_status'] = 'draft';
        }

        return $data;
    }

    /**
     * Set product edit status
     *
     * @since 3.8.3
     *
     * @param array $all_statuses
     * @param int $product_id
     *
     * @return array
     */
    public function set_product_status( $all_statuses, $product_id ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return $all_statuses;
        }

        $vendor_id = dokan_get_current_user_id();
        if ( Helper::get_vendor_remaining_products( $vendor_id ) ) {
            return $all_statuses;
        }

        if ( ! in_array( $product->get_status(), [ 'publish', 'pending' ], true ) ) {
            unset( $all_statuses['pending'] );
            unset( $all_statuses['publish'] );
            if ( ! array_key_exists( 'draft', $all_statuses ) ) {
                $all_statuses['draft'] = dokan_get_post_status( 'draft' );
            }
        }

        return $all_statuses;
    }

    /**
     * Get number of product by seller
     *
     * @param integer $user_id
     * @return integer
     */
    function get_number_of_product_by_seller( $user_id ) {
        global $wpdb;

        $allowed_status = apply_filters( 'dps_get_product_by_seller_allowed_statuses', array( 'publish', 'pending' ) );

        $query = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = $user_id AND post_type = 'product' AND post_status IN ( '" . implode( "','", $allowed_status ) . "' )";
        $count = $wpdb->get_var( $query );

        return $count;
    }

    /**
     * Check if have pack availability
     *
     * @since 1.2.1
     *
     * @return void
     */
    public static function can_create_product( $errors, $data ) {
        if ( isset( $data['ID'] ) ) {
            return;
        }

        $user_id = dokan_get_current_user_id();

        if ( dokan_is_user_seller( $user_id ) ) {
            $remaining_product = Helper::get_vendor_remaining_products( $user_id );

            if ( true === $remaining_product ) {
                return;
            }

            if ( $remaining_product <= 0 ) {
                $errors = new \WP_Error( 'no-subscription', __( 'Sorry your subscription exceeds your package limits please update your package subscription', 'dokan' ) );
            } else {
                update_user_meta( $user_id, 'product_no_with_pack', $remaining_product - 1 );
            }

            return $errors;
        }
    }

    /**
     * Display Product Pack
     */
    function display_product_pack() {
        if ( dokan_is_seller_enabled( get_current_user_id() ) ) {
            echo do_shortcode( '[dps_product_pack]' );
        } else {
            dokan_seller_not_enabled_notice();
        }
    }

    /**
     * Check is Seller has any subscription
     *
     * @return boolean
     */
    public static function can_post_product() {
        if ( get_user_meta( dokan_get_current_user_id(), 'can_post_product', true ) == '1' ) {
            return true;
        }

        return false;
    }

    /**
     * Filter vendor category according to subscription
     *
     * @since 1.1.5
     *
     * @return void
     **/
    public static function filter_category( $args ) {
        $user_id = get_current_user_id();

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return $args;
        }

        $is_seller_enabled = dokan_is_seller_enabled( $user_id );

        if ( ! $is_seller_enabled ) {
            return $args;
        }

        $vendor = dokan()->vendor->get( $user_id )->subscription;

        if ( ! $vendor ) {
            return $args;
        }

        if ( ( self::can_post_product() ) && $vendor->has_subscription() ) {
            $override_cat = get_user_meta( $user_id, 'vendor_allowed_categories', true );
            $selected_cat = ! empty( $override_cat ) ? $override_cat : $vendor->get_allowed_product_categories();

            if ( empty( $selected_cat ) ) {
                return $args;
            }

            $args['include'] = apply_filters( 'dokan_pro_subscription_allowed_categories', $selected_cat );
            return $args;
        }

        return $args;
    }

    /**
     * Filter product types for a vendor
     *
     * @param  array $types
     *
     * @return array
     */
    public static function filter_product_types( $types ) {
        $user_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return $types;
        }

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            return $types;
        }

        $allowed_product_types = Helper::get_vendor_allowed_product_types();

        if ( ! $allowed_product_types ) {
            return $types;
        }

        $types = array_filter(
            $types, function( $value, $key ) use ( $allowed_product_types ) {
            return in_array( $key, $allowed_product_types );
        }, ARRAY_FILTER_USE_BOTH
        );

        return $types;
    }

    /**
     * Filter capability for vendor
     *
     * @param  array $caps
     * @param  string $cap
     *
     * @return array
     */
    public static function filter_capability( $caps, $cap ) {
        global $wp_query;

        // if not vendor dashboard and not product edit page
        if ( ! dokan_is_seller_dashboard() && empty( $wp_query->query_vars['edit'] ) ) {
            return $caps;
        }

        if ( 'dokan_view_product_menu' === $cap ) {
            $allowed_product_types = Helper::get_vendor_allowed_product_types();

            $default_types = [ 'simple', 'variable', 'grouped', 'external' ];

            // if no other default product is selected ( ei: dokan_get_product_types() ) then don't show the product menu
            if ( $allowed_product_types && ! array_intersect( $default_types, $allowed_product_types ) ) {
                return [ 'no_permission' ];
            }
        }

        if ( 'dokan_view_booking_menu' === $cap ) {
            $allowed_product_types = Helper::get_vendor_allowed_product_types();

            if ( $allowed_product_types && ! in_array( 'booking', $allowed_product_types ) ) {
                return [ 'no_permission' ];
            }
        }

        if ( 'dokan_view_auction_menu' === $cap ) {
            $allowed_product_types = Helper::get_vendor_allowed_product_types();

            if ( $allowed_product_types && ! in_array( 'auction', $allowed_product_types ) ) {
                return [ 'no_permission' ];
            }
        }

        return $caps;
    }

    /**
     * Schedule task daily update this functions
     */
    public function schedule_task() {
        if ( ! function_exists( 'dokan' ) || ! dokan()->vendor ) {
            return;
        }

        $users = get_users(
            [
                'role__in'   => [ 'seller', 'administrator' ],
                'fields' => [ 'ID', 'user_email' ],
            ]
        );

        foreach ( $users as $user ) {
            $vendor_subscription = dokan()->vendor->get( $user->ID )->subscription;

            // if no vendor is not subscribed to any pack, skip the vendor, this process also enable code editor autocomplete/quick access support.
            if ( ! $vendor_subscription instanceof \DokanPro\Modules\Subscription\SubscriptionPack ) {
                continue;
            }

            if ( ! Helper::is_subscription_product( $vendor_subscription->get_id() ) ) {
                continue;
            }

            if ( Helper::maybe_cancel_subscription( $user->ID ) ) {
                if ( Helper::check_vendor_has_existing_product( $user->ID ) ) {
                    Helper::make_product_draft( $user->ID );
                }

                $order_id = get_user_meta( $user->ID, 'product_order_id', true );

                if ( $order_id ) {
                    $subject = ( dokan_get_option( 'cancelling_email_subject', 'dokan_product_subscription' ) ) ? dokan_get_option( 'cancelling_email_subject', 'dokan_product_subscription' ) : __( 'Subscription Package Cancel notification', 'dokan' );
                    $message = ( dokan_get_option( 'cancelling_email_body', 'dokan_product_subscription' ) ) ? dokan_get_option( 'cancelling_email_body', 'dokan_product_subscription' ) : __( 'Dear subscriber, Your subscription has expired. Please renew your package to continue using it.', 'dokan' );
                    $headers = 'From: ' . get_option( 'blogname' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";

                    wp_mail( $user->user_email, $subject, $message, $headers );

                    Helper::log( 'Subscription cancel check: As the package has expired for order #' . $order_id . ', we are cancelling the Subscription Package of user #' . $user->ID );
                    Helper::delete_subscription_pack( $user->ID, $order_id );
                }
            }

            $is_seller_enabled  = dokan_is_seller_enabled( $user->ID );
            $can_post_product   = $vendor_subscription->can_post_product();
            $has_recurring_pack = $vendor_subscription->has_recurring_pack();
            $has_subscription   = $vendor_subscription->has_subscription();

            if ( ! $has_recurring_pack && $is_seller_enabled && $has_subscription && $can_post_product ) {
                if ( Helper::alert_before_two_days( $user->ID ) ) {
                    $subject = ( dokan_get_option( 'alert_email_subject', 'dokan_product_subscription' ) ) ? dokan_get_option( 'alert_email_subject', 'dokan_product_subscription' ) : __( 'Subscription Ending Soon', 'dokan' );
                    $message = ( dokan_get_option( 'alert_email_body', 'dokan_product_subscription' ) ) ? dokan_get_option( 'alert_email_body', 'dokan_product_subscription' ) : __( 'Dear subscriber, Your subscription will be ending soon. Please renew your package in a timely manner for continued usage.', 'dokan' );
                    $headers = 'From: ' . get_option( 'blogname' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";

                    wp_mail( $user->user_email, $subject, $message, $headers );
                    update_user_meta( $user->ID, 'dokan_vendor_subscription_cancel_email', 'yes' );
                }
            }
        }
    }

    /**
     * Adds order metadata for subscription product.
     *
     * @since 3.7.10
     *
     * @param int $order_id
     *
     * @return void
     */
    public function process_subscription_ordermeta( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $product = Helper::get_vendor_subscription_product_by_order( $order );
        if ( ! $product ) {
            return;
        }

        // We need to make sure the order meta data gets saved only once
        $pack_validity = $product->get_meta( '_pack_validity', true );
        $pack_validity = empty( $pack_validity ) || ! is_numeric( $pack_validity )
            ? 'unlimited'
            : dokan_current_datetime()->modify( "+$pack_validity days" )->format( 'Y-m-d H:i:s' );

        $meta_builder = new SubscriptionOrderMetaBuilder( $order );
        $meta_builder->set_pack_validity_end_date( $pack_validity )
            ->set_no_of_allowed_products( $product->get_meta( '_no_of_product', true ) )
            ->set_subscription_product_admin_commission_type( $product->get_meta( '_subscription_product_admin_commission_type', true ) )
            ->set_subscription_product_admin_commission( $product->get_meta( '_subscription_product_admin_commission', true ) )
            ->set_subscription_product_admin_additional_fee( $product->get_meta( '_subscription_product_admin_additional_fee', true ) )
            ->set_subscription_product_admin_category_based_commission( $product->get_meta( '_subscription_product_admin_category_based_commission', true ) )
            ->set_is_vendor_subscription_order( 'yes' )
            ->build()
            ->save();
    }

    /**
     * Process order for specific package
     *
     * @param integer $order_id
     * @param string  $old_status
     * @param string  $new_status
     *
     * @return void
     */
    public function process_order_pack_product( $order_id, $old_status, $new_status ) {
        if ( $old_status === $new_status ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $product = Helper::get_vendor_subscription_product_by_order( $order );
        if ( ! $product ) {
            return;
        }

        if ( 'completed' !== $new_status ) {
            return;
        }

        $customer_id = $order->get_customer_id();

        // While the user doesn't exist, create it as a vendor
        if ( empty( $customer_id ) ) {
            $user_data = [
                'first_name' => $order->get_billing_first_name(),
                'last_name'  => $order->get_billing_last_name(),
            ];

            $email = $order->get_billing_email();

            /*
             * The user could already be created with the
             * billing email but is not just logged in.
             * So, we should avoid recreating the user.
             */
            $user = get_user_by( 'email', $email );

            if ( ! $user ) {
                $user_data['user_login']    = wc_create_new_customer_username( $email, $user_data );
                $user_data['email']         = $email;
                $user_data['phone']         = $order->get_billing_phone();
                $user_data['notify_vendor'] = true;
                $user_data['enabled']       = true;
                $user_data['store_name']    = $order->get_formatted_billing_full_name();
                $user_data['address']       = [
                    'street_1' => $order->get_billing_address_1(),
                    'street_2' => $order->get_billing_address_2(),
                    'city'     => $order->get_billing_city(),
                    'zip'      => $order->get_billing_postcode(),
                    'state'    => $order->get_billing_state(),
                    'country'  => $order->get_billing_country(),
                ];

                $vendor = dokan()->vendor->create( $user_data );

                if ( is_wp_error( $vendor ) ) {
                    return;
                }

                $customer_id = $vendor->get_id();
            } else {
                $customer_id = $user->ID;
            }

            /*
             * As the user didn't exist earlier, we need
             * to set the customer id as we have created
             * the user by now.
             */
            $order->set_customer_id( $customer_id );
            $order->save();
        }

        // Register the user as vendor if it is already not.
        if ( ! dokan_is_user_seller( $customer_id ) ) {
            $user = get_userdata( $customer_id );
            if ( ! $user ) {
                return;
            }

            $user_data = [
                'fname'    => ! empty( $user->first_name ) ? $user->first_name : $order->get_billing_first_name(),
                'lname'    => ! empty( $user->last_name ) ? $user->last_name : $order->get_billing_last_name(),
                'shopurl'  => $user->user_nicename,
                'shopname' => ! empty( $user->display_name ) ? $user->display_name : $order->get_formatted_billing_full_name(),
                'phone'    => $order->get_billing_phone(),
                'address'  => [
                    'street_1' => $order->get_billing_address_1(),
                    'street_2' => $order->get_billing_address_2(),
                    'city'     => $order->get_billing_city(),
                    'zip'      => $order->get_billing_postcode(),
                    'state'    => $order->get_billing_state(),
                    'country'  => $order->get_billing_country(),
                ],
            ];

            dokan_user_update_to_seller( $user, $user_data );
        }

        $product_id = $product->get_id();

        if ( ! Helper::has_used_trial_pack( $customer_id ) ) {
            Helper::add_used_trial_pack( $customer_id, $product_id );
        }

        if ( Helper::is_recurring_pack( $product_id ) ) {
            return;
        }

        // If order has pack validity get it, or get validity from product and format it as Y-m-d H:i:s otherwise validity will be unlimited.
        if ( $order->meta_exists( '_pack_validity' ) ) {
            $pack_validity = $order->get_meta( '_pack_validity', true  );
        } else {
            $product_pack_validity = $product->get_meta( '_pack_validity', true  );

            $pack_validity = empty( $product_pack_validity ) || ! is_numeric( $product_pack_validity )
                ? 'unlimited'
                : dokan_current_datetime()->modify( "+$product_pack_validity days" )->format( 'Y-m-d H:i:s' );
        }

        $num_product               = $order->meta_exists( '_no_of_product' ) ? $order->get_meta( '_no_of_product', true ) : $product->get_meta( '_no_of_product', true );
        $admin_commission          = $order->meta_exists( '_subscription_product_admin_commission' ) ? $order->get_meta( '_subscription_product_admin_commission', true ) : $product->get_meta( '_subscription_product_admin_commission', true );
        $admin_additional_fee      = $order->meta_exists( '_subscription_product_admin_additional_fee' ) ? $order->get_meta( '_subscription_product_admin_additional_fee', true ) : $product->get_meta( '_subscription_product_admin_additional_fee', true );
        $admin_commission_type     = $order->meta_exists( '_subscription_product_admin_commission_type' ) ? $order->get_meta( '_subscription_product_admin_commission_type', true ) : $product->get_meta( '_subscription_product_admin_commission_type', true );
        $category_admin_commission = $order->meta_exists( '_subscription_product_admin_category_based_commission' ) ? $order->get_meta( '_subscription_product_admin_category_based_commission', true ) : $product->get_meta( '_subscription_product_admin_category_based_commission', true );

        update_user_meta( $customer_id, 'product_pack_enddate', $pack_validity );
        update_user_meta( $customer_id, 'product_package_id', $product_id );
        update_user_meta( $customer_id, 'product_order_id', $order_id );
        update_user_meta( $customer_id, 'product_no_with_pack', $num_product );
        update_user_meta( $customer_id, 'product_pack_startdate', dokan_current_datetime()->format( 'Y-m-d H:i:s' ) );
        update_user_meta( $customer_id, 'can_post_product', '1' );
        update_user_meta( $customer_id, '_customer_recurring_subscription', '' );

        $vendor = dokan()->vendor->get( $customer_id );
        $vendor->save_commission_settings(
            [
                'percentage'           => $admin_commission,
                'type'                 => $admin_commission_type,
                'flat'                 => $admin_additional_fee,
                'category_commissions' => $category_admin_commission,
            ]
        );

        do_action( 'dokan_vendor_purchased_subscription', $customer_id );
    }

    /**
     * Validates billing email before checkout.
     *
     * This applies for vendor subscription when the user
     * is not logged in. As the user will be created using
     * the billing email after a successful checkout, we
     * need to make sure the billing email does not belong
     * to any existing user.
     *
     * @since 3.7.10
     *
     * @param array $data
     * @param object $errors
     *
     * @return void
     */
    public function validate_billing_email( $data, $errors ) {
        if ( ! Helper::cart_contains_subscription() ) {
            return;
        }

        if ( empty( $data['billing_email'] ) ) {
            return;
        }

        if ( is_user_logged_in() ) {
            return;
        }

        $user = get_user_by( 'email', $data['billing_email'] );
        if ( $user ) {
            $errors->add(
                'dokan-duplicate-email',
                __( 'A user already exists associated with the billing email. If this email belongs to you, please log in to your account first. Otherwise try using another email.', 'dokan' )
            );
        }
    }

    /**
     * Filters available payment gateways as needed.
     * For example, COD should not be available for recurring subscription.
     *
     * @since 3.7.10
     *
     * @param array $available_gateways
     *
     * @return array
     */
    public function filter_payment_gateways( $available_gateways ) {
        if ( Helper::cart_contains_recurring_subscription_product() ) {
            unset( $available_gateways['cod'] );
        }

        return $available_gateways;
    }

    /**
     * Redirect after add product into cart
     *
     * @param string $url url
     * @return string $url
     */
    public static function add_to_cart_redirect( $url ) {
        $product_id = isset( $_REQUEST['add-to-cart'] ) ? intval( $_REQUEST['add-to-cart'] ) : 0;

        if ( ! $product_id ) {
            return $url;
        }

        // If product is of the subscription type
        if ( ! Helper::is_subscription_product( $product_id ) ) {
            return $url;
        }

        $url = wc_get_checkout_url();

        if ( Helper::is_recurring_pack( $product_id ) && ! is_user_logged_in() ) {
            WC()->cart->empty_cart();

            wc_clear_notices();
            wc_add_notice(
                __( 'You need to be logged in to buy a recurring subscription pack. Please log in or create an account to proceed to the checkout.', 'dokan' ),
                'notice'
            );

            $url = add_query_arg(
                [
                    'redirect_to' => $url, // redirect to checkout page after logging in
                ],
                get_permalink( get_option( 'woocommerce_myaccount_page_id' ) )
            );
        }

        return $url;
    }


    /**
     * When a subscription is added to the cart, remove other products/subscriptions to
     * work with PayPal Standard, which only accept one subscription per checkout.
     */
    public static function maybe_empty_cart( $valid, $product_id, $quantity ) {
        if ( Helper::is_subscription_product( $product_id ) ) {
            WC()->cart->empty_cart();
        }

        if ( Helper::cart_contains_subscription() ) {
            Helper::remove_subscriptions_from_cart();

            wc_add_notice( __( 'A subscription has been removed from your cart. Due to payment gateway restrictions, products and subscriptions can not be purchased at the same time.', 'dokan' ) );
        }

        return $valid;
    }

    /**
     * Remove addon required validation for dokan subscription product
     *
     * @param bool $valid
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public static function remove_addons_validation( $valid, $product_id, $quantity ) {
        if ( Helper::is_subscription_product( $product_id ) && class_exists( 'WC_Product_Addons_Cart' ) ) {
            remove_filter( 'woocommerce_add_to_cart_validation', array( $GLOBALS['Product_Addon_Cart'], 'validate_add_cart_item' ), 999 );
        }

        return $valid;
    }

    /**
     * Tell WC that we don't need any processing
     *
     * @param  bool $needs_processing
     * @param  array $product
     * @return bool
     */
    function order_needs_processing( $needs_processing, $product ) {
        if ( $product->get_type() == 'product_pack' ) {
            $needs_processing = false;
        }

        return $needs_processing;
    }

    public function maybe_cancel_or_activate_subscription() {
        $posted_data     = wp_unslash( $_POST );
        $cancel_action   = ! empty( $posted_data['dps_cancel_subscription'] ) ? 'cancel' : '';
        $activate_action = ! empty( $posted_data['dps_activate_subscription'] ) ? 'activate' : '';
        $nonce           = $cancel_action ? 'dps-sub-cancel' : 'dps-sub-activate';

        if ( ! $cancel_action && ! $activate_action ) {
            return;
        }

        if ( ! wp_verify_nonce( $posted_data['_wpnonce'], $nonce ) ) {
            wp_die( __( 'Nonce failure', 'dokan' ) );
        }

        $user_id  = get_current_user_id();
        $order_id = get_user_meta( $user_id, 'product_order_id', true );

        if ( self::is_dokan_plugin() ) {
            $page_url = dokan_get_navigation_url( 'subscription' );
        } else {
            $page_url = get_permalink( dokan_get_option( 'subscription_pack', 'dokan_product_subscription' ) );
        }

        if ( $cancel_action ) {
            $cancel_immediately = false;

            if ( $order_id && get_user_meta( $user_id, '_customer_recurring_subscription', true ) == 'active' ) {
                Helper::log( 'Subscription cancel check: User #' . $user_id . ' has canceled his Subscription of order #' . $order_id );
                do_action( 'dps_cancel_recurring_subscription', $order_id, $user_id, $cancel_immediately );
                wp_redirect( add_query_arg( array( 'msg' => 'dps_sub_cancelled' ), $page_url ) );
                exit;
            } else {
                Helper::log( 'Subscription cancel check: User #' . $user_id . ' has canceled his Subscription of order #' . $order_id );
                do_action( 'dps_cancel_non_recurring_subscription', $order_id, $user_id, $cancel_immediately );
                wp_redirect( add_query_arg( array( 'msg' => 'dps_sub_cancelled' ), $page_url ) );
                exit;
            }
        }

        if ( $activate_action ) {
            Helper::log( 'Subscription activation check: User #' . $user_id . ' has reactivate his Subscription of order #' . $order_id );
            do_action( 'dps_activate_recurring_subscription', $order_id, $user_id );
            wp_redirect( add_query_arg( array( 'msg' => 'dps_sub_activated' ), $page_url ) );
        }
    }

    /**
     * Cancel recurrring subscription via paypal
     *
     * @since 1.2.1
     *
     * @return void
     **/
    public function cancel_recurring_subscription( $order_id, $user_id ) {
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( $order && 'paypal' === $order->get_payment_method() ) {
            \DPS_PayPal_Standard_Subscriptions::cancel_subscription_with_paypal( $order_id, $user_id );
        }
    }

    /**
     * Cancel non recurring subscription
     *
     * @since 3.0.3
     *
     * @param int $order_id
     * @param int $vendor_id
     *
     * @return void
     */
    public function cancel_non_recurring_subscription( $order_id, $vendor_id, $cancel_immediately ) {
        /**
         * @since 3.3.7 Introduce new filter hook: dps_cancel_non_recurring_subscription_immediately
         * @param bool $cancel_immediately
         * @param int $order_id
         * @param int $vendor_id
         */
        $cancel_immediately = apply_filters( 'dps_cancel_non_recurring_subscription_immediately', $cancel_immediately, $order_id, $vendor_id );

        if ( $cancel_immediately || 'unlimited' === Helper::get_pack_end_date( $vendor_id ) ) {
            Helper::delete_subscription_pack( $vendor_id, $order_id );
            return;
        }

        $subscription = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $subscription ) {
            /* translators: 1) vendor id */
            dokan_log( sprintf( __( 'Unable to find subscription to be cancelled for vendor id# %s', 'dokan' ), $vendor_id ) );
            return;
        }

        /**
         * Trigger subscription cancellation email for validity packs.
         *
         * @since 3.11.3 added $order_id as hook argument
         *
         * @param int      $vendor_id
         * @param int|bool $package_id
         * @param int      $order_id
         */
        do_action( 'dokan_subscription_cancelled', $vendor_id, get_user_meta( $vendor_id, 'product_package_id', true ), $order_id );

        $subscription->set_active_cancelled_subscription();
    }

    /**
     * Disable creating new product from backend
     *
     * @param  array $args
     *
     * @return array
     */
    public static function disable_creating_new_product( $args ) {
        $user_id = dokan_get_current_user_id();

        if ( current_user_can( 'manage_woocommerce' ) ) {
            return $args;
        }

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return $args;
        }

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            return $args;
        }

        $remaining_product = Helper::get_vendor_remaining_products( $user_id );

        if ( $remaining_product == 0 || ! self::can_post_product() ) {
            $args['capabilities']['create_posts'] = 'do_not_allow';
        }

        return $args;
    }

    /**
     * Exclude subscription product from product listing page
     *
     * @param  array $terms
     *
     * @return array
     */
    public function exclude_subscription_product( $terms ) {
        $terms[] = 'product_pack';

        return $terms;
    }

    /**
     * Exclude subscription product from total product count
     *
     * @param  string $query
     *
     * @return string
     */
    public function exclude_subscription_product_count( $query, $post_type, $user_id ) {
        global $wpdb;

        $query = "SELECT post_status,
            COUNT( ID ) as num_posts
            FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_author = %d
            AND ID NOT IN (
                SELECT object_id
                FROM {$wpdb->term_relationships}
                WHERE term_taxonomy_id = (
                    SELECT term_id FROM {$wpdb->terms}
                    WHERE name = 'product_pack'
                )
            )
            GROUP BY post_status";

        $results = $wpdb->get_results(
            $wpdb->prepare(
                $query,
                $post_type, $user_id
            ),
            ARRAY_A
        );
    }

    /**
     * Import number of allowed products
     *
     * @param object $object
     * @throws \ReflectionException
     * @return object
     */
    public static function import_products( $object ) {
        $user_id = dokan_get_current_user_id();

        if ( user_can( $user_id, 'manage_woocommerce' ) ) {
            return $object;
        }

        $user_remaining_product = Helper::get_vendor_remaining_products( $user_id );

        // true means unlimited products
        if ( true === $user_remaining_product ) {
            return $object;
        }

        if ( $user_remaining_product < 1 ) {
            $rf = new \ReflectionProperty( get_class( $object ), 'data_store' );

            if ( ! is_object( $rf ) ) {
                return $object;
            }

            $rf->setAccessible( true );
            $rf->setValue( $object, null );
        }

        return $object;
    }

    /**
     * Include subscription api class
     *
     * @param  array $classes
     *
     * @return array
     */
    public static function rest_api_class_map( $classes ) {
        $class = [
            dirname( __FILE__ ) . '/api/class-subscription-controller.php'                 => 'Dokan_REST_Subscription_Controller',
            dirname( __FILE__ ) . '/api/class-vendor-subscription-controller.php'          => 'Dokan_REST_Vendor_Subscription_Controller',
            dirname( __FILE__ ) . '/api/class-vendor-subscription-packages-controller.php' => 'Dokan_REST_Vendor_Subscription_Packages_Controller',
            dirname( __FILE__ ) . '/api/class-vendor-subscription-orders-controller.php'   => 'Dokan_REST_Vendor_Subscription_Orders_Controller',
        ];

        return array_merge( $classes, $class );
    }

    /**
     * Register email class
     *
     * @param  array $wc_emails
     *
     * @return array
     */
    public static function register_email_class( $wc_emails ) {
        $wc_emails['Dokan_Subscription_Cancelled']        = require_once DPS_PATH . '/includes/emails/subscription-cancelled.php';
        $wc_emails['Dokan_Subscription_Cancelled_vendor'] = require_once DPS_PATH . '/includes/emails/subscription-cancelled-vendor.php';

        return $wc_emails;
    }

    /**
     * Register email action
     *
     * @param array $actions
     *
     * @return array
     */
    public static function register_email_action( $actions ) {
        $actions[] = 'dokan_subscription_cancelled';

        return $actions;
    }

    /**
     * Register email templates.
     *
     * @param array $template
     *
     * @return array
     */
    public static function register_email_templates( $template ) {
        $template[] = 'dokan-subscription-cancelled.php';
        $template[] = 'dokan-subscription-cancelled-vendor.php';

        return $template;
    }

    /**
     * Load subscription class
     *
     * @param array $classes
     *
     * @return array
     */
    public static function load_subscription_class( $classes ) {
        $classes['subscription']         = new SubscriptionPack();
        $classes['subscription_invoice'] = new SubscriptionInvoice();

        return $classes;
    }

    /**
     * Add vendor subscriptionn class
     *
     * @param object $vendor
     *
     * @return void
     */
    public static function add_vendor_subscription( $vendor ) {
        $subscription_pack = null;

        if ( $vendor->get_id() && dokan_is_user_seller( $vendor->get_id() ) ) {
            $subscription_pack_id = get_user_meta( $vendor->get_id(), 'product_package_id', true );

            if ( $subscription_pack_id ) {
                // $subscription_pack = new Dokan_Subscription_Pack( $subscription_pack_id );
                return $vendor->subscription = new SubscriptionPack( $subscription_pack_id, $vendor->get_id() );
            }
        }

        $vendor->subscription = $subscription_pack;
    }

    /**
     * Exclude subscription products from the best selling products
     *
     * @since 2.9.10
     *
     * @param array $args
     *
     * @return array
     */
    public function exclude_subscription_product_query( $args ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'product_pack',
            'operator' => 'NOT IN',
        ];

        return $args;
    }

    /**
     * Restricts the number of gallery images based on the vendor's subscription.
     *
     * This method checks the vendor's image limit, calculates the current number of images,
     * generates a warning message if necessary, and renders the JavaScript to enforce the limit.
     *
     * @return void
     */
    public function restrict_gallery_image_count() {
        $image_count = $this->get_restricted_image_count();
        if ( $image_count === -1 ) {
            return;
        }

        if ( $image_count >= 0 ) {
            $gallery_meta = get_post_meta( get_the_ID(), '_product_image_gallery', true );
            $current_image_count = $gallery_meta ? count( explode( ',', $gallery_meta ) ) : 0;

            $warning_message = $this->get_warning_message( $image_count, $current_image_count );

            $this->render_gallery_restriction_script( $image_count, $current_image_count, $warning_message );
        }
    }

    /**
     * Generates a warning message if the current image count exceeds the limit.
     *
     * @since 3.12.2
     *
     * @param int $image_count The maximum number of images allowed.
     * @param int $current_image_count The current number of images in the gallery.
     *
     * @return string The formatted warning message, or an empty string if not needed.
     */
    private function get_warning_message( int $image_count, int $current_image_count ): string {
        if ( $current_image_count <= $image_count ) {
            return '';
        }

        return sprintf(
        // translators: 1) image limit 2) current image count
            esc_html__( 'Warning: Your image limit is %1$d, but you have %2$d images. Please remove excess images or they will be automatically deleted when saving.', 'dokan' ),
            $image_count,
            $current_image_count
        );
    }

    /**
     * Renders the JavaScript code to enforce gallery image restrictions.
     *
     * This method outputs a <script> tag with JavaScript code that handles the UI updates
     * for gallery image restrictions, including updating button states, showing/hiding elements,
     * and displaying warning messages.
     *
     * @since 3.7.4
     *
     * @param int $image_count The maximum number of images allowed.
     * @param int $current_image_count The current number of images in the gallery.
     * @param string $warning_message The warning message to display if the limit is exceeded.
     *
     * @return void
     */
    private function render_gallery_restriction_script( int $image_count, int $current_image_count, string $warning_message ) {
        ?>
        <script type="text/javascript">
            ( function () {
                const IMAGE_COUNT_LIMIT = <?php echo json_encode( $image_count, JSON_HEX_TAG ); ?>;
                const WARNING_MESSAGE = <?php echo json_encode( $warning_message ); ?>;

                function updateUI() {
                    const addedImages = document.querySelectorAll("#product_images_container .image").length;
                    const selectedImages = document.querySelectorAll("[aria-checked='true']").length;
                    const submitButton = document.querySelector('.media-toolbar button');
                    const addImageButton = document.querySelector("#product_images_container .add-image");

                    // Update submit button state
                    if (submitButton && !['Set featured image', 'Set variation image'].includes(submitButton.innerText)) {
                        submitButton.disabled = (selectedImages + addedImages > IMAGE_COUNT_LIMIT) || (selectedImages < 1);
                    }

                    // Update add image button visibility
                    if (addImageButton) {
                        addImageButton.style.display = addedImages >= IMAGE_COUNT_LIMIT ? 'none' : '';
                    }

                    // Show or remove warning message based on current image count
                    const warningMessage = document.getElementById('dokan-image-limit-warning');
                    if (addedImages > IMAGE_COUNT_LIMIT && WARNING_MESSAGE) {
                        if (!warningMessage) {
                            const container = document.querySelector("#product_images_container");
                            const warning = document.createElement('div');
                            warning.id = 'dokan-image-limit-warning';
                            warning.className = 'dokan-alert dokan-alert-warning';
                            warning.innerHTML = WARNING_MESSAGE;
                            container.appendChild(warning);
                        }
                    } else if (warningMessage) {
                        warningMessage.remove();
                    }
                }

                function initializeMutationObserver() {
                    const observer = new MutationObserver(() => {
                        if (document.querySelector('.attachments-browser ul')) {
                            updateUI();
                        }
                    });

                    observer.observe(document.body, { childList: true, subtree: true });
                }

                function setupImageDeletionListener() {
                    const imageContainer = document.querySelector("#product_images_container");
                    if (imageContainer) {
                        imageContainer.addEventListener('click', (event) => {
                            if (event.target.matches('.image a.action-delete')) {
                                setTimeout(updateUI, 100);
                            }
                        });
                    }
                }

                function initializeProductImageLimitation() {
                    updateUI();
                    initializeMutationObserver();
                    setupImageDeletionListener();
                }

                document.addEventListener('DOMContentLoaded', initializeProductImageLimitation);
            } )();
        </script>
        <?php
    }

    /**
     * Restrict already added gallery image using woocommerce_before_single_product
     *
     * @return void
     */
    public function restrict_added_image_display() {
        global $product, $post;

        $image_count = $this->get_restricted_image_count( $post->post_author );
        if ( $image_count == - 1 ) {
            return;
        }

        $product_gallery_image = $this->count_filter( $product->get_gallery_image_ids(), $image_count );
        $product->set_gallery_image_ids( $product_gallery_image );
    }

    /**
     * Restricted gallery image count for vendor subscription
     *
     * @return int
     */
    public function get_restricted_image_count( $vendor_id = null ) {
        $vendor_id = ! empty( $vendor_id ) ? $vendor_id : dokan_get_current_user_id();
        $vendor    = dokan()->vendor->get( $vendor_id )->subscription;

        if ( $vendor && $vendor->is_gallery_image_upload_restricted() ) {
            return $vendor->gallery_image_upload_count();
        }

        return -1;
    }

    /**
     * Restrict gallery image  when creating product
     *
     * @param string $errors
     * @param array $data
     *
     * @return \WP_Error|string
     */
    public function restrict_gallery_image_on_product_create( $errors, $data ) {
        $gallery_image = ! empty( $data['product_image_gallery'] ) ? array_filter( explode( ',', wc_clean( $data['product_image_gallery'] ) ) ) : [];
        $image_count   = $this->get_restricted_image_count();
        if ( $image_count == - 1 ) {
            return $errors;
        }

        if ( count( $gallery_image ) > $image_count ) {
            return new \WP_Error( 'not-allowed', __( sprintf( 'You are not allowed to add more than %s gallery images', $image_count ), 'dokan' ) );
        }

        return $errors;
    }

    /**
     * Restrict gallery image when editing product
     *
     * @param $postdata
     *
     * @return array
     */
    public function restrict_gallery_image_on_product_edit( $postdata ) {
        $gallery_image = ! empty( $postdata['product_image_gallery'] ) ? array_filter( explode( ',', wc_clean( $postdata['product_image_gallery'] ) ) ) : [];
        $image_count   = $this->get_restricted_image_count();
        if ( $image_count === - 1 ) {
            return $postdata;
        }
        $postdata['product_image_gallery'] = implode( ',', $this->count_filter( $gallery_image, $image_count ) );

        // Add a notice if images were removed
        if ( count( $gallery_image ) > $image_count ) {
            $removed_count = count( $gallery_image ) - $image_count;
            wc_add_notice(
                esc_html(
                    sprintf(
                        __( 'Due to your current subscription limit, %1$d images were automatically removed from the product gallery. The remaining %2$d images have been saved.', 'dokan' ),
                        $removed_count,
                        $image_count
                    )
                ),
                'notice'
            );
        }

        return $postdata;
    }

    /**
     * Count filter
     *
     * @param array $arr
     * @param int $count
     *
     * @return array
     */
    public function count_filter( $arr, $count ) {
        return array_filter( $arr, function ( $item, $key ) use ( $count ) {
            return $key <= $count - 1;
        }, ARRAY_FILTER_USE_BOTH );
    }

    /**
     * Restrict category if selected category found
     *
     * * @since 3.1.0
     *
     * @param $post
     *
     * @return null|\WP_Post $post
     */
    public function restrict_category_on_xml_import( $post ) {
        $category_name = array_values(
            array_map(
                function ( $category ) {
                    return $category['name'];
                }, array_filter(
                    $post['terms'], function ( $term ) {
                    return 'product_cat' === $term['domain'];
                }
                )
            )
        )[0];

        $allowed_categories = $this->get_vendor_allowed_categories();

        if ( ! empty( $allowed_categories ) ) {
            $categories = [];
            foreach ( $allowed_categories as $allowed_category ) {
                $categories[] = strtolower( get_term_field( 'name', $allowed_category ) );
            }

            if ( in_array( strtolower( $category_name ), $categories ) ) {
                return $post;
            }

            return null;
        }

        return $post;
    }

    /**
     * Restric product import on csv if category restriction enable
     *
     * @param $data
     * @since 3.1.0
     * @throws \Exception
     */
    public function restrict_category_on_csv_import( $data ) {
        if ( empty( $data['category_ids'] ) ) {
            return;
        }

        $categories         = $data['category_ids'];
        $allowed_categories = $this->get_vendor_allowed_categories();

        if ( ! empty( $allowed_categories ) ) {
            foreach ( $categories as $category ) {
                if ( ! in_array( $category, $allowed_categories ) ) {
                    throw new \Exception( __( 'Current subscription does not allow this', 'dokan' ) . get_term_field( 'name', $category ) );
                }
            }
        }
    }

    /**
     * Get subscription allowed categories if exist
     *
     * @since 3.1.0
     *
     * @return array
     */
    protected function get_vendor_allowed_categories() {
        $vendor_subscription = dokan()->vendor->get( dokan_get_current_user_id() )->subscription;
        if ( ! $vendor_subscription ) {
            return [];
        }
        $allowed_categories  = $vendor_subscription->get_allowed_product_categories();

        return $allowed_categories;
    }

    /**
     * This method will disable email verification if vendor subscription module is on
     * and if subscription is enabled on registration form
     *
     * @since 3.2.0
     * @param bool $ret
     * @return bool
     */
    public function disable_email_verification( $ret ) {
        // if $ret is true, do not bother checking if settings if enabled or not
        if ( $ret ) {
            return $ret;
        }

        $enable_option = get_option( 'dokan_product_subscription', array( 'enable_subscription_pack_in_reg' => 'off' ) );

        // check if subscription is enabled on registration form, we don't need to check if product subscription is enabled for vendor or not,
        // because we are already checking this on class constructor
        if ( (string) $enable_option['enable_subscription_pack_in_reg'] !== 'on' ) {
            return $ret;
        }

        // send verify email if newly registered user role is a customer
        if (
            (
                isset( $_POST['woocommerce-register-nonce'] ) &&
                wp_verify_nonce( sanitize_key( wp_unslash( $_POST['woocommerce-register-nonce'] ) ), 'woocommerce-register' ) &&
                isset( $_POST['role'] ) &&
                'customer' === $_POST['role']
            ) ||
            (
                isset( $_GET['dokan_email_verification'] ) && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) && ! isset( $_GET['page'] )
            )
        ) {
            return false;
        }

        // if product subscription is enabled on registration form, return true,
        // because we don't need to enable email verification if subscription module is active.
        return true;
    }

    /**
     * Redirect to currently created product edit screen
     *
     * @since 3.3.1
     *
     * @param string $redirect_to Redirect url.
     * @param int $product_id Created product ID.
     *
     * @return string
     */
    public function redirect_to_product_edit_screen( $redirect_to, $product_id ) {
        if ( Helper::get_vendor_remaining_products( dokan_get_current_user_id() ) ) {
            return $redirect_to;
        }

        if ( current_user_can( 'dokan_edit_product' ) ) {
            $redirect_to = dokan_edit_product_url( $product_id );
        } else {
            $redirect_to = dokan_get_navigation_url( 'products' );
        }
        return $redirect_to;
    }

    /**
     * @since 3.2.0
     *
     * Checking the ability to duplicate product based on subscription
     *
     * @param $can_duplicate
     *
     * @return bool|mixed|null
     */
    public function dokan_can_duplicate_product_on_subscription( $can_duplicate ) {

        if( ! $can_duplicate ) {
            return $can_duplicate;
        }

        // If the user is vendor staff, we are getting the specific vendor for that staff
        $user_id = (int) dokan_get_current_user_id();

        /** We are getting the subscription of the vendor
         * and checking if the vendor has remaining product based on active subscription
         **/
        if ( ! Helper::get_vendor_remaining_products( $user_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Add non_recurring_subscription_packs to dokan admin script
     *
     * @since 3.3.1
     *
     * @param array $localize_script
     *
     * @return array
     */
    public function add_subscription_packs_to_localize_script( $localize_script ) {
        $localize_script['non_recurring_subscription_packs'] = $this->get_nonrecurring_subscription_packs_with_emply_package();

        return $localize_script;
    }

    /**
     * Remove min max rules for subscription pack.
     *
     * @since 3.10.3
     *
     * @param bool $apply_min_max
     * @param int  $product_id
     *
     * @return bool
     */
    public function remove_min_max_for_subscription_packs( $apply_min_max, $product_id ) {
        $product = wc_get_product( $product_id );

        // Remove from min-max rules is subscription product.
        if ( 'product_pack' === $product->get_type() ) {
            return false;
        }

        return $apply_min_max;
    }

    /**
     * Add Current subscription info to vendor info.
     *
     * @since 3.3.1
     *
     * @param array $shop_data
     * @param Vendor $vendor
     *
     * @return array
     */
    public function add_currently_subscribed_pack_info_to_shop_data( $shop_data, $vendor ) {
        $users_assigned_pack = get_user_meta( $vendor->id, 'product_package_id', true );

        if ( ! $users_assigned_pack ) {
            $shop_data['current_subscription']       = array(
                'name'  => 0,
                'label' => __( '-- Select a package --', 'dokan' ),
            );
            $shop_data['assigned_subscription']      = 0;
            $shop_data['assigned_subscription_info'] = array(
                'subscription_id'    => 0,
                'has_subscription'   => false,
                'expiry_date'        => '',
                'published_products' => 0,
                'remaining_products' => 0,
                'recurring'          => false,
                'start_date'         => '',
            );
        } else {
            $subscription_pack                       = new SubscriptionPack( $users_assigned_pack, $vendor->id );
            $shop_data['current_subscription']       = array(
                'name'  => $users_assigned_pack,
                'label' => get_the_title( $users_assigned_pack ),
            );
            $shop_data['assigned_subscription']      = $users_assigned_pack;
            $shop_data['assigned_subscription_info'] = $subscription_pack->get_info();

            $shop_data['assigned_subscription_info']['recurring']  = $subscription_pack->is_recurring();
            $shop_data['assigned_subscription_info']['start_date'] = dokan_format_date( $subscription_pack->get_pack_start_date() );
        }

        return $shop_data;
    }

    /**
     *  Get non recurring subscription packs with empty pack.
     *
     * @since 3.3.1
     *
     * @return array
     */
    private function get_nonrecurring_subscription_packs_with_emply_package() {
        $subscriptions_packs = ( new SubscriptionPack() )->get_nonrecurring_packages();
        $response_array = array(
            array(
                'name' => 0,
                'label' => __( '-- Select a package --', 'dokan' ),
            ),
        );
        foreach ( $subscriptions_packs as $subscriptions_pack ) {
            array_push(
                $response_array,
                array(
                    'name' => $subscriptions_pack->ID,
                    'label' => $subscriptions_pack->post_title,
                )
            );
        }

        return $response_array;
    }

    /**
     * Store Vendor Subscribed subscription package information.
     *
     * @since 3.3.1
     *
     * @param int $vendor_id
     * @param array $data
     *
     * @return void
     */
    public function update_vendor_subscription_data( $vendor_id, $data ) {
        if ( ! isset( $data['subscription_nonce'] ) || ! wp_verify_nonce( $data['subscription_nonce'], 'dokan_admin' ) ) {
            return;
        }
        $vendor_id                = absint( $vendor_id );
        $subscription_id          = absint( $data['assigned_subscription'] );
        $previous_subscription_id = absint( get_user_meta( $vendor_id, 'product_package_id', true ) );

        if ( ! empty( $subscription_id ) && $subscription_id !== $previous_subscription_id ) {
            // Manually creating a order with 0.00 price to set the subscription.
            try {
                $order = new \WC_Order();
                $order->add_product( wc_get_product( $subscription_id ) );
                $order->set_created_via( 'dokan' );
                $order->set_customer_id( absint( $vendor_id ) );
                $order->set_total( 0.00 );
                $order->set_status( 'completed' );
                $order->save();
                $order->add_order_note( __( 'Manually assigned Vendor Subscription by Admin', 'dokan' ), 0, get_current_user_id() );
            } catch ( \Exception $exception ) {
                Helper::log( 'Subscription manually assign error from admin of User #' . $vendor_id . ' Message: ' . $exception->getMessage() );
                return;
            }

            $pack_validity         = get_post_meta( $subscription_id, '_pack_validity', true );
            $admin_commission      = get_post_meta( $subscription_id, '_subscription_product_admin_commission', true );
            $admin_additional_fee  = get_post_meta( $subscription_id, '_subscription_product_admin_additional_fee', true );
            $admin_commission_type = get_post_meta( $subscription_id, '_subscription_product_admin_commission_type', true );
            $category_admin_commission = get_post_meta( $subscription_id, '_subscription_product_admin_category_based_commission', true );

            update_user_meta( $vendor_id, 'product_package_id', $subscription_id );
            update_user_meta( $vendor_id, 'product_order_id', $order->get_id() );
            update_user_meta( $vendor_id, 'product_no_with_pack', get_post_meta( $subscription_id, '_no_of_product', true ) ); //number of products
            update_user_meta( $vendor_id, 'product_pack_startdate', dokan_current_datetime()->format( 'Y-m-d H:i:s' ) );

            if ( absint( $pack_validity ) > 0 ) {
                update_user_meta( $vendor_id, 'product_pack_enddate', dokan_current_datetime()->modify( "+$pack_validity days" )->format( 'Y-m-d H:i:s' ) );
            } else {
                update_user_meta( $vendor_id, 'product_pack_enddate', 'unlimited' );
            }

            update_user_meta( $vendor_id, 'can_post_product', 1 );
            update_user_meta( $vendor_id, '_customer_recurring_subscription', '' );

            $vendor = dokan()->vendor->get( $vendor_id );
            $vendor->save_commission_settings(
                [
                    'percentage'           => $admin_commission,
                    'type'                 => $admin_commission_type,
                    'flat'                 => $admin_additional_fee,
                    'category_commissions' => $category_admin_commission,
                ]
            );
        }
    }

    /**
     * Filter multi step vendor category according to subscription
     *
     * @since 3.7.4
     *
     * @param array $categories
     *
     * @return array
     **/
    public function filter_multistep_category( $categories ) {
        $user_id = get_current_user_id();

        if ( ! dokan_is_user_seller( $user_id ) ) {
            return $categories;
        }

        $is_seller_enabled = dokan_is_seller_enabled( $user_id );

        if ( ! $is_seller_enabled ) {
            return $categories;
        }

        $vendor = dokan()->vendor->get( $user_id )->subscription;

        if ( ! $vendor ) {
            return $categories;
        }

        if ( ! self::can_post_product() || ! $vendor->has_subscription() ) {
            return $categories;
        }

        $override_cat = get_user_meta( $user_id, 'vendor_allowed_categories', true );
        $selected_cat = ! empty( $override_cat ) ? $override_cat : $vendor->get_allowed_product_categories();

        if ( empty( $selected_cat ) ) {
            return $categories;
        }

        $selected_cat     = array_map( 'absint', apply_filters( 'dokan_pro_subscription_allowed_categories', $selected_cat ) );
        $to_return        = [];

        // We are allowing all the ancestors and grand children of the selected category.
        $category_object = new \WeDevs\Dokan\ProductCategory\Categories();
        $category_object->set_categories( $categories );

        foreach ( $selected_cat as $cat ) {
            $parent_categories = ! empty( $categories[ $cat ]['parents'] ) ? $categories[ $cat ]['parents'] : [];
            $child_categories  = ! empty( $category_object->get_children( $cat ) ) ? $category_object->get_children( $cat ) : [];
            $selected_cat      = array_merge(
                $selected_cat, $parent_categories, $child_categories
            );
        }

        foreach ( $selected_cat as $category_id ) {
            if ( empty( $categories[ $category_id ] ) ) {
                continue;
            }
            $to_return[ $category_id ] = $categories[ $category_id ];
        }

        return $to_return;
    }

    /**
     * Skips cart validation for Mangopay while purchasing subscription pack.
     * Currently, it applies for non-recurring subscriptions only.
     *
     * @todo Add support for recurring subscriptions.
     *
     * @since 3.7.18
     *
     * @param bool $needs_validation Indicates if validation is needed.
     *
     * @return bool
     */
    public function skip_cart_validation_for_mangopay( $needs_validation ) {
        if ( ! $needs_validation ) {
            return $needs_validation;
        }

        if ( ! Helper::is_subscription_module_enabled() ) {
            return $needs_validation;
        }

        if ( empty( WC()->cart ) ) {
            return $needs_validation;
        }

        /**
         * Traverse through all the items in the cart and check if subscription exists.
         * Currently, only the non-recurring subscription is supported.
         */
        foreach ( WC()->cart->cart_contents as $item ) {
            $product_id = $item['data']->get_id();

            if (
                Helper::is_subscription_product( $product_id ) &&
                ! Helper::is_recurring_pack( $product_id )
            ) {
                $needs_validation = false;
            } else {
                $needs_validation = true;
            }
        }

        return $needs_validation;
    }

    /**
     * Skips payment disbursement process for Mangopay while purchasing subscription pack.
     *
     * @since 3.7.18
     *
     * @param bool $disburse Indicates if disbursement is needed.
     * @param \WC_Order $order The order being processed.
     *
     * @return bool
     */
    public function skip_payment_disbursement_for_mangopay( $disburse, $order ) {
        if ( ! $disburse ) {
            return $disburse;
        }

        if ( ! Helper::is_vendor_subscription_order( $order ) ) {
            return $disburse;
        }

        return false;
    }

    /**
     * Modifies payin data for Mangopay while purchasing subscription pack.
     *
     * @since 3.7.18
     *
     * @param array $payin_data
     *
     * @return bool
     */
    public function modify_mangopay_payin_data( $payin_data ) {
        if ( empty( $payin_data['order_id'] ) ) {
            return $payin_data;
        }

        $order = wc_get_order( $payin_data['order_id'] );

        if ( ! $order ) {
            return $payin_data;
        }

        if ( ! Helper::is_vendor_subscription_order( $order ) ) {
            return $payin_data;
        }

        /*
        * We cannot pass the full amount as fees.
        * So the amount for the marketplace will be 1 cent less than the payin amount.
        */
        $payin_data['fees'] = $payin_data['amount'] - 1;

        return $payin_data;
    }

    /**
     * Remove catalog mode restriction.
     *
     * @since 3.8.3
     *
     * @param string      $enable Catalogue mode restriction enabled. Default `yes`
     * @param \WC_Product $product Current Product.
     * @param bool        $purchasable Whether the product is available to purchase.
     *
     * @return string
     */
    public function remove_catalogue_mode_restriction_on_dokan_subscription_product( string $enable, $product, bool $purchasable ): string {
        if ( 'product_pack' !== $product->get_type() ) {
            return $enable;
        }
        return 'no';
    }

    /**
     * Remove unsupported payment gateways.
     *
     * @since 3.9.1
     *
     * @param array $gateways All payment gateways.
     *
     * @return array
     */
    public function remove_unsupported_payment_gateways_on_dokan_subscription_product( array $gateways ): array {
        if ( ! WC()->cart || WC()->cart->is_empty() || is_admin() ) {
            return $gateways;
        }

        $is_recurring_subscription_product_is_in_cart = false;
        foreach ( WC()->cart->get_cart() as $item ) {
            if ( is_a( $item['data'], \WC_Product::class ) && 'product_pack' === $item['data']->get_type() && Helper::is_recurring_pack( $item['data']->get_id() ) ) {
                $is_recurring_subscription_product_is_in_cart = true;
                break;
            }
        }

        if ( ! $is_recurring_subscription_product_is_in_cart ) {
            return $gateways;
        }

        $vendor_recurring_subscription_supported_payment_gateways = apply_filters(
            'dokan_pro_recurring_vendor_subscription_supported_payment_gateways',
            [
                'dokan_stripe_express',
                'dokan-stripe-connect',
                'dokan_paypal_adaptive',
                'dokan_paypal_marketplace',
                'dokan-moip-connect',
                'paypal',
            ]
        );

        foreach ( $gateways as $key => $gateway ) {
            if ( ! in_array( $gateway->id, $vendor_recurring_subscription_supported_payment_gateways, true ) ) {
                unset( $gateways[ $key ] );
            }
        }

        return $gateways;
    }

    /**
     * Add Params to Store Collection.
     *
     * @since 3.11.3
     *
     * @param array $args
     *
     * @return array
     */
    public function add_params_to_store_collection( $args ) {
        $args['subscription_enabled'] = [
                'description'       => __( 'Is vendor subscription enabled', 'dokan' ),
                'type'              => 'string',
                'require'           => false,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
        ];

        $args['subscription_package_id'] = [
                'description'       => __( 'Vendor subscription package id', 'dokan' ),
                'type'              => 'integer',
                'require'           => false,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
        ];

       return $args;
    }

    /**
     * Get Stores Args.
     *
     * @since 3.11.3
     *
     * @param array $args
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function rest_get_stores_args( $args, $request ) {
        if ( 'yes' == $request['subscription_enabled'] ) {
            $args['meta_query'][] = [
                'key'     => 'product_package_id',
                'compare' => 'EXISTS',
            ];
        } elseif ( 'no' == $request['subscription_enabled'] ) {
            $args['meta_query'][] =[
                'key'     => 'product_package_id',
                'compare' => 'NOT EXISTS',
            ];
        }

        // If subscription package specified.
        if ( ! empty( $args['subscription_package_id'] ) ) {
            $args['meta_query'][] = [
                'key'     => 'product_package_id',
                'value'   => $args['subscription_package_id'],
                'compare' => '=',
            ];
        }

        return $args;
    }

    /**
     * Remove Request Warranty Button.
     *
     * @since 3.13.0
     *
     * @param array     $actions Order Actions
     * @param \WC_Order $order   WC Order
     *
     * @return array $actins
     */
    public function remove_request_warranty_button( array $actions, \WC_Order $order ): array {
        if ( Helper::is_vendor_subscription_order( $order ) ) {
            unset( $actions['request_warranty'] );
        }

        return $actions;
    }

    /**
     * Checks if manual order creation is enabled for a specific vendor.
     *
     * This method verifies if a vendor can create manual orders based on their subscription
     * package settings. It checks both global settings and vendor-specific subscription package
     * configurations.
     *
     * @since 4.0.0
     *
     * @param bool    $is_enabled  Default status of whether manual ordering is enabled
     * @param int     $vendor_id   The ID of the vendor to check permissions for
     * @param Manager $manager     The manager instance handling the manual orders
     *
     * @return bool True if manual orders are enabled for the vendor, false otherwise
     */
    public function is_manual_order_enabled( bool $is_enabled, int $vendor_id, Manager $manager ): bool {
        $vendor = dokan()->vendor->get( $vendor_id );

        // Skip if the capability is enabled or disabled for the vendor already.
        if ( metadata_exists( 'user', $vendor_id, $manager->settings->get_meta_key() ) ) {
            return $is_enabled;
        }

        $is_allowed_for_vendors  = 'on' === dokan_get_option( 'enable_pricing', 'dokan_product_subscription', 'off' );
        $subscription_package_id = $vendor->get_meta( 'product_package_id', true );

        if ( $is_allowed_for_vendors && ! empty( $subscription_package_id ) ) {
            $meta_key     = $manager->settings->get_meta_key();
            $subscription = dokan()->subscription->get( $subscription_package_id );
            $product      = $subscription->get_product();

            if ( $product && $product->meta_exists( $meta_key ) ) {
                $is_enabled = wc_string_to_bool( $product->get_meta( $meta_key, true ) );
            }
        }

        return $is_enabled;
    }
}
