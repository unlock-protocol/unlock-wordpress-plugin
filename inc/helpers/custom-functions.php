<?php
/**
 * Unlock_Protocol custom functions.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

/**
 * Generate cache key.
 *
 * @param string|array $unique base on that cache key will generate.
 *
 * @since 3.0.0
 *
 * @return string Cache key.
 */
function unlock_protocol_get_cache_key( $unique = '' ) {

	$cache_key = 'unlock_protocol_cache_';

	if ( is_array( $unique ) ) {
		ksort( $unique );
		$unique = wp_json_encode( $unique );
	}

	$md5 = md5( $unique );
	$key = $cache_key . $md5;

	return $key;
}

/**
 * Check whether current environment is production?
 *
 * Environment type support has been added in WordPress 5.5 and also support added to VIP-Go platform environments.
 *
 * @see https://make.wordpress.org/core/2020/07/24/new-wp_get_environment_type-function-in-wordpress-5-5/
 * @see https://lobby.vip.wordpress.com/2020/08/20/environment-type-support/
 *
 * @since 3.0.0
 *
 * @return bool Return true if it's production else return false.
 */
function unlock_protocol_is_production() {

	if ( 'production' === wp_get_environment_type() ) {
		return true;
	}

	return false;

}

/**
 * Get plugin template.
 *
 * @param string $template  Name or path of the template within /templates folder without php extension.
 * @param array  $variables pass an array of variables you want to use in template.
 * @param bool   $echo      Whether to echo out the template content or not.
 *
 * @since 3.0.0
 *
 * @return string|void Template markup.
 */
function unlock_protocol_get_template( $template, $variables = [], $echo = false ) {

	$template_file = sprintf( '%1$s/templates/%2$s.php', UNLOCK_PROTOCOL_PATH, $template );

	if ( ! file_exists( $template_file ) ) {
		return '';
	}

	if ( ! empty( $variables ) && is_array( $variables ) ) {
		extract( $variables, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Used as an exception as there is no better alternative.
	}

	ob_start();

	include $template_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

	$markup = ob_get_clean();

	if ( ! $echo ) {
		return $markup;
	}

	echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output escaped already in template.
}

/**
 * Get general settings.
 *
 * @param string|null $key Option key.
 * @param string      $default Default value.
 *
 * @since 3.0.0
 *
 * @return string|array
 */
function up_get_general_settings( $key = null, $default = '' ) {
	$settings = get_option( 'unlock_protocol_settings', array() );

	if ( ! isset( $settings['general'] ) ) {
		return $default;
	}

	$general = $settings['general'];

	if ( ! $key ) {
		return $general;
	}

	return isset( $general[ $key ] ) && '' !== $general[ $key ] ? $general[ $key ] : $default;
}

/**
 * Get user ethereum address.
 *
 * @param int $user_id User ID.
 *
 * @since 3.0.0
 *
 * @return mixed
 */
function up_get_user_ethereum_address( $user_id = null ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return get_user_meta( $user_id, 'unlock_ethereum_address', true );
}
