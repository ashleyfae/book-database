<?php
/**
 * License Key
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class License_Key
 * @package Book_Database
 */
class License_Key {

	/**
	 * License key string
	 *
	 * @var string
	 */
	protected $license = '';

	/**
	 * License key status
	 *
	 * May be:
	 *      valid
	 *      invalid
	 *      expired
	 *      revoked
	 *      item_name_mismatch
	 *      no_activations_left
	 *
	 *
	 * @var string|null
	 */
	protected $status = null;

	/**
	 * License expiration date
	 *
	 * @var string|null
	 */
	protected $expires = null;

	/**
	 * Number of sites the license key has been activated on
	 *
	 * @var int
	 */
	protected $site_count = 0;

	/**
	 * Site activation limite
	 *
	 * @var int
	 */
	protected $site_limit = 0;

	/**
	 * Number of remaining activations
	 *
	 * @var int
	 */
	protected $activations_left = 0;

	/**
	 * @var object|null
	 */
	protected $api_data = null;

	/**
	 * Cached data saved in the `bdb_license_key_data` option.
	 *
	 * @var array|false
	 */
	protected $cached_data = false;

	/**
	 * Whether or not the cache is still valid
	 *
	 * @var bool
	 */
	protected $cache_is_valid = false;

	/**
	 * License_Key constructor.
	 *
	 * @param string $license
	 */
	public function __construct( $license = '' ) {

		$this->license = ! empty( $license ) ? $license : get_option( 'bdb_license_key' );
		$this->license = trim( $this->license );

		$this->get_saved_data();

	}

	/**
	 * Get the saved license key data
	 *
	 * If the cache is no longer valid then we do a new API request.
	 *
	 * @return void
	 */
	public function get_saved_data() {

		if ( empty( $this->license ) ) {
			return;
		}

		$this->cached_data = get_option( 'bdb_license_key_data' );

		if ( $this->is_valid_cache() ) {
			$this->set_api_response( json_decode( $this->cached_data['data'] ) );

			return;
		}

		$response = $this->check_license();

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( $this->cache_is_valid ) {
				$this->update_cache( $this->cached_data['data'] );
			}

			return;
		}

		$this->update_cache( wp_remote_retrieve_body( $response ) );

	}

	/**
	 * Whether or not the license key is currently active
	 *
	 * @return bool
	 */
	public function is_active() {
		return 'valid' === $this->get_status();
	}

	/**
	 * Check a license key
	 *
	 * @return array|\WP_Error
	 */
	public function check_license() {

		$response = wp_remote_post( NOSE_GRAZE_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => array(
				'edd_action' => 'check_license',
				'license'    => $this->get_key(),
				'item_name'  => urlencode( 'Book Database' ),
				'url'        => home_url()
			)
		) );

		if ( ! is_wp_error( $response ) && 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->set_api_response( json_decode( wp_remote_retrieve_body( $response ) ) );
		}

		return $response;

	}

	/**
	 * Activate a license key
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function activate() {

		$response = wp_remote_post( NOSE_GRAZE_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => array(
				'edd_action' => 'activate_license',
				'license'    => $this->get_key(),
				'item_name'  => urlencode( 'Book Database' ),
				'url'        => home_url()
			)
		) );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_code(), $response->get_error_message() );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			throw new Exception( 'invalid-response', sprintf( __( 'Invalid response code: %d', 'book-database' ), wp_remote_retrieve_response_code( $response ) ) );
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		$this->set_api_response( $license_data );

		if ( false === $license_data->success ) {
			throw new Exception( 'activation-failure', $this->get_error_message() );
		}

		update_option( 'bdb_license_key', $this->get_key(), true );

		$this->update_cache( wp_remote_retrieve_body( $response ) );

		return true;

	}

	/**
	 * Deactivate a license key
	 *
	 * @return true
	 * @throws Exception
	 */
	public function deactivate() {

		$response = wp_remote_post( NOSE_GRAZE_STORE_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->get_key(),
				'item_name'  => urlencode( 'Book Database' ),
				'url'        => home_url()
			)
		) );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_code(), $response->get_error_message() );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			throw new Exception( 'invalid-response', sprintf( __( 'Invalid response code: %d', 'book-database' ), wp_remote_retrieve_response_code( $response ) ) );
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		$this->set_api_response( $license_data );

		if ( 'deactivated' === $license_data->license ) {
			delete_option( 'bdb_license_key' );
			delete_option( 'bdb_license_key_data' );

			return true;
		}

		throw new Exception( 'deactivation-failure', __( 'Failed to deactivate license.', 'book-database' ) );

	}

	/**
	 * Whether or not the cached data is valid
	 *
	 * @return bool
	 */
	protected function is_valid_cache() {

		if ( empty( $this->cached_data['data'] ) ) {
			return false;
		}

		if ( empty( $this->cached_data['expires'] ) ) {
			return false;
		}

		$this->cache_is_valid = true;

		if ( strtotime( $this->cached_data['expires'] ) < time() ) {
			return false;
		}

		return true;

	}

	/**
	 * Update the cache with a new value and set the new expiration date to +1 week
	 *
	 * @param string $new_value JSON string
	 *
	 * @return bool
	 */
	protected function update_cache( $new_value ) {

		$option = array(
			'data'    => $new_value,
			'expires' => date( 'Y-m-d H:i:s', strtotime( '+1 week' ) )
		);

		$this->set_api_response( json_decode( $new_value ) );

		return update_option( 'bdb_license_key_data', $option, false );

	}

	/**
	 * Set up API response data
	 *
	 * @param object $api_response
	 */
	public function set_api_response( $api_response ) {

		$this->api_data   = $api_response;
		$this->status     = $this->api_data->license ?? null;
		$this->expires    = $this->api_data->expires ?? null;
		$this->site_count = $this->api_data->site_count ?? 0;
		$this->site_limit = $this->api_data->license_limit ?? 0;

	}

	/**
	 * Get the license key
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->license;
	}

	/**
	 * Get the license key status
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Get the license key expiration date
	 *
	 * @param bool $formatted Whether or not to format the date for display.
	 *
	 * @return string
	 */
	public function get_expiration_date( $formatted = false ) {
		return ( $formatted && ! empty( $this->expires ) ) ? format_date( $this->expires ) : $this->expires;
	}

	/**
	 * Get the license key status message
	 *
	 * @return string
	 */
	public function get_status_message() {

		if ( ! $this->is_active() ) {
			return $this->get_error_message();
		} else {
			$expiration = $this->get_expiration_date();

			if ( 'lifetime' === $expiration ) {
				return __( 'Your license key never expires.', 'book-database' );
			} elseif ( empty( $expiration ) ) {
				return __( 'License key activated.', 'book-database' );
			} elseif ( strtotime( '+1 month' ) > strtotime( $expiration ) ) {
				return sprintf( __( 'Your license key expires on %s. <a href="%s" target="_blank">Renew your license key</a> to continue receiving updates and support.', 'book-database' ), $this->get_expiration_date( true ), esc_url( $this->get_renewal_url() ) );
			} else {
				return sprintf( __( 'Your license key expires on %s.', 'book-database' ), $this->get_expiration_date( true ) );
			}
		}

	}

	/**
	 * Get a formatted error message
	 *
	 * @return string
	 */
	public function get_error_message() {

		$code = $this->status;

		if ( ! empty( $this->api_data->error ) ) {
			$code = $this->api_data->error;
		}

		switch ( $code ) {
			case 'expired' :
				$message = sprintf(
					__( 'Your license key expired on %s. Please <a href="%s">renew your license key</a>.', 'book-database' ),
					date_i18n( get_option( 'date_format' ), strtotime( $this->expires, current_time( 'timestamp' ) ) ),
					esc_url( add_query_arg( array(
						'edd_license_key' => urlencode( $this->license )
					), trailingslashit( NOSE_GRAZE_STORE_URL ) . 'checkout/' ) )
				);
				break;

			case 'disabled' :
			case 'revoked' :
				$message = __( 'Your license key has been disabled.', 'book-database' );
				break;

			case 'missing' :
				$message = __( 'Invalid license.', 'book-database' );
				break;

			case 'invalid' :
			case 'site_inactive' :
				$message = __( 'Your license is not active for this URL.', 'book-database' );
				break;

			case 'item_name_mismatch' :
				$message = __( 'This is not a valid license key.', 'book-database' );
				break;

			case 'no_activations_left' :
				$message = __( 'Your license key has reached its activation limit.', 'book-database' );
				break;

			default :
				$message = __( 'An error occurred, please try again.', 'book-database' );
				break;
		}

		return $message;

	}

	/**
	 * Get the license key renewal URL
	 *
	 * @return string
	 */
	public function get_renewal_url() {
		return add_query_arg( 'edd_license_key', urlencode( $this->get_key() ), trailingslashit( NOSE_GRAZE_STORE_URL ) . 'checkout/' );
	}

}