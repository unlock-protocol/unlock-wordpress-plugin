<?php
/**
 * Registers assets for all blocks, and additional global functionality for gutenberg blocks.
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Blocks\Unlock_Box_Block;
use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Blocks
 *
 * @since 3.0.0
 */
class Blocks {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {

		$this->setup_hooks();
		Unlock_Box_Block::get_instance();

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
	 * Enqueue scripts for blocks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function enqueue_scripts() {
		// Automatically load dependencies and version.
		$asset_file = include UNLOCK_PROTOCOL_BUILD_DIR . '/js/blocks.asset.php';

		wp_register_script(
			'unlock-protocol-blocks',
			UNLOCK_PROTOCOL_URL . '/assets/build/js/blocks.js',
			$asset_file['dependencies'],
			filemtime( UNLOCK_PROTOCOL_PATH . '/assets/build/js/blocks.js' ),
			true
		);

		wp_enqueue_script( 'unlock-protocol-blocks' );
	}

	/**
	 * Enqueue styles for blocks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function enqueue_styles() {
		wp_register_style(
			'unlock-protocol-blocks',
			UNLOCK_PROTOCOL_URL . '/assets/build/css/blocks.css',
			array(),
			filemtime( UNLOCK_PROTOCOL_PATH . '/assets/build/css/blocks.css' )
		);

		wp_enqueue_style( 'unlock-protocol-blocks' );
	}
}
