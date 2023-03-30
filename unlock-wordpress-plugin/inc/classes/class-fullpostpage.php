<?php
/**
 * Registers assets for the Full Post Page feature.
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc;

use Unlock_Protocol\Inc\Fullpostpage\Fullpp;
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
        
		Fullpp::get_instance(); 

    }


}
