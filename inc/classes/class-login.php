<?php
/**
 * Login class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Traits\Singleton;
use Unlock_Protocol\Inc\Utils\Helper;
use WP_Error;
use WP_User;

/**
 * Class Assets
 *
 * @since 3.0.0
 */
class Login {

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
	 * Construct method.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * To setup action/filter.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function setup_hooks() {
		add_action( 'login_form', array( $this, 'login_button' ) );
		add_action( 'authenticate', array( $this, 'authenticate' ) );
		add_action( 'unlock_register_user', array( $this, 'register' ) );
	}

	/**
	 * Add the login button to login form.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function login_button() {
		$login_button_text       = get_general_settings( 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );
		$login_button_bg_color   = get_general_settings( 'login_button_bg_color', '#000' );
		$login_button_text_color = get_general_settings( 'login_button_text_color', '#fff' );

		echo unlock_protocol_get_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'login/button',
			array(
				'login_url'               => $this->get_login_url(),
				'login_button_text'       => $login_button_text,
				'login_button_bg_color'   => $login_button_bg_color,
				'login_button_text_color' => $login_button_text_color,
			)
		);
	}

	/**
	 * Authenticate the user.
	 *
	 * @param WP_User|null $user User object. Default is null.
	 *
	 * @since 3.0.0
	 *
	 * @return WP_User|WP_Error
	 * @throws \Exception Invalid Account.
	 */
	public function authenticate( $user = null ) {
		if ( $user instanceof WP_User ) {
			return $user;
		}

		$code = Helper::filter_input( INPUT_GET, 'code', FILTER_SANITIZE_STRING );

		if ( ! $code ) {
			return $user;
		}

		$state = Helper::filter_input( INPUT_GET, 'state', FILTER_SANITIZE_STRING );

		try {
			$params = array(
				'grant_type'   => 'authorization_code',
				'client_id'    => $this->get_client_id(),
				'redirect_uri' => $this->get_redirect_uri(),
				'code'         => $code,
			);

			$args = array(
				'body'        => $params,
				'redirection' => '30',
				'httpversion' => '1.0',
				'blocking'    => true,
			);

			$response = wp_remote_post( esc_url( self::VALIDATE_URL ), $args );

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! array_key_exists( 'me', $body ) ) {
				throw new \Exception( __( 'Invalid Account', 'unlock-protocol' ) );
			}

			/*
			 * Using ethereum address as email and client id as hostname.
			 */
			$ethereum_address = sanitize_text_field( $body['me'] );
			$email_address    = $this->get_email_address( $ethereum_address );

			if ( email_exists( $email_address ) ) {
				$user = get_user_by( 'email', $email_address );

				update_user_meta( $user->ID, 'unlock_ethereum_address', $ethereum_address );

				return $user;
			}

			/**
			 * Check if we need to register the user.
			 *
			 * @param string $ethereum_address Ethereum address from oauth validation.
			 * @since 3.0.0
			 */
			return apply_filters( 'unlock_register_user', $ethereum_address );

		} catch ( \Throwable $e ) {
			return new WP_Error( 'unlock_login_failed', $e->getMessage() );
		}
	}

	/**
	 * Get client id.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_client_id() {
		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Get redirect uri.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_redirect_uri() {
		return wp_login_url();
	}

	/**
	 * Get email address from Ethereum address.
	 *
	 * @param string $ethereum_address Ethereum address.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_email_address( $ethereum_address ) {
		return sprintf( '%1$s@%2$s', esc_html( $ethereum_address ), esc_html( $this->get_client_id() ) );
	}

	/**
	 * Register the new user if setting is on for registration.
	 *
	 * @param string $ethereum_address Ethereum address.
	 *
	 * @since 3.0.0
	 *
	 * @return WP_User|null
	 * @throws \Exception Registration is not allowed.
	 * @throws \Throwable Invalid email registration.
	 */
	public function register( $ethereum_address ) {
		$register = true === (bool) get_option( 'users_can_register', false );

		if ( ! $register ) {
			throw new \Exception( __( 'Registration is not allowed.', 'unlock-protocol' ) );
		}

		try {
			$user_email = $this->get_email_address( $ethereum_address );

			$uid = wp_insert_user(
				array(
					'user_login' => $ethereum_address,
					'user_pass'  => wp_generate_password( 10 ),
					'user_email' => $user_email,
				)
			);

			$user = get_user_by( 'id', $uid );

			update_user_meta( $user->ID, 'unlock_ethereum_address', $ethereum_address );

			/**
			 * Fires once the user has been registered successfully.
			 */
			do_action( 'unlock_user_created', $uid, $user );

			return $user;
		} catch ( \Throwable $e ) {
			throw $e;
		}
	}

	/**
	 * Get login url.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_login_url() {
		$login_url = add_query_arg(
			array(
				'client_id'    => $this->get_client_id(),
				'redirect_uri' => $this->get_redirect_uri(),
				'state'        => time(),
			),
			self::BASE_URL
		);

		return $login_url;
	}
}
