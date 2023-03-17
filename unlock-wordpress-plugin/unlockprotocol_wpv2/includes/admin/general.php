<?php


// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



// add submenu 1: General setting page
function unlockprotocol_wpv2_general_page() 
{
	// Check if user is logged in and has the required permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to access this page.' );
	}

	?>
	<div class="wrap">
		<h1>General Settings</h1>
		<p>Here you can configure and customize some features of the Unlock Protocol Version 2 WordPress plugin.</p>
	</div>
	<?php
}