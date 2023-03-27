<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



use Unlock_Protocol\Inc\Rest_Api\Settings;




/**
 * Holds the api classes.
 */
function up_get_api_classes() {
    return array(
        Settings::class,
    );
}

/**
 * Register the API
 *
 * @return void
 */
function up_register_api() {
    $classes = up_get_api_classes();
    foreach ($classes as $class) {
        $object = new $class();
        $object->register_routes();
    }
}

add_action('rest_api_init', 'up_register_api');
