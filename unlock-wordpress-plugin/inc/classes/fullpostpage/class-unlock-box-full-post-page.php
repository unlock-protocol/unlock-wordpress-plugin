<?php
/**
 * Unlock box dynamic class for full post/page.
 *
 * @since 4.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\FullPostPage;

use Unlock_Protocol\Inc\Traits\Singleton;
use Unlock_Protocol\Inc\Unlock;

/**
 * Class Unlock_Box_Fullpp
 *
 * @since 4.0.0
 */
class Unlock_Box_Full_Post_Page {

    use Singleton;

    /**
     * Construct method.
     *
     * @since 4.0.0
     */
    protected function __construct() {

        $this->setup_hooks();

    }

    /**
     * Setup hooks.
     *
     * @since 4.0.0
     */
    protected function setup_hooks() {
		/**
		 * Filters.
		 */
		add_filter( 'the_content', array( $this, 'trigger_unlockprotocol_flow' ) );	
    }
	
	/**
	 * Trigger the Unlock Protocol flow
	 *
	 * @since 4.0.0
	 *
	 * @param string $content The original post content.
	 * 
	 */
	public function trigger_unlockprotocol_flow( $content ) {
		// Get the current post ID.
		$post_id = get_the_ID();

		// Retrieve the saved attributes.
		$attributes = get_post_meta( $post_id, 'unlock_protocol_post_locks', true );

		if ( ! empty( $attributes ) ) {
			$locks = json_decode($attributes, true);
			// Call render_content() to trigger the Unlock Protocol flow.
			return Unlock::render_content( $locks, $content );
		} else {			
			return $content;
		}
	}

}
