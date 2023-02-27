<?php
/**
 * Menu class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Menu
 *
 * @since 3.0.0
 */
class Menu {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'plugin_action_links_' . UNLOCK_PROTOCOL_BASENAME_FILE, array( $this, 'plugin_action_links' ) );

	}

	/**
	 * Initialize the functions.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function init() {
		$register = true === (bool) get_option( 'users_can_register', false );

		if ( ! $register && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_notices', array( $this, 'membership_admin_notice_error' ) );
		}
	}

	/**
	 * Show admin error notice if membership option is disabled
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function membership_admin_notice_error() {

		$class = 'notice notice-error';

		if ( is_multisite() ) {
			$settings_url = admin_url( 'network/settings.php' );
			$anchor_text  = __( 'Network Settings > Allow new registrations > User accounts may be registered', 'unlock-protocol' );
		} else {
			$settings_url = admin_url( 'options-general.php' );
			$anchor_text  = __( 'Settings > General > Anyone can register', 'unlock-protocol' );
		}

		$setting_anchor = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $settings_url ), $anchor_text );
		/* translators: %s: url */
		$message = sprintf( __( 'Unlock Protocol has detected that user registrations are disabled on this website. Please make sure that %s is enabled.', 'unlock-protocol' ), wp_kses_post( $setting_anchor ) );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );

	}

	/**
	 * Register menu.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		$parent_slug = 'options-general.php';
		$capability  = 'manage_options';

		add_submenu_page(
			$parent_slug,
			'Unlock Protocol',
			'Unlock Protocol',
			$capability,
			'unlock-protocol',
			array( $this, 'dev_site_alert_page' )
		);
	}

	/**
	 * Render menu page.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function dev_site_alert_page() {
		printf( '<div class="unlock-protocol-container" id="unlock-protocol-container"></div>' );
	}

	/**
	 * Plugin action links
	 *
	 * @param array $links plugin links.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		array_unshift( $links, '<a href="' . admin_url( 'options-general.php?page=unlock-protocol' ) . '">' . __( 'Settings', 'unlock-protocol' ) . '</a>' );

		return $links;
	}
}
