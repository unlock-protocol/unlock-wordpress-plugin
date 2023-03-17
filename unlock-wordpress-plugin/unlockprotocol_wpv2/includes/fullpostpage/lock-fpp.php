<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




// Create and Add custom meta box with "Add Lock" button
function unlockprotocol_wpv2_add_custom_meta_box() 
{
    $post_types = get_post_types(); //detect any custom post types like courses and add lock features automatically
    foreach ($post_types as $post_type) {
        add_meta_box(
            'unlockprotocol_wpv2_meta_box', // ID
            'Unlock', // title
            'unlockprotocol_wpv2_show_custom_meta_box', // callback function
            $post_type, // post type
            'side', // position
            'high' // priority
        );
    }
}
add_action('add_meta_boxes', 'unlockprotocol_wpv2_add_custom_meta_box');





// Show custom meta box with "Lock Networks" dropdown and "Lock Contract ID" input box
function unlockprotocol_wpv2_show_custom_meta_box() 
{
    global $post;

    $meta_network = get_post_meta($post->ID, 'unlockprotocol_wpv2_network_meta', true);
    $meta_id = get_post_meta($post->ID, 'unlockprotocol_wpv2_id_meta', true);

    global $wpdb;
    $table_name = $wpdb->prefix . 'unlockprotocol_wpv2_networks';
    $networks = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
  
        <button id="add-lock-button" onclick="toggleLockInputs()" class="button button-primary">Add Lock</button>

        <div id="lock-inputs" style="display: none;">
            <label for="unlockprotocol_wpv2_network_meta">Lock Networks:</label>
            <select name="unlockprotocol_wpv2_network_meta" id="unlock-network-select" onchange="toggleLockIdInput()">
            <option value="" <?php selected($meta_network, ""); ?>></option>
            <?php foreach ($networks as $network) { ?>
                <option value="<?php echo esc_attr($network->name); ?>" <?php selected($meta_network, $network->name); ?>><?php echo esc_html($network->name); ?></option>
            <?php } ?>
            </select>

            <div id="lock-id-input" style="display: <?php echo ($meta_network ? "block" : "none"); ?>;">
            <label for="unlockprotocol_wpv2_id_meta">Lock Contract ID:</label>
            <input type="text" name="unlockprotocol_wpv2_id_meta" id="unlock-id-input" value="<?php echo esc_attr($meta_id); ?>" />
            </div>
        </div>

        <script>
            function toggleLockInputs() {
                var lockInputs = document.getElementById("lock-inputs");
                if (lockInputs.style.display === "none") {
                    lockInputs.style.display = "block";
                    document.getElementById("add-lock-button").innerHTML = "Remove Lock";
                } else {
                    lockInputs.style.display = "none";
                    document.getElementById("add-lock-button").innerHTML = "Add Lock";
                }
            }

            function toggleLockIdInput() {
                var lockIdInput = document.getElementById("lock-id-input");
                if (document.getElementById("unlock-network-select").value === "") {
                    lockIdInput.style.display = "none";
                } else {
                    lockIdInput.style.display = "block";
                }
            }
        </script>
    <?php
}

  




// Network Input: Validate and sanitize network input
function unlockprotocol_wpv2_validate_network_input($input) 
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'unlockprotocol_wpv2_networks';
    $networks = $wpdb->get_results("SELECT * FROM $table_name");
    
    $valid_networks = array_map(function ($network) {
        return $network->name;
    }, $networks);
    
    if (in_array($input, $valid_networks)) {
        return sanitize_text_field($input);
    } else {
        return '';
    }
}





// Lock Contract ID: Validate and sanitize Contract ID input
function unlockprotocol_wpv2_validate_contract_id_input($input) 
{
    // Sanitize input
    $input = sanitize_text_field($input);

    // Check if input is a valid Ethereum address
    if (preg_match('/^(0x)?[0-9a-f]{40}$/i', $input)) {
        return $input;
    } else {
        return 'error: invalid lock contract address ID added';
    }
}



// Save lock custom meta data to the meta of post/page it was added
function unlockprotocol_wpv2_save_custom_meta($post_id) 
{
    if (isset($_POST['unlockprotocol_wpv2_network_meta'])) {
        
        $network_input = unlockprotocol_wpv2_validate_network_input($_POST['unlockprotocol_wpv2_network_meta']);
        
        if ($network_input) {

            //Save lock network to the meta of specific post/page it was added
            update_post_meta($post_id, 'unlockprotocol_wpv2_network_meta', $network_input);

            if (isset($_POST['unlockprotocol_wpv2_id_meta'])) {

                $id_input = unlockprotocol_wpv2_validate_contract_id_input($_POST['unlockprotocol_wpv2_id_meta']);

                if (!is_wp_error($id_input)) {

                    //Save lock address id to the meta of specific post/page it was added
                    update_post_meta($post_id, 'unlockprotocol_wpv2_id_meta', $id_input);

                }
            }
        }
    }
}
add_action('save_post', 'unlockprotocol_wpv2_save_custom_meta');





// Lock Full post/Page Content and Add Unlock form for user to put lock id used to lock the post to unlock it
function unlockprotocol_wpv2_render_content($content) 
{
    global $post;

    //If user admin/author of the locked content, auto-unlock it
    if ( current_user_can( 'manage_options' ) || ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ) {
        return $content;
    }    

    // Get the lock ID and network from post meta
    $network = get_post_meta($post->ID, 'unlockprotocol_wpv2_network_meta', true);
    $lockAddressId = get_post_meta($post->ID, 'unlockprotocol_wpv2_id_meta', true);


    // If the post has been locked
    if (!empty($network) && !empty($lockAddressId)) {


        // Check if user has entered the correct lock address
        if (isset($_POST['unlockprotocol_wpv2_lock_id']) && !empty($_POST['unlockprotocol_wpv2_lock_id'])) {
            $user_lock_id = sanitize_text_field($_POST['unlockprotocol_wpv2_lock_id']);
            if ($user_lock_id === $lockAddressId) {
                return $content; // Post unlocked
            } else {
                $error_message = "Incorrect Lock Address Entered, Unable to unlock content";
                return unlockprotocol_wpv2_lock_content_form($error_message); // Return error message
            }
        }

        // Post is locked, show input box
        return unlockprotocol_wpv2_lock_content_form();        
 

    } else {

        return $content;  

    }

}
add_action('the_content', 'unlockprotocol_wpv2_render_content');


// Helper function to show locked content form to unlock content
function unlockprotocol_wpv2_lock_content_form($error_message = '') 
{
    $output = '<div class="unlock-pv2-content-locked">';
    $output .= '<p>Please enter the correct Lock Address to unlock the content:</p>';
    if (!empty($error_message)) {
        $output .= '<p class="unlock-pv2-error">' . $error_message . '</p>';
    }
    $output .= '<form method="post">';
    $output .= '<input type="text" name="unlockprotocol_wpv2_lock_id" id="unlock-pv2-lock-id" />';
    $output .= '<input type="submit" name="unlockprotocol_wpv2_submit" value="Unlock" />';
    $output .= '</form>';
    $output .= '</div>';
    return $output;
}



