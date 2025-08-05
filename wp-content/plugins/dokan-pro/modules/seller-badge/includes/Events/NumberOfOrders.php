<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class number of orders count badge
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class NumberOfOrders extends BadgeEvents {

    /**
     * Class constructor
     *
     * @since 3.7.14
     *
     * @param string $event_type
     */
    public function __construct( $event_type ) {
        parent::__construct( $event_type );
        // return in case of error
        if ( is_wp_error( $this->badge_event ) ) {
            return;
        }
        add_action( 'woocommerce_order_status_changed', [ $this, 'process_hook' ], 999, 3 );
    }

    /**
     * Process hooks related to this badge
     *
     * @since 3.7.14
     *
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     *
     * @return void
     */
    public function process_hook( $order_id, $old_status, $new_status ) {
        if ( ! in_array( $new_status, [ 'completed', 'processing' ] ) ) {
            return;
        }

        if ( false === $this->set_badge_and_badge_level_data() ) {
            return;
        }

        // if badge status is draft, no need to update vendor badges
        if ( 'published' !== $this->badge_data['badge_status'] ) {
            return;
        }

        $vendor_id = dokan_get_seller_id_by_order( $order_id );
        if ( $vendor_id ) {
            $this->run( $vendor_id );
        }
    }

    /**
     * Get current compare data
     *
     * @since 3.7.14
     *
     * @param int $vendor_id
     *
     * @return false|float
     */
    protected function get_current_data( $vendor_id ) {
        /**
         * @var Vendor $vendor
         */
        $vendor = dokan()->vendor->get( $vendor_id );
        if ( ! $vendor->get_id() ) {
            return false;
        }

        $orders = dokan_count_orders( $vendor_id );
        if ( ! is_object( $orders ) || ( empty( $orders->{'wc-completed'} ) &&  empty( $orders->{'wc-processing'} ) ) ) {
            return false;
        }

        return round( $orders->{'wc-completed'} + $orders->{'wc-processing'}, 2 );
    }
}
