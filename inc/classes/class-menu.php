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

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'plugin_action_links_' . UNLOCK_PROTOCOL_BASENAME_FILE, array( $this, 'plugin_action_links' ) );

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
