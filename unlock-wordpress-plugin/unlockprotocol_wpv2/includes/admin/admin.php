<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




// Create a new menu item in wp admin page
function unlockprotocol_wpv2_add_menu() 
{
	// Check if user is logged in and has the required permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to access this page.' );
	}

    add_menu_page(
      'Unlock Protocol', // page title
      'Unlock Protocol', // menu title
      'manage_options', // capability
      'unlockprotocol_wpv2_general', // menu slug
      'unlockprotocol_wpv2_general_page', // callback function
      'dashicons-lock', // icon
      2 // position
    );
	
  }
  add_action( 'admin_menu', 'unlockprotocol_wpv2_add_menu' );




// Add submenu
function unlockprotocol_wpv2_add_submenus() {
	add_submenu_page(
		'unlockprotocol_wpv2_general',
		'General',
		'General',
		'manage_options',
		'unlockprotocol_wpv2_general',
		'unlockprotocol_wpv2_general_page'
	);
	add_submenu_page(
		'unlockprotocol_wpv2_general',
		'Networks',
		'Networks',
		'manage_options',
		'unlockprotocol_wpv2_networks',
		'unlockprotocol_wpv2_networks_page'
	);
}
add_action( 'admin_menu', 'unlockprotocol_wpv2_add_submenus' );






// Include submenu files and their functions
require_once plugin_dir_path( __FILE__ ) . './general.php';
require_once plugin_dir_path( __FILE__ ) . './networks.php';
