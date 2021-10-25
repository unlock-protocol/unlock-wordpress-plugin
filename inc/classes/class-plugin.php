<?php
/**
 * Plugin manifest class.
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use \Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Plugin
 */
class Plugin {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		// Load plugin classes.
		Assets::get_instance();
		Blocks::get_instance();

	}

}
