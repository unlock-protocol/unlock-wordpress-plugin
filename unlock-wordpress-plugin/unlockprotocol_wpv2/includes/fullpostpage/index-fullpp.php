<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



// Include needed external files and their functions
require_once plugin_dir_path( __FILE__ ) . './edit-fullpp.php';




//Register the required meta fields for the full post/page content lock
function unlock_protocol_register_post_meta() 
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
add_action('init', 'unlock_protocol_register_post_meta');




