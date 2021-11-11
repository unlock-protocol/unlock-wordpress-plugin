<?php
/**
 * Unlock class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Unlock
 *
 * @since 3.0.0
 */
class Unlock {

	use Singleton;

	/**
	 * Login base url.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const BASE_URL = 'https://app.unlock-protocol.com/checkout';

	/**
	 * Validation url.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const VALIDATE_URL = 'https://locksmith.unlock-protocol.com/api/oauth';

	/**
	 * Post call to validate if a user has access to a content.
	 *
	 * @param string $url Network RPC endpoint.
	 * @param string $lock_address Lock address.
	 * @param string $user_ethereum_address User ethereum address.
	 *
	 * @since 3.0.0
	 *
	 * @return float|int|\WP_Error
	 */
	public static function has_access( $url, $lock_address, $user_ethereum_address = null ) {
		$validation = self::validate( $url, $lock_address, $user_ethereum_address );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$key_expiration = hexdec( $validation['result'] );

		if ( $key_expiration < time() ) {
			return false;
		}

		return true;
	}

	/**
	 * Post call to validate.
	 *
	 * @param string $url Network RPC endpoint.
	 * @param string $lock_address Lock address.
	 * @param string $user_ethereum_address User ethereum address.
	 *
	 * @since 3.0.0
	 *
	 * @return float|int|\WP_Error
	 */
	public static function validate( $url, $lock_address, $user_ethereum_address = null ) {
		$user_ethereum_address = $user_ethereum_address ? $user_ethereum_address : get_user_ethereum_address();
		$user_ethereum_address = substr( $user_ethereum_address, 2 );

		$params = array(
			'method'  => 'eth_call',
			'params'  => array(
				array(
					'to'   => $lock_address,
					'data' => sprintf( '0xabdf82ce000000000000000000000000%s', $user_ethereum_address ),
				),
				'latest',
			),
			'id'      => 31337,
			'jsonrpc' => '2.0',
		);

		$args = array(
			'body'        => wp_json_encode( $params ),
			'redirection' => '30',
			'httpversion' => '1.0',
			'blocking'    => true,
		);

		$response = wp_remote_post( esc_url( $url ), $args );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'unlock_validate_error', $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body;
	}

	/**
	 * Get checkout url.
	 *
	 * @param string $lock_address Lock address.
	 * @param string $network_id Network ID.
	 * @param string $redirect_uri Redirect URI.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_checkout_url( $lock_address, $network_id, $redirect_uri ) {
		$paywall_config = array(
			'locks' => array(
				"$lock_address" => array(
					'network' => (int) $network_id,
				),
			),
		);

		$checkout_url = add_query_arg(
			array(
				'redirectUri'   => $redirect_uri,
				'paywallConfig' => wp_json_encode( $paywall_config ),
			),
			self::BASE_URL
		);

		return $checkout_url;
	}

	/**
	 * Get client id.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_client_id() {
		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Get redirect uri.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_redirect_uri() {
		return wp_login_url();
	}

	/**
	 * Validate auth code.
	 *
	 * @param string $code Authorization code.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error
	 */
	public static function validate_auth_code( $code ) {
		$params = array(
			'grant_type'   => 'authorization_code',
			'client_id'    => self::get_client_id(),
			'redirect_uri' => self::get_redirect_uri(),
			'code'         => $code,
		);

		$args = array(
			'body'        => $params,
			'redirection' => '30',
			'httpversion' => '1.0',
			'blocking'    => true,
		);

		$response = wp_remote_post( esc_url( self::VALIDATE_URL ), $args );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'unlock_validate_auth_code', $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! array_key_exists( 'me', $body ) ) {
			return new \WP_Error( 'unlock_validate_auth_code', __( 'Invalid Account', 'unlock-protocol' ) );
		}

		return $body['me'];
	}

	/**
	 * Get login url.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_login_url() {
		$login_url = add_query_arg(
			array(
				'client_id'    => self::get_client_id(),
				'redirect_uri' => self::get_redirect_uri(),
				'state'        => time(),
			),
			self::BASE_URL
		);

		return $login_url;
	}
}
