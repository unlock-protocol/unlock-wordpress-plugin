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
	 * Returns the locksmith base URL used to validate the auth tokens.
	 */
	public static function get_locksmith_validate_url_base() {
		$settings = get_option( 'unlock_protocol_settings', array() );

		$locksmith_url_base = 'https://locksmith.unlock-protocol.com/api/oauth';
		if (isset($settings['general']['locksmith_url_base']) && 
			filter_var($settings['general']['locksmith_url_base'], FILTER_VALIDATE_URL)) {
			$locksmith_url_base = $settings['general']['locksmith_url_base'];
		}
		return $locksmith_url_base;
	}

	/**
	 * Returns the checkout base URL used to for both auth and checkout.
	 */
	public static function get_checkout_url_base() {
		$settings = get_option( 'unlock_protocol_settings', array() );
		$checkout_url_base = 'https://app.unlock-protocol.com/checkout';
		if (isset($settings['general']['checkout_url_base']) && 
			filter_var($settings['general']['checkout_url_base'], FILTER_VALIDATE_URL)) {
			$checkout_url_base = $settings['general']['checkout_url_base'];
		}
		return $checkout_url_base;
	}

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
				$has_unlocked = hexdec( $validation['result'] ) == 1;
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
						'data' => sprintf( '0x6d8ea5b4000000000000000000000000%s', $user_ethereum_address ),
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
		$settings = get_option( 'unlock_protocol_settings', array() );
		// Let's add the default setup in the config too!

		$default_paywall_config = array();
		if (isset($settings['general']['custom_paywall_config'])) {
			$default_paywall_config = json_decode($settings['general']['custom_paywall_config'], true);
		}

		foreach ($locks as $lock) {
			$paywall_locks[$lock["address"]] = array('network' => (int) $lock["network"],);
		}

		$paywall_config = apply_filters(
			'unlock_protocol_paywall_config',
			array_merge(
				$default_paywall_config ?? array(), 
				array(
					'locks'       => $paywall_locks,
					'pessimistic' => true,
				)
			)
		);
		$checkout_url = add_query_arg(
			array(
				'redirectUri'   => $redirect_uri,
				'paywallConfig' => wp_json_encode( $paywall_config ),
			),
			self::get_checkout_url_base()
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

		$response = wp_remote_post( esc_url( self::get_locksmith_validate_url_base() ), $args );

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
			self::get_checkout_url_base()
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
				'network_name'         => 'goerli',
				'network_id'           => 5,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/5',
			),
			'mainnet'  => array(
				'network_name'         => 'mainnet',
				'network_id'           => 1,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/1',
			),
			'xdai'     => array(
				'network_name'         => 'gnosis chain',
				'network_id'           => 100,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/100',
			),
			'polygon'  => array(
				'network_name'         => 'polygon',
				'network_id'           => 137,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/137',
			),
			'optimism'     => array(
				'network_name'         => 'Optimism',
				'network_id'           => 10,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/10',
			),
			'arbitrum' => array(
				'network_name'         => 'arbitrum',
				'network_id'           => 42161,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/42161',
			),
			'binance'  => array(
				'network_name'         => 'BNB Chain',
				'network_id'           => 56,
				'network_rpc_endpoint' => 'https://rpc.unlock-protocol.com/56',
			),
		);

		return apply_filters( 'unlock_protocol_network_list', $networks );
	}


	/**
	 * Render checkout button.
	 *
	 * @param array $locks locks.
	 *
	 * @return mixed|void
	 */
	public static function render_checkout_button( $locks ) {
		$checkout_url = Unlock::get_checkout_url( $locks, get_permalink() );

		$checkout_button_text       = up_get_general_settings( 'checkout_button_text', __( 'Purchase this', 'unlock-protocol' ) );
		$checkout_button_bg_color   = up_get_general_settings( 'checkout_button_bg_color', '#000' );
		$checkout_button_text_color = up_get_general_settings( 'checkout_button_text_color', '#fff' );
		$blurred_image_activated    = wp_validate_boolean( up_get_general_settings( 'checkout_blurred_image_button', false ) );

		$template_data = array(
			'checkout_url'               => $checkout_url,
			'checkout_button_text'       => $checkout_button_text,
			'checkout_button_bg_color'   => $checkout_button_bg_color,
			'checkout_button_text_color' => $checkout_button_text_color,
			'blurred_image_activated'    => $blurred_image_activated,
		);

		// Fetching some more data if blurred image button type is activated.
		if ( $blurred_image_activated ) {
			$checkout_button_description = up_get_general_settings( 'checkout_button_description', __( 'To view this content please', 'unlock-protocol' ) );
			$checkout_bg_image           = up_get_general_settings( 'checkout_bg_image' );

			$template_data['checkout_button_description'] = $checkout_button_description;
			$template_data['checkout_bg_image']           = $checkout_bg_image;
		}

		$html_template = unlock_protocol_get_template( 'login/checkout-button', $template_data );

		return apply_filters( 'unlock_protocol_checkout_content', $html_template, $template_data );
	}

	/**
	 * Render login button.
	 *
	 * @return mixed|void
	 */
	public static function render_login_button() {
		$login_button_text       = up_get_general_settings( 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );
		$login_button_bg_color   = up_get_general_settings( 'login_button_bg_color', '#000' );
		$login_button_text_color = up_get_general_settings( 'login_button_text_color', '#fff' );
		$blurred_image_activated = wp_validate_boolean( up_get_general_settings( 'login_blurred_image_button', false ) );

		$template_data = array(
			'login_url'               => Unlock::get_login_url( get_permalink() ),
			'login_button_text'       => $login_button_text,
			'login_button_bg_color'   => $login_button_bg_color,
			'login_button_text_color' => $login_button_text_color,
			'blurred_image_activated' => $blurred_image_activated,
		);

		// Fetching some more data if blurred image button type is activated.
		if ( $blurred_image_activated ) {
			$login_button_description = up_get_general_settings( 'login_button_description', __( 'To view this content please', 'unlock-protocol' ) );
			$login_bg_image           = up_get_general_settings( 'login_bg_image' );

			$template_data['login_button_description'] = $login_button_description;
			$template_data['login_bg_image']           = $login_bg_image;
		}

		$html_template = unlock_protocol_get_template( 'login/button', $template_data );

		return apply_filters( 'unlock_protocol_login_content', $html_template, $template_data );
	}

	/**
	 * Render block.
	 *
	 * @param array  $locks List of attributes passed in block.
	 * @param string $content post content.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML elements.
	 */
	public static function render_content( $locks, $content ) {
		// Bail out if current user is admin or the author.
		if ( current_user_can( 'manage_options' ) || ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ) {
			return $content;
		}

		if (
			! is_user_logged_in() ||
			( is_user_logged_in() && ! up_get_user_ethereum_address() )
		) {
			return Unlock::render_login_button();
		}

		$settings = get_option( 'unlock_protocol_settings', array() );
		$networks = isset( $settings['networks'] ) ? $settings['networks'] : array();

		if ( !Unlock::has_access( $networks, $locks ) ) {
			return Unlock::render_checkout_button( $locks );
		}
		return $content;
	}



}
