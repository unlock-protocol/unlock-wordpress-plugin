<?php
/**
 * Registers assets for full post/page functionality.
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Fullpostpage\Unlock_Box_Fullpp;
use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class FullPostPage
 *
 * @since 3.0.0
 */
class Fullpostpage {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {

		$this->setup_hooks();
		Unlock_Box_Fullpp::get_instance();

	}

	/**
	 * Setup hooks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function setup_hooks() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$this->enqueue_scripts();
		$this->enqueue_styles();
	}

	/**
	 * Enqueue scripts for full post/page functionality.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function enqueue_scripts() {
		// Automatically load dependencies and version.
		$asset_file = include UNLOCK_PROTOCOL_BUILD_DIR . '/js/fullpostpage.asset.php';

		wp_register_script(
			'unlock-protocol-fullpostpage',
			UNLOCK_PROTOCOL_URL . '/assets/build/js/fullpostpage.js',
			$asset_file['dependencies'],
			filemtime( UNLOCK_PROTOCOL_PATH . '/assets/build/js/fullpostpage.js' ),
			true
		);

		wp_enqueue_script( 'unlock-protocol-fullpostpage' );
	}

	/**
	 * Enqueue styles for full post/page functionality.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function enqueue_styles() {
		wp_register_style(
			'unlock-protocol-fullpostpage',
			UNLOCK_PROTOCOL_URL . '/assets/build/css/fullpostpage.css',
			array(),
			filemtime( UNLOCK_PROTOCOL_PATH . '/assets/build/css/fullpostpage.css' )
		);

		wp_enqueue_style( 'unlock-protocol-fullpostpage' );
	}
}
