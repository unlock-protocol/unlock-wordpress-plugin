<?php
/**
 * REST BASE class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\Rest_Api;

use WP_Http;
use WP_REST_Controller;

/**
 * REST BASE class
 */
class Rest_Base extends WP_REST_Controller {

	/**
	 * Namespace.
	 *
	 * @since 3.0.0
	 *
	 * @var string Namespace.
	 */
	public $namespace = 'unlock-protocol';

	/**
	 * Version.
	 *
	 * @var string version.
	 */
	public $version = 'v1';

	/**
	 * Permission check
	 *
	 * @param \WP_REST_Request $request WP Rest Request.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|bool
	 */
	public function admin_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'unlock_protocol_permission_error',
				__( 'You have no permission to do that', 'unlock-protocol' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		return true;
	}

	/**
	 * Get full namespace
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace . '/' . $this->version;
	}
}
