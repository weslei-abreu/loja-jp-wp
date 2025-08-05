<?php

namespace WeDevs\DokanPro\Modules\RMA;

use WeDevs\Dokan\CatalogMode\Helper as CatalogModeHelper;
use WeDevs\DokanPro\Modules\RMA\Traits\RMACommon;

/**
* Frontend product and cart management
*/
class Frontend {

    use RMACommon;

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'show_product_warranty' ] );
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 10, 2 );
        add_filter( 'woocommerce_add_cart_item', [ $this, 'add_cart_item' ], 10, 1 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_cart_validation' ], 10, 2 );

        add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 10, 2 );
        add_filter( 'woocommerce_get_item_data', [ $this, 'get_item_data' ], 10, 2 );
        add_action( 'woocommerce_add_to_cart', [ $this, 'add_warranty_index' ], 10, 6 );

        add_filter( 'add_to_cart_text', [ $this, 'add_to_cart_text' ], 15 );
        add_filter( 'woocommerce_product_add_to_cart_text', [ $this, 'add_to_cart_text' ], 15, 2 );

        add_action( 'template_redirect', [ $this, 'handle_warranty_submit_request' ], 10 );
        add_action( 'template_redirect', [ $this, 'handle_warranty_conversation' ], 10 );

        // Dokan Catalog Mode Integration
        add_filter( 'dokan_rma_addons_add_to_cart_text', [ $this, 'change_rma_add_to_cart_text' ], 10, 2 );
    }

    /**
     * Show a product's warranty information
     *
     * @since 1.0.0
     */
    public function show_product_warranty() {
        global $post, $product;

        if ( $product->is_type( 'external' ) ) {
            return;
        }

        $product_id     = $product->get_id();
        $warranty       = $this->get_settings( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty['type'] === 'included_warranty' ) {
            if ( 'limited' === $warranty['length'] ) {
                $value      = $warranty['length_value'];
                $duration   = dokan_rma_get_duration_value( $warranty['length_duration'], $value );

                echo '<p class="warranty_info"><b>' . $warranty_label . ':</b> ' . $value . ' ' . $duration . '</p>';
            } else {
                echo '<p class="warranty_info"><b>' . $warranty_label . ':</b> ' . __( 'Lifetime', 'dokan' ) . '</p>';
            }
        } elseif ( $warranty['type'] === 'addon_warranty' ) {
            $addons = $warranty['addon_settings'];

            if ( is_array( $addons ) && ! empty( $addons ) ) {
                echo '<p class="warranty_info"><b>' . $warranty_label . '</b> <select name="dokan_warranty">';
                echo '<option value="-1">' . __( 'No warranty', 'dokan' ) . '</option>';

                foreach ( $addons as $x => $addon ) {
                    $amount     = $addon['price'];
                    $value      = $addon['length'];
                    $duration   = dokan_rma_get_duration_value( $addon['duration'], $value );

                    if ( intval( $value ) === 0 && intval( $amount ) === 0 ) {
                        // no warranty option
                        echo '<option value="-1">' . __( 'No warranty', 'dokan' ) . '</option>';
                    } else {
                        if ( intval( $amount ) === 0 ) {
                            $amount = __( 'Free', 'dokan' );
                        } else {
                            $amount = wc_price( $amount );
                        }
                        echo '<option value="' . $x . '">' . $value . ' ' . $duration . ' &mdash; ' . $amount . '</option>';
                    }
                }

                echo '</select></p>';
            }
        } else {
            echo '<p class="warranty_info"></p>';
        }
    }

    /**
     * Adds a dokan_warranty_index to a cart item.
     * Used in tracking the selected warranty options
     *
     * @since 1.0.0
     *
     * @param array $item_data The item data
     * @param int $product_id The product ID
     *
     * @return array $item_data
     */
    public function add_cart_item_data( array $item_data, int $product_id ): array {
        if ( isset( $_POST['dokan_warranty'] ) && $_POST['dokan_warranty'] !== '' ) { //phpcs:ignore
            $item_data['dokan_warranty_index'] = sanitize_text_field( wp_unslash( $_POST['dokan_warranty'] ) ); //phpcs:ignore
        }

        return $item_data;
    }

    /**
     * Add custom data to a cart item based on the selected warranty type
     *
     * @since 1.0.0
     *
     * @param array $item_data The item data
     *
     * @return array $item_data
     */
    public function add_cart_item( array $item_data ): array {
        $_product       = $item_data['data'];
        $warranty_index = false;

        if ( isset( $item_data['dokan_warranty_index'] ) ) {
            $warranty_index = $item_data['dokan_warranty_index'];
        }

        $product_id = ( version_compare( WC_VERSION, '3.0', '<' ) && isset( $_product->variation_id ) ) ? $_product->variation_id : $_product->get_id();

        if ( is_a( $_product, 'WC_Product_Variation' ) ) {
            $product_id = $_product->get_parent_id();
        }

        $warranty = $this->get_settings( $product_id );

        if ( $warranty ) {
            if ( $warranty['type'] === 'addon_warranty' && $warranty_index !== false ) {
                $addons                            = $warranty['addon_settings'];
                $item_data['dokan_warranty_index'] = $warranty_index;
                $add_cost                          = 0;

                if ( ! empty( $addons[ $warranty_index ] ) ) {
                    $addon = $addons[ $warranty_index ];
                    if ( $addon['price'] > 0 ) {
                        $add_cost += $addon['price'];

                        $price = $_product->get_price();
                        $_product->set_price( (float) $price + $add_cost );
                    }
                }
            }
        }

        return $item_data;
    }

    /**
     * Make sure an add-to-cart request is valid
     *
     * @param bool $valid The current validation status
     * @param int $product_id The product ID
     * @return bool $valid
     */
    public function add_cart_validation( bool $valid = false, int $product_id = 0 ): bool {
        $warranty       = $this->get_settings( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty['type'] === 'addon_warranty' && ! isset( $_REQUEST['dokan_warranty'] ) ) {
            // translators: %s: warranty label
            $error = sprintf( __( 'Please select your %s first.', 'dokan' ), $warranty_label );
            wc_add_notice( $error, 'error' );
            return false;
        }

        return $valid;
    }

    /**
     * Get warranty index and add it to the cart item
     *
     * @since 1.0.0
     *
     * @param array $cart_item The cart item
     * @param array $values
     *
     * @return array
     */
    public function get_cart_item_from_session( array $cart_item, array $values ): array {
        if ( isset( $values['dokan_warranty_index'] ) ) {
            $cart_item['dokan_warranty_index'] = $values['dokan_warranty_index'];
            $cart_item = $this->add_cart_item( $cart_item );
        }

        return $cart_item;
    }

    /**
     * Returns warranty data about a cart item
     *
     * @since 1.0.0
     *
     * @param array $other_data The other data
     * @param array $cart_item The cart item
     *
     * @return array
     */
    public function get_item_data( array $other_data, array $cart_item ): array {
        $_product   = $cart_item['data'];
        $product_id = $_product->get_id();

        if ( is_a( $_product, 'WC_Product_Variation' ) ) {
            $product_id = $_product->get_parent_id();
        }

        $warranty       = $this->get_settings( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty ) {
            if ( $warranty['type'] === 'addon_warranty' && isset( $cart_item['dokan_warranty_index'] ) ) {
                $addons         = $warranty['addon_settings'];
                $warranty_index = $cart_item['dokan_warranty_index'];

                if ( ! empty( $addons[ $warranty_index ] ) ) {
                    $addon         = $addons[ $warranty_index ];
                    $name          = $warranty_label;
                    $duration_unit = dokan_rma_get_duration_value( $addon['duration'], $addon['length'] );
                    $value         = $addon['length'] . ' ' . $duration_unit;

                    if ( $addon['price'] > 0 ) {
                        $value .= ' (' . wc_price( $addon['price'] ) . ')';
                    }

                    $other_data[] = array(
                        'name'      => $name,
                        'value'     => $value,
                        'display'   => '',
                    );
                }
            } elseif ( $warranty['type'] === 'included_warranty' ) {
                if ( $warranty['length'] === 'lifetime' ) {
                    $other_data[] = array(
                        'name'      => $warranty_label,
                        'value'     => __( 'Lifetime', 'dokan' ),
                        'display'   => '',
                    );
                } elseif ( $warranty['length'] === 'limited' ) {
                    $duration_unit = dokan_rma_get_duration_value( $warranty['length_duration'], $warranty['length_value'] );
                    $string = $warranty['length_value'] . ' ' . $duration_unit;
                    $other_data[] = array(
                        'name'      => $warranty_label,
                        'value'     => $string,
                        'display'   => '',
                    );
                }
            }
        }

        return $other_data;
    }

    /**
     * Add warranty index to the cart items from POST
     *
     * @since 1.0.0
     *
     * @param string $cart_key
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * @param object $variation
     * @param array $cart_item_data
     *
     * @return void
     */
    public function add_warranty_index( $cart_key, $product_id, $quantity, $variation_id = null, $variation = null, $cart_item_data = null ) {
        if ( isset( $_POST['dokan_warranty'] ) && $_POST['dokan_warranty'] !== '' ) {
            WC()->cart->cart_contents[ $cart_key ]['dokan_warranty_index'] = sanitize_text_field( wp_unslash( $_POST['dokan_warranty'] ) );
        }
    }

    /**
     * Add to cart text.
     *
     * @since 1.0.0
     * @version 2.9.0
     *
     * @param string $text Add to cart text.
     * @param null|\WC_Product $product Product object.
     *
     * @return string
     */
    public function add_to_cart_text( string $text, $product = null ): string {
        if ( ! is_object( $product ) ) {
            $product = wc_get_product( get_the_ID() );
        }

        if ( ! is_a( $product, 'WC_Product' ) ) {
            return $text;
        }

        if ( ! is_single( $product->get_id() ) && $this->check_required_warranty( $product->get_id() ) ) {
            $text = apply_filters( 'dokan_rma_addons_add_to_cart_text', __( 'Select options', 'dokan' ), $product );
        }

        return $text;
    }

    /**
     * Handle customer submit request
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_warranty_submit_request() {
        if ( ! isset( $_POST['dokan_save_warranty_request_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_save_warranty_request_nonce'] ) ), 'dokan_save_warranty_request' ) ) {
            return;
        }

        if ( ! isset( $_POST['request_item'] ) ) {
            wc_add_notice( __( 'Please select some item for sending request', 'dokan' ), 'error' );
            return;
        }

        $product_map = [];

        // Mapping all product with quantity
        foreach ( wc_clean( wp_unslash( $_POST['request_item'] ) ) as $key => $product_id ) {
            $product_map[] = [
                'product_id' => $product_id,
                'quantity'   => ! empty( $_POST['request_item_qty'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['request_item_qty'][ $key ] ) ) : 1,
                'item_id'    => ! empty( $_POST['request_item_id'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['request_item_id'][ $key ] ) ) : 0,
            ];
        }

        $data          = $_POST;
        $data['items'] = $product_map;

        $result = dokan_save_warranty_request( $data );

        if ( is_wp_error( $result ) ) {
            wc_add_notice( $result->get_error_message(), 'error' );
            return;
        }

        /**
         * Trigger an action after warranty request has been sent
         *
         * @since 1.0.0
         *
         * @param array $data
         */
        do_action( 'dokan_rma_send_warranty_request', $data );

        wc_add_notice( __( 'Request has been successfully submitted', 'dokan' ), 'success' );

        wp_safe_redirect( wc_get_account_endpoint_url( 'rma-requests' ) );
        exit();
    }

    /**
     * Handle Warranty Conversation.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_warranty_conversation() {
        if ( ! isset( $_POST['dokan_rma_send_message_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_rma_send_message_nonce'] ) ), 'dokan_rma_send_message' ) ) {
            return;
        }

        $redirect_url = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : home_url();

        if ( ! isset( $_POST['message'] ) || '' === trim( sanitize_text_field( wp_unslash( $_POST['message'] ) ) ) ) {
            wc_add_notice( __( 'Please enter some text for messaging', 'dokan' ), 'error' );
            wp_safe_redirect( $redirect_url );
            exit();
        }

        if ( empty( $_POST['request_id'] ) ) {
            wc_add_notice( __( 'No request found for conversation', 'dokan' ), 'error' );
            wp_safe_redirect( $redirect_url );
            exit();
        }

        $data = [
            'request_id' => intval( $_POST['request_id'] ),
            'from'       => isset( $_POST['from'] ) ? intval( $_POST['from'] ) : '',
            'to'         => isset( $_POST['to'] ) ? intval( $_POST['to'] ) : '',
            'message'    => sanitize_textarea_field( wp_unslash( $_POST['message'] ) ),
            'created_at' => dokan_current_datetime()->format( 'Y-m-d H:i:s' ),
        ];

        $conversation = new WarrantyConversation();
        $result       = $conversation->insert( $data );

        if ( is_wp_error( $result ) ) {
            wc_add_notice( $result->get_error_message(), 'error' );
            return;
        }

        wc_add_notice( __( 'Message send successfully', 'dokan' ), 'success' );

        wp_safe_redirect( $redirect_url );
        exit();
    }

    /**
     * This method will change add to cart text from Select Options to Read More
     *
     * @sience 3.7.4
     *
     * @param string $add_to_cart_text Add to cart text
     * @param \WC_Product $product Product object
     *
     * @return string
     */
    public function change_rma_add_to_cart_text( string $add_to_cart_text, \WC_Product $product ): string {
        if ( ! class_exists( CatalogModeHelper::class ) ) {
            return $add_to_cart_text;
        }
        // check if enabled by admin
        if ( ! CatalogModeHelper::is_enabled_by_admin() ) {
            return $add_to_cart_text;
        }

        // check if enabled by product
        if ( CatalogModeHelper::is_enabled_for_product( $product ) ) {
            return __( 'Read More', 'dokan' ); // per product settings to hide product price is enabled
        }

        // check if enabled by vendor global settings
        $vendor_id = dokan_get_vendor_by_product( $product, true );
        if ( CatalogModeHelper::hide_add_to_cart_button_option_is_enabled_by_vendor( $vendor_id ) ) {
            return __( 'Read More', 'dokan' ); // vendor global settings to hide product price is enabled
        }

        return $add_to_cart_text;
    }
}
