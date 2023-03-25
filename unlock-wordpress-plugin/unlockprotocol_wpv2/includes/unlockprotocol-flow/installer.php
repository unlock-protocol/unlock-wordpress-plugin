<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



// Include needed external files and their functions
require_once plugin_dir_path( __FILE__ ) . './unlock.php';



/**
 * Run the installer
 *
 * @since 3.0.0
 *
 * @return void
 */
function run_installer() 
{
    add_version();
    add_default_networks();
}




/**
 * Add time and version on DB
 *
 * @since 3.0.0
 *
 * @return void
 */
function add_version() 
{
    $installed = get_option( 'unlock_protocol_installed' );

    if ( ! $installed ) {
        update_option( 'unlock_protocol_installed', time() );
    }

    update_option( 'unlock_protocol_version', UNLOCK_PLUGIN_VERSION );
}



/**
 * Add default networks.
 *
 * @since 3.0.0
 *
 * @return void
 */
function add_default_networks() 
{
    $settings = get_option( 'unlock_protocol_settings', array() );

    if ( isset( $settings['networks'] ) ) {
        return;
    }

    $default_networks = networks_list();

    $settings['networks'] = array_values( $default_networks );

    update_option( 'unlock_protocol_settings', $settings, false );
}

// Run the installer
run_installer();
