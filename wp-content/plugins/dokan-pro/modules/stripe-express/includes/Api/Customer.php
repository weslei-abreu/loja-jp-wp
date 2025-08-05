<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * Customer API handler class.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Customer extends Api {

	/**
	 * Retrieves a Customer object.
	 *
	 * @since 3.11.4
	 *
	 * @param string $id The ID of the customer to retrieve.
	 *
	 * @throws DokanException if the request fails
	 *
	 * @return \Stripe\Customer
	 */
	public static function retrieve( $id ) {
		try {
			return static::api()->customers->retrieve( $id );
		} catch ( Exception $e ) {
			Helper::log( sprintf( 'Could not retrieve customer: %s', $e->getMessage() ), 'Customer' );
			Helper::log( 'id: ' . print_r( $id, true ), 'Customer' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			throw new DokanException( 'dokan-stripe-customer-retrieve-error', $e->getMessage() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}
	}

	/**
	 * Creates stripe customer.
	 *
	 * @since 3.6.1
	 *
	 * @param array $data
	 *
	 * @return \Stripe\Customer The newly created customer object.
	 * @throws DokanException
	 */
	public static function create( $data ) {
		try {
			return static::api()->customers->create( $data );
		} catch ( Exception $e ) {
			Helper::log( sprintf( 'Could not create customer: %s', $e->getMessage() ), 'Customer' );
			Helper::log( 'Data: ' . print_r( $data, true ), 'Customer' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			throw new DokanException( 'dokan-stripe-customer-create-error', $e->getMessage() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}
	}

	/**
	 * Updates a Stripe customer.
	 *
	 * @since 3.6.1
	 *
	 * @param int|string $id
	 * @param array      $data
	 *
	 * @return \Stripe\Customer The updated customer object.
	 * @throws DokanException
	 */
	public static function update( $id, $data ) {
		try {
			return static::api()->customers->update( $id, $data );
		} catch ( Exception $e ) {
			Helper::log( sprintf( 'Could not update customer: %s. Error: %s', $id, $e->getMessage() ) );
			Helper::log( 'Data: ' . print_r( $data, true ), 'Customer' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			throw new DokanException( 'dokan-stripe-customer-update-error', $e->getMessage() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}
	}
}
