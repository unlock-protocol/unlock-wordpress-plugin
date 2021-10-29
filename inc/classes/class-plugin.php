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

		// Load plugin classes.
		Assets::get_instance();
		Login::get_instance();
		Blocks::get_instance();
		Menu::get_instance();
		API::get_instance();

	}

}
