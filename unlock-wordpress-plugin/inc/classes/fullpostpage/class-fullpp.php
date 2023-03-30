<?php
/**
 * Full Post Page class.
 *
 * @since 3.0.0
 *
 * @package Full_Post_Page
 */

namespace Unlock_Protocol\Inc\Fullpostpage;

use Unlock_Protocol\Inc\FullPostPage\Editfullpp;
use Unlock_Protocol\Inc\FullPostPage\Unlock_Box_Fullpp;
use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Fullpp
 *
 * @since 3.0.0
 */
class Fullpp {

    use Singleton;

    /**
     * Construct method.
     *
     * @since 3.0.0
     */
    protected function __construct() 
    {

        $this->setup_hooks();
		Editfullpp::get_instance();
        Unlock_Box_Fullpp::get_instance();
    }

    

    /**
     * Setup hooks.
     *
     * @since 3.0.0
     */
    protected function setup_hooks() 
    {
        /**
         * Actions.
         */
        add_action('init', [$this, 'unlock_protocol_register_post_meta']);
    }

    /**
     * Register post meta.
     *
     * @since 3.0.0
     */
    public function unlock_protocol_register_post_meta() 
    {
        register_meta('post', 'fullpp_locks', array(
            'type' => 'array',
            'single' => true,
            'show_in_rest' => true,
        ));

        register_meta('post', 'fullpp_ethereum_networks', array(
            'type' => 'array',
            'single' => true,
            'show_in_rest' => true,
        ));
    }
}
