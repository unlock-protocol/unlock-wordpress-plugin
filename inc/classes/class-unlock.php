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
	public static function has_access( $networks, $locks, $user_ethereum_address = null ) {

		$has_unlocked = false;

		foreach($locks as $lock) {
			if (!$has_unlocked) {
				// Find the URL!
				foreach($networks as $network) {
					if ($network["network_id"] == $lock['network']) {
						$url = $network["network_rpc_endpoint"];
					}
				}

				$validation = self::validate( $url, $lock['address'], $user_ethereum_address );

				if ( is_wp_error( $validation ) || ! isset( $validation['result'] ) ) {
					break;
				}

				$key_expiration = hexdec( $validation['result'] );

				if ( $key_expiration > time() ) {
					$has_unlocked = true;
				}
			}
		}

		return $has_unlocked;
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
		$user_ethereum_address = $user_ethereum_address ? $user_ethereum_address : up_get_user_ethereum_address();
		$user_ethereum_address = substr( $user_ethereum_address, 2 );

		$params = apply_filters(
			'unlock_protocol_user_validate_params',
			array(
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
			)
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
	public static function get_checkout_url( $locks, $redirect_uri ) {
		$paywall_locks = array();

		foreach ($locks as $lock) {
			$paywall_locks[$lock["address"]] = array('network' => (int) $lock["network"],);
		}

		$paywall_config = apply_filters(
			'unlock_protocol_paywall_config',
			array(
				'locks'       => $paywall_locks,
				'pessimistic' => true,
			)
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
		return apply_filters( 'unlock_protocol_get_client_id', wp_parse_url( home_url(), PHP_URL_HOST ) );
	}

	/**
	 * Get redirect uri.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_redirect_uri() {
		return apply_filters( 'unlock_protocol_get_redirect_uri', wp_login_url() );
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
		$params = apply_filters(
			'unlock_protocol_validate_auth_code_params',
			array(
				'grant_type'   => 'authorization_code',
				'client_id'    => self::get_client_id(),
				'redirect_uri' => self::get_redirect_uri(),
				'code'         => $code,
			)
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
	 * @param string $redirect_uri Redirect URI.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_login_url( $redirect_uri = null ) {
		$login_url = add_query_arg(
			array(
				'client_id'    => self::get_client_id(),
				'redirect_uri' => $redirect_uri ? $redirect_uri : self::get_redirect_uri(),
				'state'        => wp_create_nonce( 'unlock_login_state' ),
			),
			self::BASE_URL
		);

		return apply_filters( 'unlock_protocol_get_login_url', $login_url );
	}

	/**
	 * Get networks list.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function networks_list() {
		$networks = array(
			'mainnet'  => array(
				'network_name'         => 'mainnet',
				'network_id'           => 1,
				'network_rpc_endpoint' => 'https://mainnet.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'ropsten'  => array(
				'network_name'         => 'ropsten',
				'network_id'           => 3,
				'network_rpc_endpoint' => 'https://ropsten.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'rinkeby'  => array(
				'network_name'         => 'rinkeby',
				'network_id'           => 4,
				'network_rpc_endpoint' => 'https://rinkeby.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'kovan'    => array(
				'network_name'         => 'kovan',
				'network_id'           => 42,
				'network_rpc_endpoint' => 'https://kovan.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
			),
			'xdai'     => array(
				'network_name'         => 'xdai',
				'network_id'           => 100,
				'network_rpc_endpoint' => 'https://rpc.xdaichain.com/',
			),
			'polygon'  => array(
				'network_name'         => 'polygon',
				'network_id'           => 137,
				'network_rpc_endpoint' => 'https://rpc-mainnet.maticvigil.com/',
			),
			'arbitrum' => array(
				'network_name'         => 'arbitrum',
				'network_id'           => 42161,
				'network_rpc_endpoint' => 'https://arb1.arbitrum.io/rpc',
			),
			'binance'  => array(
				'network_name'         => 'binance',
				'network_id'           => 56,
				'network_rpc_endpoint' => 'https://bsc-dataseed.binance.org/',
			),
		);

		return apply_filters( 'unlock_protocol_network_list', $networks );
	}
}
