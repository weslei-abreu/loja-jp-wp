<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * API handler class for setup intent
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class SetupIntent extends Api {

	/**
	 * Creates a setup intent.
	 *
	 * @since 3.6.1
	 *
	 * @param array $args The data to create the setup intent with.
	 *
	 * @return \Stripe\SetupIntent
	 * @throws DokanException
	 */
	public static function create( $args ) {
		try {
			return self::api()->setupIntents->create( $args );
		} catch ( Exception $e ) {
			Helper::log( sprintf( 'Could not create setup intent. Error: %s', $e->getMessage() ), 'Setup Intent' );
			Helper::log( 'Data: ' . print_r( $args, true ), 'Setup Intent' );

			$error_code = 'dokan-stripe-express-setup-intent-error';
			if ( Helper::is_no_such_customer_error( $e->getMessage() ) ) {
				$error_code = 'error_setup-intent_no-such-customer';
			}

			/* translators: 1) error message */
			throw new DokanException( esc_html( $error_code ), sprintf( esc_html__( 'Could not create setup intent. Error: %s', 'dokan' ), esc_html( $e->getMessage() ) ) );
		}
	}

	/**
	 * Updates a setup intent.
	 *
	 * @since 3.6.1
	 *
	 * @param string $intent_id The ID of the setup intent to update.
	 * @param array $data The data to update the setup intent with.
	 *
	 * @return \Stripe\SetupIntent
	 * @throws DokanException
	 */
	public static function update( $intent_id, $data ) {
		try {
			return self::api()->setupIntents->update( $intent_id, $data );
		} catch ( Exception $e ) {
			Helper::log( sprintf( 'Could not update setup intent: %1$s. Error: %2$s', $intent_id, $e->getMessage() ), 'setup Intent' );
			Helper::log( 'Data: ' . print_r( $data, true ), 'setup Intent' );
			throw new DokanException( 'dokan-stripe-express-setup-intent-error', esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Retrieves a Setup intent.
	 *
	 * @since 3.6.1
	 *
	 * @param string $intent_id The ID of the setup intent to retrieve.
	 * @param array  $args     Optional arguments to pass to the API request.
	 *
	 * @return \Stripe\SetupIntent|false
	 */
	public static function get( $intent_id, $args = array() ) {
		try {
			return self::api()->setupIntents->retrieve( $intent_id, $args );
		} catch ( Exception $e ) {
			Helper::log( sprintf( 'Could not retrieve setup intent for id: %1$s. Error: %2$s', $intent_id, $e->getMessage() ), 'setup Intent' );
			return false;
		}
	}
}
