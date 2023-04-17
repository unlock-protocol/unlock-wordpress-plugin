<?php
/**
 * Registers assets for full post/page functionality.
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\FullPostPage\Unlock_Box_Full_Post_Page;
use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Full-Post-Page
 *
 * @since 4.0.0
 */
class FullPostPage {

	use Singleton;


	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	protected function __construct() {
		$this->setup_hooks();
		Unlock_Box_Full_Post_Page::get_instance();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setup_hooks() {
		add_action( 'init', [ $this, 'meta_fields_register_meta' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Register meta fields.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function meta_fields_register_meta() {
		register_meta(
			'post',
			'unlock_protocol_post_locks',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 4.0.0
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
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function enqueue_scripts() {
		// Automatically load dependencies and version.
		$asset_file = include UNLOCK_PROTOCOL_BUILD_DIR . '/js/full-post-page.asset.php';

		wp_register_script(
			'unlock-protocol-full-post-page',
			UNLOCK_PROTOCOL_URL . '/assets/build/js/full-post-page.js',
			$asset_file['dependencies'],
			filemtime( UNLOCK_PROTOCOL_PATH . '/assets/build/js/full-post-page.js' ),
			true
		);

		wp_enqueue_script( 'unlock-protocol-full-post-page' );
	}

	/**
	 * Enqueue styles for full post/page functionality.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function enqueue_styles() {
		wp_register_style(
			'unlock-protocol-full-post-page',
			UNLOCK_PROTOCOL_URL . '/assets/build/css/full-post-page.css',
			array(),
			filemtime( UNLOCK_PROTOCOL_PATH . '/assets/build/css/full-post-page.css' )
		);

		wp_enqueue_style( 'unlock-protocol-full-post-page' );
	}
}
