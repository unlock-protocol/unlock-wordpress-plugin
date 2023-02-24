<?php
/**
 * Plugin manifest class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use \Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Plugin
 *
 * @since 3.0.0
 */
class Plugin {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {

		register_activation_hook( UNLOCK_PROTOCOL_PLUGIN_FILE, array( $this, 'activate' ) );

		// Load plugin classes.
		Assets::get_instance();
		Login::get_instance();
		Blocks::get_instance();
		Menu::get_instance();
		API::get_instance();

	}

	/**
	 * Activator of the plugin.
	 *
	 * @return void
	 */
	public function activate() {
		$installer = Installer::get_instance();
		$installer->run();

	}
}
