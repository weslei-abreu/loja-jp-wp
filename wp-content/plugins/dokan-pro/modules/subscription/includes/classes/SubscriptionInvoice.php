<?php

namespace DokanPro\Modules\Subscription;

use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Subscription Invoice Class.
 *
 * @since 3.13.0
 */
class SubscriptionInvoice {

    /**
     * Class Constructor.
     *
     * @return void
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Init Hooks.
     *
     * @since 3.13.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'wpo_wcpdf_after_item_meta', [ $this, 'print_subscription_product_validity_content' ], 10, 3 );
        add_action( 'wpo_wcpdf_after_order_details', [ $this, 'print_invoice_footnote_content' ], 10, 2 );

        add_filter( 'dokan_invoice_store_name', [ $this, 'get_marketplace_name' ], 10, 2 );
        add_filter( 'dokan_cf_vat_number_label', [ $this, 'get_vat_number_label' ] );
        add_filter( 'dokan_cf_bank_iban_label', [ $this, 'get_bank_iban_label' ] );
    }

    /**
     * Get Marketplace Name.
     *
     * @since 3.13.0
     *
     * @param string $store_name Store Name
     * @param object $document   Invoice Document
     *
     * @return string
     */
    public function get_marketplace_name( string $store_name, object $document ): string {
        if ( ! isset( $document->order ) ) {
            return $store_name;
        }

        if ( ! Helper::is_vendor_subscription_order( $document->order ) ) {
            return $store_name;
        }

        return sprintf( '<h2>%s</h2>', get_bloginfo() );
    }

    /**
     * Get Vat Number Label
     *
     * @since 3.13.0
     *
     * @return string
     */
    public function get_vat_number_label(): string {
        return esc_attr__( 'VAT / TAX ID', 'dokan' );
    }

    /**
     * Get Bank IBAN Label
     *
     * @since 3.13.0
     *
     * @return string
     */
    public function get_bank_iban_label(): string {
        return esc_attr__( 'Account / IBAN', 'dokan' );
    }

    /**
     * Print Subscription Product Validity Content.
     *
     * @since 3.13.0
     *
     * @param string   $document_type Document Type
     * @param array    $order_item    Order Item
     * @param WC_Order $order         Order Object
     *
     * @return void
     */
    public function print_subscription_product_validity_content( string $document_type, array $order_item, WC_Order $order ) {
        if ( 'invoice' !== $document_type ) {
            return;
        }

        if ( ! ( $order && $order->get_meta( '_pack_validity' ) ) ) {
            return;
        }

        $interval_count  = $order->get_meta( '_dokan_subscription_pack_renewal_interval_count', true );
        $interval_period = $order->get_meta( '_dokan_subscription_pack_renewal_interval_period', true );
        $pack_validity   = 'unlimited' === $order->get_meta( '_pack_validity' ) ? esc_html__( 'Unlimited', 'dokan' ) : dokan_format_datetime( $order->get_meta( '_pack_validity' ) );

        if ( $interval_count && $interval_period ) {
            printf( esc_html__( 'Subscription Period: %d %s <br>', 'dokan' ), $interval_count, $interval_period );
            printf( esc_html__( 'Next Renewal: %s', 'dokan' ), $pack_validity );
        } else {
            printf( esc_html__( 'Subscription Validity: %s', 'dokan' ), $pack_validity );
        }
    }

    /**
     * Print Invoice Footnote Content.
     *
     * @since 3.13.0
     *
     * @param string $document_type Document Type
     * @param WC_Order $order         Order Object
     *
     * @return void
     */
    public function print_invoice_footnote_content( string $document_type, WC_Order $order ) {
        if ( 'invoice' !== $document_type ) {
            return;
        }

        if ( ! $order->meta_exists( '_dokan_vendor_subscription_order' ) ) {
            return;
        }

        esc_html_e( 'Payment must be received within the payment period specified in agreement. Late payments may result in the suspension or cancellation of your subscription service without prior notice.<br>VAT is charged in accordance with the applicable tax laws. If you are VAT-registered within the EU, please provide your VAT number to claim reverse charge relief.', 'dokan' );
    }
}
