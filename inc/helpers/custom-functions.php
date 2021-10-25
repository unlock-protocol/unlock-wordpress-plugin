<?php
/**
 * Unlock_Protocol custom functions.
 *
 * @package unlock-protocol
 */

/**
 * Generate cache key.
 *
 * @param string|array $unique base on that cache key will generate.
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
 * To get cached version of result of WP_Query
 *
 * @param array $args Args of WP_Query.
 *
 * @return array List of posts.
 */
function unlock_protocol_get_cached_posts( $args ) {

	if ( empty( $args ) || ! is_array( $args ) ) {
		return [];
	}

	$args['suppress_filters'] = false;

	$expires_in = MINUTE_IN_SECONDS * 15;

	$cache_key = unlock_protocol_get_cache_key( $args );

	$cache  = new \Unlock_Protocol\Inc\Cache( $cache_key );
	$result = $cache->expires_in( $expires_in )->updates_with( 'get_posts', [ $args ] )->get();

	return ( ! empty( $result ) && is_array( $result ) ) ? $result : [];
}

/**
 * Return template content.
 *
 * @param string $slug Template path.
 * @param array  $vars Variables to be used in the template.
 *
 * @return string Template markup.
 */
function unlock_protocol_get_template_content( $slug, $vars = [] ) {

	ob_start();

	get_template_part( $slug, null, $vars );

	$markup = ob_get_clean();

	return $markup;

}

/**
 * Get plugin template.
 *
 * @param string $template  Name or path of the template within /templates folder without php extension.
 * @param array  $variables pass an array of variables you want to use in template.
 * @param bool   $echo      Whether to echo out the template content or not.
 *
 * @return string|void Template markup.
 */
function unlock_protocol_template( $template, $variables = [], $echo = false ) {

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
 * Get data file content from '/data' directory.
 *
 * @param  string $slug file Data file name without '.php' extention.
 * @param  array  $default   Default value to return if file not found.
 *
 * @return mixed Data file content.
 */
function unlock_protocol_get_data( $slug, $default = [] ) {

	$data_file = sprintf( UNLOCK_PROTOCOL_PATH . '/inc/data/%s.php', $slug );

	if ( file_exists( $data_file ) ) {

		$file_content = require $data_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		return $file_content;

	}

	return $default;

}

/**
 * Check whether current environment is production?
 *
 * Environment type support has been added in WordPress 5.5 and also support added to VIP-Go platform environments.
 *
 * @see https://make.wordpress.org/core/2020/07/24/new-wp_get_environment_type-function-in-wordpress-5-5/
 * @see https://lobby.vip.wordpress.com/2020/08/20/environment-type-support/
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
 * Determine if the current User Agent matches the passed $kind
 *
 * @param string $kind                 Category of mobile device to check for.
 *                                     Either: any, dumb, smart.
 * @param bool   $return_matched_agent Boolean indicating if the UA should be returned.
 *
 * @return bool|string Boolean indicating if current UA matches $kind. If
 *                     $return_matched_agent is true, returns the UA string
 */
function unlock_protocol_is_mobile( $kind = 'any', $return_matched_agent = false ) {

	if ( function_exists( 'jetpack_is_mobile' ) ) {
		return jetpack_is_mobile( $kind, $return_matched_agent );
	}

	return false;
}
