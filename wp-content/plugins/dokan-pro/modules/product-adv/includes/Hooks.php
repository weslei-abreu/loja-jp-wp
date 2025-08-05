<?php
namespace WeDevs\DokanPro\Modules\ProductAdvertisement;

use WeDevs\DokanPro\Modules\ProductAdvertisement\Frontend\ProductSection;
use WeDevs\Dokan\ReverseWithdrawal\Manager as ReverseWithdrawalManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Hooks
 *
 * @since 3.5.0
 *
 * @package WeDevs\DokanPro\Modules\ProductAdvertisement
 */
class Hooks {
    /**
     * Hooks constructor.
     */
    public function __construct() {
        // make product featured
        add_action( 'dokan_after_product_advertisement_created', [ $this, 'make_product_featured' ], 10, 2 );
        // add reverse withdrawal entry
        if ( class_exists( ReverseWithdrawalManager::class ) ) {
            add_action( 'dokan_after_product_advertisement_created', [ $this, 'add_reverse_withdrawal_entry' ], 10, 3 );
        }

        // remove featured products after an advertisement is expired
        add_action( 'dokan_after_batch_expire_product_advertisement', [ $this, 'remove_featured_product' ], 10, 1 );

        // remove from feature product during product delete
        add_action( 'dokan_before_deleting_product_advertisement', [ $this, 'remove_deleted_featured_product' ], 10, 1 );
        add_action( 'dokan_before_batch_delete_product_advertisement', [ $this, 'remove_deleted_featured_product' ], 10, 1 );

        // expire advertisements daily cron hook
        add_action( 'dokan_product_advertisement_daily_at_midnight_cron', [ $this, 'expire_advertisements' ] );

        // remove advertisement base product after advertisement product has been deleted
        add_action( 'delete_post', [ $this, 'delete_advertisement_base_product' ], 20 );

        //display advertised products on top
        add_action( 'posts_results', [ $this, 'display_advertised_products_on_top' ], 10, 2 );

        //render advertise product section in single store page
        add_filter( 'dokan_product_sections_container', [ $this, 'render_product_section' ], 99, 1 );

        // fix admin report log list
        add_filter( 'dokan_log_exclude_commission', [ $this, 'report_log_exclude_commission' ], 10, 2 );

        // after deleting a product, delete advertisement
        add_action( 'delete_post', [ $this, 'delete_advertisement' ], 20, 1 );

        // remove min max rules for advertisement product.
        add_filter( 'dokan_validate_min_max_rules_for_product', [ $this, 'remove_min_max_for_advertisements' ], 10, 2 );

        // make product purchasable for vendors own product.
        add_filter( 'dokan_vendor_own_product_purchase_restriction', [ $this, 'make_product_purchasable_for_advertisement' ], 10, 2 );
    }

    /**
     * This method will mark advertised product as featured
     *
     * @since 3.5.0
     *
     * @param int $advertisement_id
     * @param array $data
     *
     * @return void
     */
    public function make_product_featured( $advertisement_id, $data ) {
        if ( ! Helper::is_featured_enabled() ) {
            return;
        }

        Helper::make_product_featured( $data['product_id'] );
    }

    /**
     * This method will mark advertised product as featured
     *
     * @since 3.6.1
     *
     * @param int $advertisement_id
     * @param array $data
     * @param array $args
     *
     * @return void
     */
    public function add_reverse_withdrawal_entry( $advertisement_id, $data, $args ) {
        // check if reverse_withdrawal_entry key exists in args
        if ( ! isset( $args['reverse_withdrawal_entry'] ) || false === $args['reverse_withdrawal_entry'] ) {
            return;
        }

        // check if price is greater than 0
        if ( $data['price'] <= 0 ) {
            return;
        }

        $vendor_id = dokan_get_vendor_by_product( $args['product_id'], true );

        // prepare item for database
        $args = [
            'trn_id'    => absint( $data['product_id'] ),
            'trn_type'  => 'product_advertisement',
            'vendor_id' => $vendor_id,
            'debit'     => floatval( $data['price'] ),
        ];

        // insert reverse withdrawal entry
        $manager  = new ReverseWithdrawalManager();
        $inserted = $manager->insert( $args ); // debug log is added in insert method
    }

    /**
     * Remove from featured list when advertisement is expired
     *
     * @since 3.5.0
     *
     * @param array $ids
     *
     * @return void
     */
    public function remove_featured_product( $ids ) {
        // return if make featured is disabled
        if ( ! Helper::is_featured_enabled() ) {
            return;
        }

        $manager     = new Manager();
        $product_ids = $manager->all(
            [
                'id'     => $ids,
                'return' => 'product_ids',
            ]
        );

        foreach ( $product_ids as $product_id ) {
            Helper::make_product_featured( $product_id, false );
        }
    }

    /**
     * Remove from featured list when advertisement is deleted
     *
     * @since 3.5.0
     *
     * @param array $ids
     *
     * @return void
     */
    public function remove_deleted_featured_product( $ids ) {
        // return if make featured is disabled
        if ( ! Helper::is_featured_enabled() ) {
            return;
        }

        // get product by ids
        $manager  = new Manager();
        $items    = $manager->all(
            [
                'id'     => $ids,
                'return' => 'all',
            ]
        );

        // if advertisement status is 1, we'll consider this product
        $eligible_products = [];
        foreach ( $items as $item ) {
            if ( intval( $item['status'] ) === 1 ) {
                $eligible_products[] = $item['product_id'];
            }
        }

        foreach ( $eligible_products as $product_id ) {
            Helper::make_product_featured( $product_id, false );
        }
    }

    /**
     * Expire advertisement daily
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function expire_advertisements() {
        $manager = new Manager();
        $manager->expire_advertisement_by_date();
    }

    /**
     * Remove advertisement base product after advertisement product has been deleted.
     *
     * @since 3.5.0
     *
     * @param int
     *
     * @return void
     */
    public function delete_advertisement_base_product( $post_id ) {
        if (
            file_exists( DOKAN_PRODUCT_ADVERTISEMENT_INC . 'Helper' )
            && $post_id === Helper::get_advertisement_base_product() ) {
            delete_option( Helper::get_advertisement_base_product_option_key() );
        }
    }

    /**
     * Display advertised products on top
     *
     * @since 3.5.0
     *
     * @param array $posts
     * @param object $query query arguments
     *
     * @return array
     */
    public function display_advertised_products_on_top( $posts, $query ) {
        global $wp_query;
        if ( ! is_admin() &&
            Helper::is_catalog_priority_enabled() &&
            $query->is_main_query() &&
            (
                is_search() ||
                ( is_a( $wp_query, 'WP_Query' ) && ! empty( $wp_query->get_queried_object() ) && is_shop() ) ||
                is_product_category() ||
                ( is_a( $wp_query, 'WP_Query' ) && dokan_is_store_page() )
            )
        ) {
            $non_advertised = [];
            $advertised    = [];
            // get all advertised products
            $manager = new Manager();
            $advertised_products = $manager->all(
                [
                    'status'   => 1,
                    'per_page' => -1,
                    'return'   => 'product_ids',
                ]
            );

            foreach ( $posts as $post ) {
                if ( in_array( (string) $post->ID, $advertised_products, true ) ) {
                    $advertised[] = $post;
                } else {
                    $non_advertised[] = $post;
                }
            }

            if ( dokan_is_store_page() ) {
                //todo: hack applied here, our store page ordering wasn't setting query var order,
                //we are putting advertised products at top
                $posts = array_merge( $advertised, $non_advertised );
            } else {
                /* if order is ASC put featured at top, otherwise put featured at bottom */
                $posts = ( 'ASC' === strtoupper( $query->get( 'order' ) ) )
                    ? array_merge( $advertised, $non_advertised )
                    : array_merge( $non_advertised, $advertised );
            }
        }

        return $posts;
    }

    /**
     * Render product section under single product page
     *
     * @param $container
     *
     * @return array
     */
    public function render_product_section( $container ) {
        return array_merge(
            [ new ProductSection() ],
            $container
        );
    }

    /**
     * Exclude commission from report log if order contains advertisement product
     *
     * @since 3.5.0
     *
     * @param bool $exclude
     * @param object $order
     *
     * @return bool
     */
    public function report_log_exclude_commission( $exclude, $order ) {
        if ( Helper::has_product_advertisement_in_order( $order->order_id ) ) {
            return true;
        }

        return $exclude;
    }

    /**
     * Delete advertisement data if a product has been deleted
     *
     * @since 3.5.0
     *
     * @param $post_id
     *
     * @return void
     */
    public function delete_advertisement( $post_id ) {
        // try to get wooCommerce product from post_id
        $product = wc_get_product( $post_id );

        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        $manager = new Manager();
        $manager->delete_advertisement_by_product_id( $product->get_id() );
    }

    /**
     * Remove min max rules for advertisement products.
     *
     * @since 3.10.3
     *
     * @param bool $apply_min_max
     * @param int  $product_id
     *
     * @return bool
     */
    public function remove_min_max_for_advertisements( $apply_min_max, $product_id ) {
        // Remove from min-max rules is advertisement product.
        if ( (int) $product_id === Helper::get_advertisement_base_product() ) {
            $apply_min_max = false;
        }

        return $apply_min_max;
    }

    /**
     * Make vendors own product purchasable if
     * advertisement product.
     *
     * @since 3.10.3
     *
     * @param bool        $is_purchasable
     * @param \WC_Product $product
     *
     * @return bool
     */
    public function make_product_purchasable_for_advertisement( $is_purchasable, $product ) {
        // Check is advertisement product.
        if ( $product->get_id() === Helper::get_advertisement_base_product() ) {
            $is_purchasable = true;
        }

        return $is_purchasable;
    }
}
