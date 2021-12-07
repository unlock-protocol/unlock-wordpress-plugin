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
 * Class Login
 *
 * @since 3.0.0
 */
class Login {

	use Singleton;

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
		/**
		 * Actions
		 */
		add_action( 'login_form', array( $this, 'login_button' ) );
		add_action( 'authenticate', array( $this, 'authenticate' ) );
		add_action( 'unlock_protocol_register_user', array( $this, 'register' ) );
		add_action( 'wp', array( $this, 'login_user' ) );

		/**
		 * Filters
		 */
		add_filter( 'unlock_authenticate_user', array( $this, 'authenticate' ) );
	}

	/**
	 * Add the login button to login form.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function login_button() {
		$login_button_text       = __( 'Connect Your Crypto Wallet', 'unlock-protocol' );
		$login_button_bg_color   = up_get_general_settings( 'login_button_bg_color', '#000' );
		$login_button_text_color = up_get_general_settings( 'login_button_text_color', '#fff' );

		echo unlock_protocol_get_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'login/button',
			array(
				'login_url'               => Unlock::get_login_url(),
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

		$code  = Helper::filter_input( INPUT_GET, 'code', FILTER_SANITIZE_STRING );
		$state = Helper::filter_input( INPUT_GET, 'state', FILTER_SANITIZE_STRING );

		if ( '' === $code || false === wp_verify_nonce( $state, 'unlock_login_state' ) ) {
			return $user;
		}

		try {
			$ethereum_address = Unlock::validate_auth_code( $code );

			if ( is_wp_error( $ethereum_address ) ) {
				throw new \Exception( __( 'Invalid Account', 'unlock-protocol' ) );
			}

			$ethereum_address = sanitize_text_field( $ethereum_address );

			/**
			 * If there is any user associated with the given ethereum address already, log them in.
			 */
			$user = $this->get_user_with_ethereum_address( $ethereum_address );
			if ( false !== $user ) {
				return $user;
			}

			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();

				update_user_meta( $user->ID, 'unlock_ethereum_address', $ethereum_address );

				return $user;
			}

			/*
			 * Using ethereum address as email and client id as hostname.
			 */
			$email_address = $this->get_email_address( $ethereum_address );

			if ( email_exists( $email_address ) ) {
				$user = get_user_by( 'email', $email_address );

				update_user_meta( $user->ID, 'unlock_ethereum_address', $ethereum_address );

				return $user;
			}

			/**
			 * Check if we need to register the user.
			 *
			 * @param string $ethereum_address Ethereum address from oauth validation.
			 *
			 * @since 3.0.0
			 */
			return apply_filters( 'unlock_protocol_register_user', $ethereum_address );

		} catch ( \Throwable $e ) {
			return new WP_Error( 'unlock_login_failed', $e->getMessage() );
		}
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
		return sprintf( '%1$s@%2$s', esc_html( $ethereum_address ), esc_html( Unlock::get_client_id() ) );
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
			do_action( 'unlock_protocol_user_created', $uid, $user );

			return $user;
		} catch ( \Throwable $e ) {
			throw $e;
		}
	}

	/**
	 * Returns WP_User with specific ethereum address.
	 *
	 * @since 3.0.0
	 *
	 * @param string $ethereum_address Ethereum Address to be searched.
	 *
	 * @return WP_User|false Returns WP_User associated with the ethereum address or returns false if not found.
	 */
	public function get_user_with_ethereum_address( $ethereum_address = '' ) {
		$args = array(
			'meta_key'     => 'unlock_ethereum_address',
			'meta_value'   => $ethereum_address, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_compare' => '=',
		);

		$user_query = new \WP_User_Query( $args );
		$users      = $user_query->get_results();
		if ( ! empty( $users ) ) {
			return $users[0];
		}

		return false;
	}

	/**
	 * Login user.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function login_user() {
		global $wp;
		$user = apply_filters( 'unlock_authenticate_user', null );

		if ( $user ) {
			wp_clear_auth_cookie();
			wp_set_current_user( $user->ID );

			if ( true === is_ssl() ) {
				wp_set_auth_cookie( $user->ID, true, true );
			} else {
				wp_set_auth_cookie( $user->ID, true, false );
			}

			/**
			 * Redirecting it intentionally because removing the code query string.
			 */
			wp_safe_redirect( home_url( $wp->request ) );
			exit;
		}
	}
}
