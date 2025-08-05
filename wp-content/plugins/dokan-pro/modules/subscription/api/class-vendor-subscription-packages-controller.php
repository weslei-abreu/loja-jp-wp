<?php

use DokanPro\Modules\Subscription\SubscriptionPack;

/**
 * Vendor Subscription Orders API Controller.
 *
 * @since 4.0.0
 *
 * @package dokan
 */
class Dokan_REST_Vendor_Subscription_Packages_Controller extends Dokan_REST_Vendor_Subscription_Controller {

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
    protected $base = 'vendor-subscription/packages';

    /**
     * Register Routes Related with Vendor Subscription Packages.
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );
    }

    /**
     * Get Vendor Subscription Packages.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function get_items( $request ) {
        global $wpdb;

        $params = $request->get_params();

        $args = [
            'posts_per_page' => $params['per_page'],
            'offset'         => ( $params['page'] - 1 ) * $params['per_page'],
            'post_status'    => [ 'publish' ],
        ];

        if ( ! empty( $params['search'] ) ) {
            $args['s']              = $wpdb->esc_like( $params['search'] );
            $args['search_columns'] = [ 'post_title' ];
        }

        $query          = ( new SubscriptionPack() )->all( $args );
        $total_packages = $query->found_posts;

        $data = [];
        foreach ( $query->get_posts() as $package ) {
            $product = wc_get_product( $package->ID );

            if ( ! $product ) {
                continue;
            }

            $item_data = $this->prepare_item_for_response( $product, $request );
            $data[]    = $this->prepare_response_for_collection( $item_data );
        }

        $response = rest_ensure_response( $data );

        return $this->format_collection_response( $response, $request, $total_packages );
    }

    /**
     * Prepare Item for REST Response.
     *
     * @since 4.0.0
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response $response
     */
    public function prepare_item_for_response( $item, $request ): WP_REST_Response {
        $fields = $this->get_fields_for_response( $request );
        $data   = [];

        if ( in_array( 'id', $fields, true ) ) {
            $data['id'] = absint( $item->get_id() );
        }

        if ( in_array( 'title', $fields, true ) ) {
            $data['title'] = $item->get_title();
        }

        if ( in_array( 'price', $fields, true ) ) {
            $data['price'] = $item->get_price();
        }

        if ( in_array( 'regular_price', $fields, true ) ) {
            $data['regular_price'] = $item->get_regular_price();
        }

        if ( in_array( 'sale_price', $fields, true ) ) {
            $data['sale_price'] = $item->get_sale_price();
        }

        if ( in_array( 'no_of_product', $fields, true ) ) {
            $data['no_of_product'] = $item->get_meta( '_no_of_product' );
        }

        if ( in_array( 'pack_validity', $fields, true ) ) {
            $data['pack_validity'] = $item->get_meta( '_pack_validity' );
        }

        if ( in_array( 'gallery_restriction', $fields, true ) ) {
            $data['gallery_restriction'] = $item->get_meta( '_enable_gallery_restriction' );
        }

        if ( in_array( 'gallery_restriction_count', $fields, true ) ) {
            $data['gallery_restriction_count'] = $item->get_meta( '_gallery_image_restriction_count' );
        }

        if ( in_array( 'recurring_payment', $fields, true ) ) {
            $data['recurring_payment'] = $item->get_meta( '_enable_recurring_payment' );
        }

        if ( in_array( 'recurring_period_interval', $fields, true ) ) {
            $data['recurring_period_interval'] = $item->get_meta( '_dokan_subscription_period_interval' );
        }

        if ( in_array( 'recurring_period_type', $fields, true ) ) {
            $data['recurring_period_type'] = $item->get_meta( '_dokan_subscription_period' );
        }

        if ( in_array( 'recurring_period_length', $fields, true ) ) {
            $data['recurring_period_length'] = $item->get_meta( '_dokan_subscription_length' );
        }

        if ( in_array( 'allowed_trial', $fields, true ) ) {
            $data['allowed_trial'] = $item->get_meta( 'dokan_subscription_enable_trial' );
        }

        if ( in_array( 'trial_period_range', $fields, true ) ) {
            $data['trial_period_range'] = $item->get_meta( 'dokan_subscription_trail_range' );
        }

        if ( in_array( 'trial_period_types', $fields, true ) ) {
            $data['trial_period_types'] = $item->get_meta( 'dokan_subscription_trial_period_types' );
        }

        if ( in_array( 'advertisement_slot_count', $fields, true ) ) {
            $data['advertisement_slot_count'] = $item->get_meta( '_dokan_advertisement_slot_count' );
        }

        if ( in_array( 'advertisement_validity', $fields, true ) ) {
            $data['advertisement_validity'] = $item->get_meta( '_dokan_advertisement_validity' );
        }

        $context  = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data     = $this->filter_response_by_context( $data, $context );
        $data     = $this->add_additional_fields_to_object( $data, $request );

        // Wrap the data in a response object.
        $response = rest_ensure_response( $data );

        /**
         * Filter vendor subscription package object returned from the REST API.
         *
         * @param WP_REST_Response $response The response object.
         * @param WC_Product       $item     Product object used to create response.
         * @param WP_REST_Request  $request  Request object.
         */
        return apply_filters( 'dokan_rest_prepare_vendor_subscription_package', $response, $item, $request );
    }

    /**
     * Get Item Schema.
     *
     * @since 4.0.0
     *
     * @return array
     */
    public function get_item_schema(): array {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'vendor_subscription_packages',
            'type'       => 'object',
            'properties' => [
                'id'                       => [
                    'description' => __( 'The unique identifier of the item.', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                    'required'    => true,
                ],
                'title'                    => [
                    'description' => __( 'Title of the item.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'price'                    => [
                    'description' => __( 'Current price of the item.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'regular_price'            => [
                    'description' => __( 'Regular price of the item.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'sale_price'               => [
                    'description' => __( 'Sale price of the item.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'no_of_product'            => [
                    'description' => __( 'Number of products included.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'pack_validity'            => [
                    'description' => __( 'Validity period of the pack.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'gallery_restriction'      => [
                    'description' => __( 'Whether gallery restriction is enabled.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'gallery_restriction_count' => [
                    'description' => __( 'Maximum number of gallery images allowed.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'recurring_payment'        => [
                    'description' => __( 'Whether recurring payment is enabled.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'recurring_period_interval' => [
                    'description' => __( 'Interval between recurring payments.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'recurring_period_type'    => [
                    'description' => __( 'Type of recurring period.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'recurring_period_length'  => [
                    'description' => __( 'Length of the recurring period.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'allowed_trial'            => [
                    'description' => __( 'Whether trial is allowed.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'trial_period_range'       => [
                    'description' => __( 'Range of the trial period.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'trial_period_types'       => [
                    'description' => __( 'Type of trial period.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'advertisement_slot_count' => [
                    'description' => __( 'Number of advertisement slots.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'advertisement_validity'   => [
                    'description' => __( 'Validity period of advertisements.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
