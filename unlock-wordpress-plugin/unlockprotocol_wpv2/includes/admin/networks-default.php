<?php


// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




function unlockprotocol_wpv2_create_default_networks() {

	// Check if user is logged in and has the required permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to access this page.' );
	}


    global $wpdb;

    // create the network table if it doesn't exist
    $table_name = $wpdb->prefix . 'unlockprotocol_wpv2_networks';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        network_id varchar(255) NOT NULL,
        rpc_url varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // add Ethereum and Polygon networks if they don't exist
    $default_networks = array(
        array(
            'name' => 'Ethereum Mainnet',
            'network_id' => '1',
            'rpc_url' => 'https://rpc.unlock-protocol.com/1',
        ),
        array(
            'name' => 'Polygon  Mainnet',
            'network_id' => '137',
            'rpc_url' => 'https://rpc.unlock-protocol.com/137',
        ),
        array(
            'name' => 'Arbitrum Mainnet',
            'network_id' => '42161',
            'rpc_url' => 'https://rpc.unlock-protocol.com/42161',
        ),
        array(
            'name' => 'GnosisChain Mainnet',
            'network_id' => '100',
            'rpc_url' => 'https://rpc.unlock-protocol.com/100',
        ),
        array(
            'name' => 'Optimism Mainnet',
            'network_id' => '10',
            'rpc_url' => ' https://rpc.unlock-protocol.com/10',
        ),
        array(
            'name' => 'BNBChain Mainnet',
            'network_id' => '56',
            'rpc_url' => 'https://rpc.unlock-protocol.com/56',
        )                
    );

    foreach ( $default_networks as $default_network ) {
        $existing_network = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE network_id = %s",
                $default_network['network_id']
            )
        );

        if ( ! $existing_network ) {
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $default_network['name'],
                    'network_id' => $default_network['network_id'],
                    'rpc_url' => $default_network['rpc_url'],
                )
            );
        }
    }
}
