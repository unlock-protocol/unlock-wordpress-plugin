<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// add submenu 2: Network setting page
// add submenu 2: Network setting page
function unlockprotocol_wpv2_networks_page()
{
    // Check if user is logged in and has the required permissions
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'unlockprotocol_wpv2_networks';

    // check if edit network form is submitted
    if (isset($_POST['edit_network'])) {
        // retrieve network ID from submitted form data
        $network_id = absint($_POST['network_id']);

        // retrieve network data from database
        $network = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $network_id));

        // display edit network form
        unlockprotocol_wpv2_display_edit_network_form($network);
        return;
    }

    // check if update network form is submitted
    if (isset($_POST['update_network'])) {
        // sanitize input values
        $network_name = sanitize_text_field($_POST['network_name']);
        $network_id = absint($_POST['network_id']);
        $rpc_url = sanitize_text_field($_POST['rpc_url']);

        // update network in database
        $wpdb->update(
            $table_name,
            array(
                'name' => $network_name,
                'rpc_url' => $rpc_url,
            ),
            array('id' => $network_id)
        );

        // add feedback message
        add_settings_error(
            'unlockprotocol_wpv2_networks_page',
            'network_updated',
            'Network updated successfully.',
            'updated'
        );
    }



    // check if add new network form is submitted
    if (isset($_POST['unlockprotocol_wpv2_save_network'])) {
        // sanitize input values
        $network_name = sanitize_text_field($_POST['unlockprotocol_wpv2_network_name']);
        $network_id = sanitize_text_field($_POST['unlockprotocol_wpv2_network_id']);
        $network_rpc = sanitize_text_field($_POST['unlockprotocol_wpv2_network_rpc']);

        // save new network in database
        $wpdb->insert(
            $table_name,
            array(
                'name' => $network_name,
                'network_id' => $network_id,
                'rpc_url' => $network_rpc,
            )
        );

        // add feedback message
        add_settings_error(
            'unlockprotocol_wpv2_networks_page',
            'network_added',
            'New network added to networks list successfully.',
            'updated'
        );
    }

    // display add new network form
    unlockprotocol_wpv2_display_add_network_form();

    // display list of networks
    unlockprotocol_wpv2_display_networks();

    // display feedback message
    settings_errors('unlockprotocol_wpv2_networks_page');
}





// function to display add new network form
function unlockprotocol_wpv2_display_add_network_form() 
{
    ?>
    <div class="wrap">
		
        <h1>Network Settings</h1>
        <p>Here you can add, edit or completely delete any Blockchain network configuration for your locks.</p>

        <h2>Add A Network</h2>
        <form method="post">
            <label for="unlockprotocol_wpv2_network_name">Network Name:</label>
            <br>
            <input type="text" name="unlockprotocol_wpv2_network_name" id="unlockprotocol_wpv2_network_name" required>
            <br>
            <label for="unlockprotocol_wpv2_network_id">Network ID:</label>
            <br>
            <input type="text" name="unlockprotocol_wpv2_network_id" id="unlockprotocol_wpv2_network_id" required>
            <br>
            <label for="unlockprotocol_wpv2_network_rpc">Network RPC Endpoint:</label>
            <br>
            <input type="text" name="unlockprotocol_wpv2_network_rpc" id="unlockprotocol_wpv2_network_rpc" required>
            <br>
            <br>
            <input type="submit" name="unlockprotocol_wpv2_save_network" value="Save New Network" class="button button-primary">
        </form>
    </div>
    <?php
}




// Function to display edit network form
function unlockprotocol_wpv2_display_edit_network_form( $network ) 
{
    ?>
    <h2>Edit Network</h2>
    <form method="post">
        <input type="hidden" name="network_id" value="<?php echo $network->id; ?>">
        <label for="network_name">Network Name:</label>
        <input type="text" id="network_name" name="network_name" value="<?php echo $network->name; ?>">
        <br>
        <label for="network_id">Network ID:</label>
        <input type="text" id="network_id" name="network_id" value="<?php echo $network->network_id; ?>">
        <br>
        <label for="rpc_url">RPC Endpoint:</label>
        <input type="text" id="rpc_url" name="rpc_url" value="<?php echo $network->rpc_url; ?>">
        <br>
        <button type="submit" name="update_network">Save Changes</button>
    </form>
    <?php

if ( isset( $_POST['update_network'] ) ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'unlockprotocol_wpv2_networks';
    // retrieve network ID from submitted form data
    $network_id = absint( $_POST['network_id'] );

    // retrieve network data from database
    $network = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $network_id ) );

    // sanitize input values
    $network_name = sanitize_text_field( $_POST['network_name'] );
    $network_id = sanitize_text_field( $_POST['network_id'] );
    $network_rpc = sanitize_text_field( $_POST['rpc_url'] );

    // update network in database
    $wpdb->update(
        $table_name,
        array(
            'name' => $network_name,
            'network_id' => $network_id,
            'rpc_url' => $network_rpc,
        ),
        array( 'id' => $network_id )
    );

    // add feedback message
    add_settings_error(
        'unlockprotocol_wpv2_networks_page',
        'network_updated',
        'Network updated successfully.',
        'updated'
    );
}


}



// Function to display list of networks
function unlockprotocol_wpv2_display_networks() {

    // retrieve all networks from database
    global $wpdb;
    $table_name = $wpdb->prefix . 'unlockprotocol_wpv2_networks';
    $networks = $wpdb->get_results( "SELECT * FROM $table_name" );
    ?>

    <h2>Networks List</h2>
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Network Name</th>
                <th>Network ID</th>
                <th>Network RPC Endpoint</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach( $networks as $network ) { ?>
                <tr>
                    <td><?php echo $network->name; ?></td>
                    <td><?php echo $network->network_id; ?></td>
                    <td><?php echo $network->rpc_url; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="network_id" value="<?php echo $network->id; ?>">
                            <button type="submit" name="edit_network">EDIT</button>
                            <button type="submit" name="delete_network">DELETE</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php
}


