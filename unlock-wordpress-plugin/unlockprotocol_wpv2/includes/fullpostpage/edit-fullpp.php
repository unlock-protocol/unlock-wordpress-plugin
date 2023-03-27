<?php

// Prevent direct access to this file.
if (!defined('ABSPATH')) {
    exit;
}

// Include needed external files and their functions
require_once plugin_dir_path(__FILE__) . '../unlockprotocol-flow/unlock.php';



// Register and create the Unlock Protocol meta box for posts and pages
function unlock_protocol_add_meta_box() {
    add_meta_box(
        'unlock_protocol_meta_box',
        'Unlock Protocol',
        'unlock_protocol_meta_box_callback',
        ['post', 'page'],
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'unlock_protocol_add_meta_box');



// Output the contents of the Unlock Protocol meta box
function unlock_protocol_meta_box_callback($post) 
{
    wp_nonce_field('unlock_protocol_save_meta', 'unlock_protocol_meta_nonce');

    $attributes = get_post_meta($post->ID, 'unlock_box_attributes', true);

    // Get the up-to-date network list from the plugin user's saved settings
    $networkList = get_option('unlock_protocol_settings');

    // Pass the attributes and network list to JavaScript
    $formattedNetworks = array_map(function ($net) {
        return array(
            'name' => $net['network_name'],
            'value' => $net['network_id'],
        );
    }, array_values($networkList['networks']));

    // Create the meta box HTML structure
    echo '<div id="unlock-protocol-meta-box">';
    echo '<p><button id="add-lock" type="button">Add Lock</button></p>';
    echo '<p><div id="lock-list"></div></p>';
    echo '<p><div id="feedback"></div></p>';
    echo '<p><button id="save-lock" type="button">Save Lock</button></p>';

    // Display the saved selected network and lock address list
    if ($attributes && isset($attributes['locks']) && isset($attributes['ethereumNetworks'])) {
        echo '<div id="saved-locks">';
        echo '<h3>Saved Locks:</h3>';
        echo '<ul>';
        foreach ($attributes['locks'] as $index => $lock) {
            echo '<li>';
            echo '<strong>Network:</strong> ' . $formattedNetworks[array_search($attributes['ethereumNetworks'][$index], array_column($formattedNetworks, 'value'))]['name'];
            echo '<br>';
            echo '<strong>Lock Address:</strong> ' . $lock['lockAddress'];
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    echo '</div>';

    echo '<script>';
    echo 'var unlockAttributes = ' . json_encode($attributes) . ';';
    echo 'var ethereumNetworks = ' . json_encode($formattedNetworks);
    echo '</script>';
}





// AJAX action to save lock attributes
function save_lock_attributes_ajax() {
    check_ajax_referer('unlock_protocol_save_meta', 'nonce');

    $post_id = intval($_POST['post_id']);
    if (isset($_POST['lock_attributes'])) {
        $attributes = json_decode(stripslashes($_POST['lock_attributes']), true);
        update_post_meta($post_id, 'unlock_box_attributes', $attributes);
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_save_lock_attributes', 'save_lock_attributes_ajax');



// Enqueue the necessary JavaScript for the Unlock Protocol meta box
function unlock_protocol_enqueue_scripts() {
    wp_enqueue_script(
        'unlock-protocol-edit-fullpp',
        plugins_url('edit-fullpp.js', __FILE__),
        array('jquery'), // Add any dependencies required by your JavaScript here (e.g., jQuery)
        filemtime(plugin_dir_path(__FILE__) . 'edit-fullpp.js'),
        true
    );
}

add_action('admin_enqueue_scripts', 'unlock_protocol_enqueue_scripts');
