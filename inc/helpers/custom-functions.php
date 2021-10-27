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
