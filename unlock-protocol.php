<?php
/**
 * Plugin Name: Unlock Protocol Plugin
 * Description:  A plugin to add lock(s) to blocks of content inside of WordPress, on both pages and posts. See https://www.ibenic.com/enable-inner-blocks-gutenberg/ for details about the implementation.
 * Plugin URI:  https://github.com/unlock-protocol/unlock-wordpress-plugin
 * Author:      Unlock
 * Author URI:  https://unlock-protocol.com
 * Version:     3.2.2
 * Text Domain: unlock-protocol
 *
 * @package unlock-protocol
 */

define( 'UNLOCK_PLUGIN_VERSION', '3.2.2' );
define( 'UNLOCK_PROTOCOL_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'UNLOCK_PROTOCOL_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'UNLOCK_PROTOCOL_BUILD_DIR', UNLOCK_PROTOCOL_PATH . '/assets/build' );
define( 'UNLOCK_PROTOCOL_BUILD_URI', UNLOCK_PROTOCOL_URL . '/assets/build' );
define( 'UNLOCK_PROTOCOL_BASENAME_FILE', plugin_basename( __FILE__ ) );
define( 'UNLOCK_PROTOCOL_PLUGIN_FILE', untrailingslashit( __FILE__ ) );

// phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once UNLOCK_PROTOCOL_PATH . '/inc/helpers/autoloader.php';
require_once UNLOCK_PROTOCOL_PATH . '/inc/helpers/custom-functions.php';
// phpcs:enable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

/**
 * To load plugin manifest class.
 *
 * @since 3.0.0
 *
 * @return void
 */
function unlock_protocol_plugin_loader() {
	\Unlock_Protocol\Inc\Plugin::get_instance();
}

unlock_protocol_plugin_loader();
