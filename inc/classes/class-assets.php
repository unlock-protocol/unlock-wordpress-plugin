<?php
/**
 * Assets class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Assets
 *
 * @since 3.0.0
 */
class Assets {

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
		 * Action
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

	}

	/**
	 * To enqueue scripts and styles.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->register_styles();

		wp_enqueue_style( 'unlock-protocol' );
	}

	/**
	 * To enqueue scripts and styles. in admin.
	 *
	 * @param string $hook_suffix Admin page name.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		$this->register_scripts();
		$this->register_styles();

		wp_enqueue_media();

		wp_enqueue_style( 'unlock-protocol-admin' );

		$localize_data = array(
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			'unlock_docs'       => [
				'docs'        => 'https://wordpress.org/plugins/unlock-protocol/',
				'deploy_lock' => 'https://app.unlock-protocol.com/dashboard',
			],
			'network_help_url'  => esc_url_raw( '#' ),
			'network_help_text' => __( 'You can add any Ethereum network where you have deployed your lock.', 'unlock-protocol' ),
			'rest'              => array(
				'root'    => esc_url_raw( get_rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'version' => 'unlock-protocol/v1',
			),
		);

		wp_localize_script( 'unlock-protocol-admin', 'unlockProtocol', $localize_data );

		wp_enqueue_script( 'unlock-protocol-admin' );
	}

	/**
	 * Register scripts
	 *
	 * @param array|boolean $scripts Scripts array.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function register_scripts( $scripts = false ) {
		if ( ! $scripts ) {
			$scripts = $this->get_scripts();
		}

		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
			$version   = isset( $script['version'] ) ? $script['version'] : '';

			$this->register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles
	 *
	 * @param array|boolean $styles Styles array.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function register_styles( $styles = false ) {
		if ( ! $styles ) {
			$styles = $this->get_styles();
		}

		foreach ( $styles as $handle => $style ) {
			$deps = isset( $style['deps'] ) ? $style['deps'] : false;

			$this->register_style( $handle, $style['src'], $deps );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_scripts() {
		// Automatically load dependencies and version.
		$asset_file = include UNLOCK_PROTOCOL_BUILD_DIR . '/js/admin.asset.php';

		$scripts = array(
			'unlock-protocol-admin' => array(
				'src'       => 'js/admin.js',
				'deps'      => $asset_file['dependencies'],
				'in_footer' => true,
			),
		);

		return $scripts;
	}

	/**
	 * Get registered styles
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_styles() {
		$styles = array(
			'unlock-protocol-admin' => array(
				'src'  => 'css/style-admin.css',
				'deps' => array( 'wp-components' ),
			),
			'unlock-protocol'       => array(
				'src'  => 'css/main.css',
				'deps' => array(),
			),
		);

		return $styles;
	}

	/**
	 * Register a new script.
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string|bool      $file       script file, path of the script relative to the assets/build/ directory.
	 * @param array            $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default 'false'.
	 * @return bool Whether the script has been registered. True on success, false on failure.
	 */
	public function register_script( $handle, $file, $deps = array(), $ver = false, $in_footer = true ) {
		$src     = sprintf( UNLOCK_PROTOCOL_BUILD_URI . '/%s', $file );
		$version = $this->get_file_version( $file, $ver );

		return wp_register_script( $handle, $src, $deps, $version, $in_footer );
	}

	/**
	 * Register a CSS stylesheet.
	 *
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string|bool      $file    style file, path of the script relative to the assets/build/ directory.
	 * @param array            $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
	 *
	 * @return bool Whether the style has been registered. True on success, false on failure.
	 */
	public function register_style( $handle, $file, $deps = array(), $ver = false, $media = 'all' ) {
		$src     = sprintf( UNLOCK_PROTOCOL_BUILD_URI . '/%s', $file );
		$version = $this->get_file_version( $file, $ver );

		return wp_register_style( $handle, $src, $deps, $version, $media );
	}

	/**
	 * Get file version.
	 *
	 * @param string             $file File path.
	 * @param int|string|boolean $ver  File version.
	 *
	 * @return bool|false|int
	 */
	public function get_file_version( $file, $ver = false ) {
		if ( ! empty( $ver ) ) {
			return $ver;
		}

		$file_path = sprintf( '%s/%s', UNLOCK_PROTOCOL_BUILD_URI, $file );

		return file_exists( $file_path ) ? filemtime( $file_path ) : false;
	}
}
