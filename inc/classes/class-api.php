<?php
/**
 * API class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Rest_Api\Settings;
use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class API
 *
 * @since 3.0.0
 */
class API {

	use Singleton;

	/**
	 * Holds the api classes.
	 *
	 * @var array
	 */
	private $classes;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		$this->classes = array(
			Settings::class,
		);

		add_action( 'rest_api_init', array( $this, 'register_api' ) );

	}

	/**
	 * Register the API
	 *
	 * @return void
	 */
	public function register_api() {
		foreach ( $this->classes as $class ) {
			$object = new $class();
			$object->register_routes();
		}
	}
}
