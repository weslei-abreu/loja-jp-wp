<?php

use DokanPro\Modules\Subscription\Helper;

/**
 * Vendor Subscription API Controller.
 *
 * @since 4.0.0
 *
 * @package dokan
 */
class Dokan_REST_Vendor_Subscription_Controller extends Dokan_REST_Subscription_Controller {

    /**
     * Endpoint Namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route Name.
     *
     * @var string
     */
    protected $base = 'vendor-subscription';

    /**
     * Register Routes Related with Vendor Subscription.
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base . '/vendor/(?P<id>[\d]+)', [
                'args' => [
                    'id' => [
                        'description' => __( 'Vendor id', 'dokan' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_active_subscription_for_vendor' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/update/(?P<id>[\d]+)/', [
                'args' => [
                    'id' => [
                        'description' => __( 'Vendor id', 'dokan' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_subscription' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => [
                        'action' => [
                            'description'       => __( 'Action to update.', 'dokan' ),
                            'type'              => 'string',
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Check Permission.
     *
     * @since 4.0.0
     *
     * @return bool|WP_Error
     */
    public function check_permission() {
        if ( ! current_user_can( 'dokandar' ) ) {
            return new WP_Error(
                'dokan_pro_permission_failure',
                __( 'Sorry! You are not permitted to do current action.', 'dokan' ),
                [ 'status' => 403 ]
            );
        }

        return true;
    }

    /**
     * Get currently activated subscription for a vendor.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function get_active_subscription_for_vendor( $request ) {
        $vendor_id = $this->get_vendor_id( $request );
        $vendor    = get_user_by( 'id', $vendor_id );

        $data = $this->prepare_item_for_response( $vendor, $request );

        return rest_ensure_response( $data );
    }

    /**
     * Update Subscription.
     *
     * @since 4.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function update_subscription( $request ) {
        $vendor_id          = $this->get_vendor_id( $request );
        $action             = $request->get_param( 'action' );
        $cancel_immediately = false;

        $order_id = get_user_meta( $vendor_id, 'product_order_id', true );
        $vendor   = dokan()->vendor->get( $vendor_id )->subscription;
        $user     = new \WP_User( $vendor_id );

        if ( ! $order_id || ! $vendor ) {
            return new WP_Error(
                'no_subscription',
                __( 'No subscription is found to be updated.', 'dokan' ),
                [ 'status' => 404 ]
            );
        }

        if ( 'activate' === $action ) {
            if ( $vendor->has_recurring_pack() && $vendor->has_active_cancelled_subscrption() ) {
                Helper::log( 'Subscription re-activation check: Recurring subscription re-activation for User #' . $vendor_id . ' on order #' . $order_id );
                do_action( 'dps_activate_recurring_subscription', $order_id, $vendor_id );
            }

            if ( ! $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription re-activation check: Non-recurring subscription re-activation for User #' . $vendor_id . ' on order #' . $order_id );
                do_action( 'dps_activate_non_recurring_subscription', $order_id, $vendor_id );
            }
        }

        if ( 'cancel' === $action ) {
            if ( $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription cancellation check: Recurring subscription cancellation for User #' . $vendor_id . ' on order #' . $order_id );
                do_action( 'dps_cancel_recurring_subscription', $order_id, $vendor_id, $cancel_immediately );
            } elseif ( ! $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription cancellation check: Non-recurring subscription cancellation for User #' . $vendor_id . ' on order #' . $order_id );
                do_action( 'dps_cancel_non_recurring_subscription', $order_id, $vendor_id, $cancel_immediately );
            }
        }

        $response = $this->prepare_item_for_response( $user, $request );
        $response = rest_ensure_response( $response );

        return $response;
    }

    /**
     * Get seller id from Query param for Admin and currently logged-in user as Vendor.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return int
     */
    public function get_vendor_id( WP_REST_Request $request ): int {
        if ( ! is_user_logged_in() ) {
            return 0;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return dokan_get_current_user_id();
        }

        return (int) $request->get_param( 'id' );
    }
}
