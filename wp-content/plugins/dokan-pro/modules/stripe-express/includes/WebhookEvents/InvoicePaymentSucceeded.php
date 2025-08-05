<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;
use WeDevs\DokanPro\Modules\Subscription\SubscriptionOrderMetaBuilder;

/**
 * Class to handle `invoice.payment_succeeded` webhook.
 *
 * @since   3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class InvoicePaymentSucceeded extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function handle() {
        if ( ! Subscription::has_vendor_subscription_module() ) {
            return;
        }

        $invoice         = $this->get_payload();
        $stripe_currency = strtolower( $invoice->currency ?? '' );

        if ( ! $invoice->paid ) {
            return;
        }

        if ( empty( $invoice->billing_reason ) || in_array( $invoice->billing_reason, Subscription::DISALLOWED_BILLING_REASONS, true ) ) {
            return;
        }

        $vendor_id  = Subscription::get_vendor_id_by_subscription( $invoice->subscription );
        $product_id = UserMeta::get_product_pack_id( $vendor_id );
        if ( ! $product_id || ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        $order_id = UserMeta::get_product_order_id( $vendor_id );
        $order    = wc_get_order( $order_id );
        if ( ! $order ) {
            $this->log( sprintf( 'Invalid Order ID: %s', $order_id ) );

            return;
        }

        $subscription = new SubscriptionPack( $product_id, $vendor_id );
        if ( $subscription->has_active_cancelled_subscrption() && $subscription->reactivate_subscription() ) {
            $order->add_order_note( __( 'Subscription Reactivated.', 'dokan' ) );

            return;
        }

        if ( ! empty( $invoice->charge ) ) {
            $meta_builder = new SubscriptionOrderMetaBuilder( $order, 'stripe_express' );
            $meta_builder->set_gateway_charge_id( $invoice->charge )
                ->build()
                ->save();
        }

        // get processing fee
        $processing_fee = 0;
        if ( $invoice->charge ) {
            $processing_fee = Payment::get_gateway_fee_from_charge( $invoice->charge );
        }

        switch ( $invoice->billing_reason ) {
            case 'subscription_create':
                try {
                    $subscription->activate_subscription( $order );

                    $stripe_subscription = Subscription::get( $invoice->subscription );

                    if ( ! empty( $processing_fee ) ) {
                        $meta_builder = new SubscriptionOrderMetaBuilder( $order, 'stripe_express' );
                        $meta_builder->set_processing_fee( $processing_fee )
                            ->set_gateway_fee( $processing_fee )
                            ->build()
                            ->save();
                    }

                    // If trial period exists, setup trial data and do not complete order yet.
                    if ( ! empty( $stripe_subscription->trial_end ) && $stripe_subscription->trial_end > time() ) {
                        SubscriptionHelper::activate_trial_subscription( $order, $subscription, $stripe_subscription->id );
                        break;
                    }

                    /* translators: 1) Stripe Invoice ID */
                    Order::add_note( $order, sprintf( __( 'Subscription activated. Invoice ID: %s', 'dokan' ), $invoice->id ) );
                    $order->payment_complete( $invoice->id );

                    SubscriptionHelper::delete_trial_meta_data( $vendor_id );
                } catch ( \Exception $e ) {
                    break;
                }
                break;

            case 'subscription':
                Order::add_note( $order, __( 'Subscription updated.', 'dokan' ) );
                break;

            case 'subscription_cycle':
                $order_total         = (float) $invoice->amount_paid / 100;
                $stripe_subscription = Subscription::get( $invoice->subscription );
                $period_start        = dokan_current_datetime()->setTimestamp( $stripe_subscription->current_period_start )->format( 'Y-m-d H:i:s' );
                $period_end          = dokan_current_datetime()->setTimestamp( $stripe_subscription->current_period_end )->format( 'Y-m-d H:i:s' );
                $interval_count      = $stripe_subscription->plan->interval_count ?? 0;
                $interval_period     = $stripe_subscription->plan->interval ?? '';

                // Check if transaction already recorded
                add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10, 2 );

                $query  = new \WC_Order_Query(
                    [
                        'search_transaction' => $invoice->id,
                        'customer_id'        => $order->get_customer_id(),
                        'limit'              => 1,
                        'type'               => 'shop_order',
                        'orderby'            => 'date',
                        'order'              => 'DESC',
                        'return'             => 'ids',
                    ]
                );
                $orders = $query->get_orders();

                remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10 );

                // Transaction is already recorded.
                if ( ! empty( $orders ) ) {
                    $order->payment_complete( $invoice->id );

                    return;
                }

                // Create new renewal order
                $renewal_order = SubscriptionHelper::create_renewal_order( $order, $order_total );
                if ( is_wp_error( $renewal_order ) ) {
                    $this->log( 'Create Renewal Order Failed. Error: ' . $renewal_order->get_error_message() );

                    return;
                }

                // Add renewal order number on order note
                $order->add_order_note(
                    sprintf(
                    /* translators: renewal order number with link */
                        __( 'Order %s created to record renewal.', 'dokan' ),
                        sprintf(
                            '<a href="%s">%s</a> ',
                            esc_url( SubscriptionHelper::get_edit_post_link( $renewal_order->get_id() ) ),
                            /* translators: renewal order number */
                            sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $renewal_order->get_order_number() )
                        )
                    )
                );

                // Add order number on renewal order note
                $renewal_order->add_order_note(
                    sprintf(
                    /* translators: 1) subscription order number with link */
                        __( 'Order created to record renewal subscription for %s.', 'dokan' ),
                        sprintf(
                            '<a href="%s">%s</a> ',
                            esc_url( SubscriptionHelper::get_edit_post_link( $subscription->get_id() ) ),
                            /* translators: order number */
                            sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $order->get_order_number() )
                        )
                    )
                );

                // Add less required metadatas
                $meta_builder = new SubscriptionOrderMetaBuilder( $renewal_order, 'stripe_express' );
                $meta_builder->set_capture_id( $invoice->id )
                    ->set_processing_fee( $processing_fee )
                    ->set_processing_currency( $stripe_currency )
                    ->set_gateway_fee( $processing_fee )
                    ->set_gateway_fee_paid_by( 'admin' )
                    ->set_shipping_fee_recipient( 'admin' )
                    ->set_tax_fee_recipient( 'admin' )
                    ->set_is_vendor_subscription_order( 'yes')
                    ->set_pack_validity_start_date( $period_start )
                    ->set_pack_validity_end_date( $period_end )
                    ->set_pack_renewal_interval_count( $interval_count )
                    ->set_pack_renewal_interval_period( $interval_period )
                    ->build()
                    ->save();

                /* translators: processing fee */
                Order::add_note( $renewal_order, sprintf( __( 'Processing fee: %s', 'dokan' ), $processing_fee ) );
                /* translators: transaction id */
                Order::add_note( $renewal_order, sprintf( __( 'Transaction ID: %s', 'dokan' ), $invoice->id ) );

                // Complete Payment for Subscription
                $renewal_order->payment_complete( $invoice->id );
                break;
        }
    }

    /**
     * Modifies query params according to need.
     *
     * @since 3.7.8
     *
     * @param array $query
     * @param array $query_vars
     *
     * @return array
     */
    public function handle_custom_query_var( $query, $query_vars ) {
        if ( ! empty( $query_vars['search_transaction'] ) ) {
            $query['meta_query'][] = [
                'key'     => OrderMeta::payment_capture_id_key(),
                'value'   => $query_vars['search_transaction'],
                'compare' => '=',
            ];
        }

        return $query;
    }
}
