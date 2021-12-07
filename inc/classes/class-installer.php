<?php
/**
 * Installer class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Installer
 *
 * @since 3.0.0
 */
class Installer {

	use Singleton;

	/**
	 * Run the installer
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function run() {
		$this->add_version();
		$this->add_default_networks();
	}

	/**
	 * Add time and version on DB
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function add_version() {
		$installed = get_option( 'unlock_protocol_installed' );

		if ( ! $installed ) {
			update_option( 'unlock_protocol_installed', time() );
		}

		update_option( 'unlock_protocol_version', UNLOCK_PLUGIN_VERSION );
	}

	/**
	 * Add default networks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function add_default_networks() {
		$settings = get_option( 'unlock_protocol_settings', array() );

		if ( isset( $settings['networks'] ) ) {
			return;
		}

		$default_networks = Unlock::networks_list();

		$settings['networks'] = array_values( $default_networks );

		update_option( 'unlock_protocol_settings', $settings, false );
	}

}
